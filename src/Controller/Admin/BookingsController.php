<?php
declare(strict_types=1);

namespace App\Controller\Admin;

/**
 * Bookings Controller
 *
 * @property \App\Model\Table\BookingsTable $Bookings
 */
class BookingsController extends AppController
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

        $query = $this->Bookings->find()
            ->contain(['Services', 'Users', 'Customers']);
        $bookings = $this->paginate($query);

        $this->set(compact('bookings'));
    }

    /**
     * View method
     *
     * @param string|null $id Booking id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $booking = $this->Bookings->get($id, contain: ['Services', 'Users', 'Customers']);
        $this->Authorization->authorize($booking);
        $this->set(compact('booking'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $booking = $this->Bookings->newEntity([
            'business_id' => $this->Authentication->getIdentityData('business_id'),
        ]);
        $this->Authorization->authorize($booking);

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['business_id'] = $this->Authentication->getIdentityData('business_id');
            $booking = $this->Bookings->patchEntity($booking, $data);
            if ($this->Bookings->save($booking)) {
                $this->Flash->success(__('The booking has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The booking could not be saved. Please, try again.'));
        }
        $services = $this->Bookings->Services->find('list', limit: 200)->all();
        $users = $this->Bookings->Users->find('list', limit: 200)->all();
        $customers = $this->Bookings->Customers->find('list', limit: 200)->all();
        $this->set(compact('booking', 'services', 'users', 'customers'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Booking id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $booking = $this->Bookings->get($id, contain: []);
        $this->Authorization->authorize($booking);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $booking = $this->Bookings->patchEntity($booking, $this->request->getData());
            if ($this->Bookings->save($booking)) {
                $this->Flash->success(__('The booking has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The booking could not be saved. Please, try again.'));
        }
        $services = $this->Bookings->Services->find('list', limit: 200)->all();
        $users = $this->Bookings->Users->find('list', limit: 200)->all();
        $customers = $this->Bookings->Customers->find('list', limit: 200)->all();
        $this->set(compact('booking', 'services', 'users', 'customers'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Booking id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $booking = $this->Bookings->get($id);
        $this->Authorization->authorize($booking);

        if ($this->Bookings->delete($booking)) {
            $this->Flash->success(__('The booking has been deleted.'));
        } else {
            $this->Flash->error(__('The booking could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
