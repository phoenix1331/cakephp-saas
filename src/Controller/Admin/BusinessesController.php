<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Http\Response;

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
        $this->set(compact('business'));
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
}
