<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

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
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Businesses->find()
            ->contain(['Plans']);
        $businesses = $this->paginate($query);

        $this->set(compact('businesses'));
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
        $this->set(compact('business'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $business = $this->Businesses->newEmptyEntity();
        if ($this->request->is('post')) {
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
     * Edit method
     *
     * @param string|null $id Business id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $business = $this->Businesses->get($id, contain: []);
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
     * Delete method
     *
     * @param string|null $id Business id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $business = $this->Businesses->get($id);
        if ($this->Businesses->delete($business)) {
            $this->Flash->success(__('The business has been deleted.'));
        } else {
            $this->Flash->error(__('The business could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
