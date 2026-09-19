<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Business;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * The public, unauthenticated guest booking flow at /book/{slug}.
 *
 * The Business (tenant) is resolved from the slug route parameter before
 * every action, not from a logged-in identity - there is no login here.
 */
class BookingsController extends AppController
{
    protected Business $business;

    /**
     * @param \Cake\Event\EventInterface $event The beforeFilter event.
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException When the slug matches no Business.
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authorization->skipAuthorization();
        $this->viewBuilder()->setLayout('booking');

        $slug = $this->request->getParam('slug');

        try {
            $this->business = $this->fetchTable('Businesses')
                ->find()
                ->where(['slug' => $slug])
                ->firstOrFail();
        } catch (RecordNotFoundException $exception) {
            throw new NotFoundException(
                message: sprintf('No business found for slug `%s`.', (string)$slug),
                previous: $exception,
            );
        }

        $this->set('business', $this->business);
    }

    /**
     * Index method
     *
     * Lists the services a customer can book with this Business.
     *
     * @return void
     */
    public function index(): void
    {
        $services = $this->fetchTable('Services')
            ->find('unscoped')
            ->where(['business_id' => $this->business->id])
            ->orderBy(['name' => 'ASC'])
            ->all();

        $this->set(compact('services'));
    }

    /**
     * Service method
     *
     * Shows which staff can perform the chosen Service, so the customer can
     * pick who they want - availability/slot computation is a later task.
     *
     * @param string|null $id Service id.
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException When the service does not belong to this Business.
     */
    public function service(?string $id = null): void
    {
        try {
            $service = $this->fetchTable('Services')
                ->find('unscoped')
                ->where(['id' => $id, 'business_id' => $this->business->id])
                ->contain(['Users' => function ($query) {
                    return $query->find('unscoped');
                }])
                ->firstOrFail();
        } catch (RecordNotFoundException $exception) {
            throw new NotFoundException(
                message: sprintf('No service `%s` found for this business.', (string)$id),
                previous: $exception,
            );
        }

        $this->set(compact('service'));
    }
}
