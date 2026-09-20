<?php
declare(strict_types=1);

namespace App\Controller;

use App\Billing\StripeClientFactory;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use Cake\Log\Log;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Subscription;
use Stripe\Webhook;

/**
 * Receives Stripe webhook events and keeps Business.subscription_status
 * (and Business.plan_id) in sync locally. This is a plain, signature-
 * verified controller action, not a queued job - Stripe expects a fast
 * 2xx/4xx response on the same request, and retries on anything else,
 * so the handler does the minimum: verify, look up, save.
 */
class StripeWebhooksController extends AppController
{
    /**
     * @return \Cake\Http\Response
     * @throws \Cake\Http\Exception\BadRequestException When the signature or payload is invalid.
     */
    public function handle(): Response
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod('post');

        $event = $this->verifyAndParseEvent();

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                $this->handleSubscriptionChanged($event->data->object);
                break;
            default:
                // Every other event type is intentionally ignored - Stripe
                // sends many more than this app tracks locally, and an
                // unhandled type is not an error.
                break;
        }

        return $this->response->withStringBody('ok');
    }

    /**
     * @return \Stripe\Event
     * @throws \Cake\Http\Exception\BadRequestException When the signature or payload is invalid.
     */
    private function verifyAndParseEvent(): Event
    {
        $secret = Configure::read('Stripe.webhookSecret');
        $signature = $this->request->getHeaderLine('Stripe-Signature');
        $payload = (string)$this->request->getBody();

        try {
            return Webhook::constructEvent($payload, $signature, (string)$secret);
        } catch (UnexpectedValueException | SignatureVerificationException $exception) {
            Log::warning('Stripe webhook rejected: ' . $exception->getMessage());

            throw new BadRequestException('Invalid Stripe webhook payload or signature.', previous: $exception);
        }
    }

    /**
     * A completed Checkout session confirms the subscription and its Plan
     * (via the Price the customer just paid for) - both are only known for
     * certain at this point, not at checkout() time, since a customer can
     * abandon Checkout after the session is created.
     *
     * @param \Stripe\Checkout\Session $session The completed Checkout Session.
     * @return void
     */
    private function handleCheckoutSessionCompleted(CheckoutSession $session): void
    {
        if ($session->mode !== 'subscription' || empty($session->customer) || empty($session->subscription)) {
            return;
        }

        $businesses = $this->fetchTable('Businesses');
        $business = $businesses->find()
            ->where(['stripe_customer_id' => $session->customer])
            ->first();

        if ($business === null) {
            Log::warning('Stripe webhook: no Business found for customer ' . $session->customer);

            return;
        }

        $planId = $this->resolvePlanId((string)$session->subscription);

        $business = $businesses->patchEntity($business, [
            'subscription_status' => 'active',
            'plan_id' => $planId,
        ]);
        $businesses->saveOrFail($business, ['checkRules' => false]);
    }

    /**
     * Keeps subscription_status in sync for any later lifecycle event -
     * a plan change, a cancellation, a payment failure moving it to
     * past_due, etc. Unlike handleCheckoutSessionCompleted(), this can
     * fire any number of times over a subscription's life.
     *
     * @param \Stripe\Subscription $subscription The changed Subscription.
     * @return void
     */
    private function handleSubscriptionChanged(Subscription $subscription): void
    {
        $businesses = $this->fetchTable('Businesses');
        $business = $businesses->find()
            ->where(['stripe_customer_id' => $subscription->customer])
            ->first();

        if ($business === null) {
            Log::warning('Stripe webhook: no Business found for customer ' . $subscription->customer);

            return;
        }

        $business = $businesses->patchEntity($business, [
            'subscription_status' => $subscription->status,
        ]);
        $businesses->saveOrFail($business, ['checkRules' => false]);
    }

    /**
     * @param string $subscriptionId Stripe Subscription id.
     * @return int|null The matching local Plan id, or null if the Price is unrecognised.
     */
    private function resolvePlanId(string $subscriptionId): ?int
    {
        $priceId = StripeClientFactory::create()->getSubscriptionPriceId($subscriptionId);
        if ($priceId === null) {
            return null;
        }

        $plan = $this->fetchTable('Plans')->find()
            ->where(['stripe_price_id' => $priceId])
            ->first();

        return $plan?->id;
    }
}
