<?php
declare(strict_types=1);

namespace App\Controller\Admin;

/**
 * Services Controller
 *
 * @property \App\Model\Table\ServicesTable $Services
 */
class ServicesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // Tenant scoping already restricts this to the current Business -
        // there's no per-row policy question left to ask for a listing.
        $this->Authorization->skipAuthorization();

        $query = $this->Services->find()
            ->contain(['Businesses']);
        $services = $this->paginate($query);

        $this->set(compact('services'));
    }

    /**
     * View method
     *
     * @param string|null $id Service id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $service = $this->Services->get($id, contain: ['Businesses', 'Users', 'Bookings']);
        $this->Authorization->authorize($service);
        $this->set(compact('service'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $service = $this->Services->newEntity([
            'business_id' => $this->Authentication->getIdentityData('business_id'),
        ]);
        $this->Authorization->authorize($service);

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['business_id'] = $this->Authentication->getIdentityData('business_id');
            $service = $this->Services->patchEntity($service, $data);
            if ($this->Services->save($service)) {
                $this->Flash->success(__('The service has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The service could not be saved. Please, try again.'));
        }
        $users = $this->Services->Users->find('list', limit: 200)->all();
        $this->set(compact('service', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Service id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $service = $this->Services->get($id, contain: ['Users']);
        $this->Authorization->authorize($service);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $service = $this->Services->patchEntity($service, $this->request->getData());
            if ($this->Services->save($service)) {
                $this->Flash->success(__('The service has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The service could not be saved. Please, try again.'));
        }
        $users = $this->Services->Users->find('list', limit: 200)->all();
        $this->set(compact('service', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Service id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $service = $this->Services->get($id);
        $this->Authorization->authorize($service);

        if ($this->Services->delete($service)) {
            $this->Flash->success(__('The service has been deleted.'));
        } else {
            $this->Flash->error(__('The service could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
