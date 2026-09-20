<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Billing\StripeClientFactory;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Routing\Router;
use RuntimeException;

/**
 * Businesses Controller
 *
 * @property \App\Model\Table\BusinessesTable $Businesses
 */
class BusinessesController extends AppController
{
    /**
     * Index method
     *
     * A User belongs to exactly one Business, so there is no multi-business
     * list to show - index() is a settings-page entry point that redirects
     * straight to the logged-in identity's own Business.
     *
     * @return \Cake\Http\Response Redirects to view() for the current Business.
     */
    public function index(): Response
    {
        // Redirects to view(), which performs the real authorization check.
        $this->Authorization->skipAuthorization();

        return $this->redirect(['action' => 'view', $this->Authentication->getIdentityData('business_id')]);
    }

    /**
     * View method
     *
     * @param string|null $id Business id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $business = $this->Businesses->get($id, contain: ['Plans', 'Users', 'Bookings']);
        $this->Authorization->authorize($business);
        $plans = $this->Businesses->Plans->find('list', limit: 200)->all();
        $this->set(compact('business', 'plans'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Business id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $business = $this->Businesses->get($id, contain: []);
        $this->Authorization->authorize($business);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $business = $this->Businesses->patchEntity($business, $this->request->getData());
            if ($this->Businesses->save($business)) {
                $this->Flash->success(__('The business has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The business could not be saved. Please, try again.'));
        }
        $plans = $this->Businesses->Plans->find('list', limit: 200)->all();
        $this->set(compact('business', 'plans'));
    }

    /**
     * Checkout method
     *
     * Starts a Stripe Checkout session for the given Plan and redirects the
     * owner to Stripe's hosted page. Creates the Stripe Customer on first
     * use (once created, its id is reused for every future subscription
     * change) rather than at signup, since a Business may never actually
     * subscribe.
     *
     * @param string|null $id Business id.
     * @param string|null $planId Plan id.
     * @return \Cake\Http\Response
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When the Business or Plan does not exist.
     * @throws \Cake\Http\Exception\NotFoundException When the Plan has no `stripe_price_id` configured.
     */
    public function checkout(?string $id = null, ?string $planId = null): Response
    {
        $business = $this->Businesses->get($id, contain: []);
        $this->Authorization->authorize($business);

        $plan = $this->Businesses->Plans->get($planId);
        if (empty($plan->stripe_price_id)) {
            throw new NotFoundException(sprintf('Plan `%s` has no Stripe price configured.', $plan->name));
        }

        $checkoutClient = StripeClientFactory::create();

        if (empty($business->stripe_customer_id)) {
            $customerId = $checkoutClient->createCustomer($business->name, $business->id);
            $business = $this->Businesses->patchEntity($business, [
                'stripe_customer_id' => $customerId,
            ]);
            $this->Businesses->saveOrFail($business);
        }

        try {
            $sessionUrl = $checkoutClient->createCheckoutSessionUrl(
                $business->stripe_customer_id,
                $plan->stripe_price_id,
                Router::url(['action' => 'checkoutSuccess', $business->id], true) . '?session_id={CHECKOUT_SESSION_ID}',
                Router::url(['action' => 'view', $business->id], true),
            );
        } catch (RuntimeException $exception) {
            $this->Flash->error(__('Could not start checkout: {0}', $exception->getMessage()));

            return $this->redirect(['action' => 'view', $business->id]);
        }

        return $this->redirect($sessionUrl);
    }

    /**
     * CheckoutSuccess method
     *
     * Stripe redirects here after a successful Checkout session. The
     * subscription itself is confirmed and Business.subscription_status is
     * kept in sync by the `checkout.session.completed` webhook, not here -
     * a customer landing on this page has no guarantee the webhook has
     * been delivered and processed yet, so this is a friendly landing page
     * only, never the source of truth for whether payment succeeded.
     *
     * @param string|null $id Business id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When the Business does not exist.
     */
    public function checkoutSuccess(?string $id = null): void
    {
        $business = $this->Businesses->get($id, contain: ['Plans']);
        $this->Authorization->authorize($business, 'view');

        $this->set(compact('business'));
    }
}
