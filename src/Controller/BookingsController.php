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
     * Landing page for a business's public booking link - service selection
     * comes in a later task.
     *
     * @return void
     */
    public function index(): void
    {
    }
}
