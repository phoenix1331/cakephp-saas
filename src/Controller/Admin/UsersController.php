<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\Utility\Text;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Signup method
     *
     * Creates a new Business and its first User (the owner) together - the
     * one place in the app a User is created without an existing tenant to
     * scope to, since signup is what creates the tenant in the first place.
     *
     * @return \Cake\Http\Response|null Redirects on successful signup, renders the form otherwise.
     */
    public function signup(): ?Response
    {
        $this->Authorization->skipAuthorization();

        $businesses = $this->Users->Businesses;
        $business = $businesses->newEmptyEntity();
        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $business = $businesses->newEntity([
                'name' => $data['business_name'] ?? null,
                'slug' => Text::slug(mb_strtolower((string)($data['business_name'] ?? '')), '-'),
                'timezone' => $data['timezone'] ?? 'Europe/London',
                // 14-day trial, no card required - see Business::hasAccess().
                'trial_ends_at' => DateTime::now()->addDays(14),
            ]);
            $saved = $businesses->getConnection()->transactional(
                function () use ($businesses, $business, $data, &$user): bool {
                    if (!$businesses->save($business)) {
                        return false;
                    }

                    $this->Users->behaviors()->get('TenantScope')->setTenantId($business->id);
                    $user = $this->Users->newEntity([
                        'business_id' => $business->id,
                        'email' => $data['email'] ?? null,
                        'password' => $data['password'] ?? null,
                        'role' => 'owner',
                    ]);

                    return (bool)$this->Users->save($user);
                },
            );

            if ($saved) {
                $this->Flash->success(__('Your account has been created. Please log in.'));

                return $this->redirect(['action' => 'login']);
            }

            $this->Flash->error(__('Your account could not be created. Please check the form and try again.'));
        }

        $this->set(compact('business', 'user'));

        return null;
    }

    /**
     * Login method
     *
     * @return \Cake\Http\Response|null Redirects on successful login, renders the form otherwise.
     */
    public function login(): ?Response
    {
        $this->Authorization->skipAuthorization();

        $result = $this->Authentication->getResult();
        if ($result !== null && $result->isValid()) {
            $redirect = $this->request->getQuery('redirect', ['controller' => 'Businesses', 'action' => 'index']);

            return $this->redirect($redirect);
        }

        if ($this->request->is('post') && $result !== null && !$result->isValid()) {
            $this->Flash->error(__('Invalid email or password.'));
        }

        return null;
    }

    /**
     * Logout method
     *
     * @return \Cake\Http\Response|null Redirects to the login page.
     */
    public function logout(): ?Response
    {
        $this->Authorization->skipAuthorization();
        $this->Authentication->logout();

        return $this->redirect(['action' => 'login']);
    }

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

        $query = $this->Users->find();
        $users = $this->paginate($query);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $user = $this->Users->get($id, contain: ['Services', 'Availabilities', 'Bookings']);
        $this->Authorization->authorize($user);
        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * Invites a new staff member to the current Business - any logged-in
     * User may do this, not just the owner (see UserPolicy::canAdd()).
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity([
            'business_id' => $this->Authentication->getIdentityData('business_id'),
        ]);
        $this->Authorization->authorize($user);

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['business_id'] = $this->Authentication->getIdentityData('business_id');
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $services = $this->Users->Services->find('list', limit: 200)->all();
        $this->set(compact('user', 'services'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $user = $this->Users->get($id, contain: ['Services']);
        $this->Authorization->authorize($user);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $services = $this->Users->Services->find('list', limit: 200)->all();
        $this->set(compact('user', 'services'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        $this->Authorization->authorize($user);

        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
