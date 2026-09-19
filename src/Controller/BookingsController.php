<?php
declare(strict_types=1);

namespace App\Controller;

use App\Booking\SlotFinder;
use App\Model\Entity\Business;
use App\Model\Entity\Service;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\I18n\Date;
use Cake\I18n\DateTime;

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
     * pick who they want.
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

    /**
     * Book method
     *
     * GET shows available slots for the chosen date (default: today) plus
     * the guest details form. POST validates the chosen slot is still free,
     * finds-or-creates the Customer by email, and creates the Booking.
     *
     * @param string|null $serviceId Service id.
     * @param string|null $userId Staff member (Users.id).
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\NotFoundException When the service/staff pairing is invalid for this Business.
     */
    public function book(?string $serviceId = null, ?string $userId = null): ?Response
    {
        $services = $this->fetchTable('Services');

        try {
            $service = $services->find('unscoped')
                ->where(['id' => $serviceId, 'business_id' => $this->business->id])
                ->contain(['Users' => function ($query) use ($userId) {
                    return $query->find('unscoped')->where(['Users.id' => $userId]);
                }])
                ->firstOrFail();
        } catch (RecordNotFoundException $exception) {
            throw new NotFoundException(
                message: 'No such service for this business.',
                previous: $exception,
            );
        }

        if (count($service->users) !== 1) {
            throw new NotFoundException('That staff member does not offer this service.');
        }

        $date = new Date((string)$this->request->getQuery('date', 'today'));
        $slotFinder = new SlotFinder();
        $slots = $slotFinder->findSlots((int)$userId, $service->duration_minutes, $date);

        $customers = $this->fetchTable('Customers');
        $bookings = $this->fetchTable('Bookings');
        $customer = $customers->newEmptyEntity();
        $booking = $bookings->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $chosenTime = (string)($data['slot'] ?? '');
            $availableTimes = array_map(static fn($slot) => $slot->format('H:i'), $slots);

            if (!in_array($chosenTime, $availableTimes, true)) {
                $this->Flash->error(__('That slot is no longer available. Please choose another.'));
            } else {
                [$saved, $customer, $booking] = $this->createBooking(
                    (int)$userId,
                    $service,
                    $date,
                    $chosenTime,
                    $data,
                );

                if ($saved) {
                    $this->Flash->success(__('Your booking is confirmed.'));

                    return $this->redirect(['action' => 'index', 'slug' => $this->business->slug]);
                }

                $this->Flash->error(__('Your booking could not be made. Please check the form and try again.'));
            }
        }

        $this->set(compact('service', 'date', 'slots', 'customer', 'booking'));

        return null;
    }

    /**
     * Finds-or-creates the Customer by email and creates the Booking, in
     * one transaction.
     *
     * @param int $userId Staff member (Users.id).
     * @param \App\Model\Entity\Service $service The chosen Service.
     * @param \Cake\I18n\Date $date The chosen date.
     * @param string $chosenTime The chosen slot, `H:i`.
     * @param array<string, mixed> $data Submitted form data (name, email, phone).
     * @return array{0: bool, 1: \App\Model\Entity\Customer, 2: \App\Model\Entity\Booking}
     */
    private function createBooking(int $userId, Service $service, Date $date, string $chosenTime, array $data): array
    {
        // Bookings::buildRules() checks existsIn against Services, Users and
        // Customers too, so all four tenant-scoped tables need the tenant
        // set before the save, not just the ones queried directly here.
        foreach (['Customers', 'Bookings', 'Services', 'Users'] as $alias) {
            $this->fetchTable($alias)->behaviors()->get('TenantScope')->setTenantId($this->business->id);
        }

        $customers = $this->fetchTable('Customers');
        $bookings = $this->fetchTable('Bookings');

        $customer = $customers->newEmptyEntity();
        $booking = $bookings->newEmptyEntity();

        $saved = $bookings->getConnection()->transactional(function () use (
            $customers,
            $bookings,
            $service,
            $userId,
            $date,
            $chosenTime,
            $data,
            &$customer,
            &$booking,
        ): bool {
            $customer = $customers->findOrCreate(
                ['business_id' => $this->business->id, 'email' => $data['email'] ?? null],
                function ($entity) use ($data) {
                    $entity->set('name', $data['name'] ?? null);
                    $entity->set('phone', $data['phone'] ?? null);
                },
            );

            $startTime = new DateTime($date->format('Y-m-d') . ' ' . $chosenTime . ':00');
            $endTime = $startTime->addMinutes($service->duration_minutes);

            $booking = $bookings->newEntity([
                'business_id' => $this->business->id,
                'service_id' => $service->id,
                'user_id' => $userId,
                'customer_id' => $customer->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'pending',
            ]);

            return (bool)$bookings->save($booking);
        });

        return [$saved, $customer, $booking];
    }
}
