<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Booking Entity
 *
 * @property int $id
 * @property int $business_id
 * @property int $service_id
 * @property int $user_id
 * @property int $customer_id
 * @property \Cake\I18n\DateTime $start_time
 * @property \Cake\I18n\DateTime $end_time
 * @property string $status
 * @property \Cake\I18n\DateTime|null $reminder_sent_at
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Business $business
 * @property \App\Model\Entity\Service $service
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Customer $customer
 */
class Booking extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'business_id' => true,
        'service_id' => true,
        'user_id' => true,
        'customer_id' => true,
        'start_time' => true,
        'end_time' => true,
        'status' => true,
        'reminder_sent_at' => true,
        'created' => true,
        'modified' => true,
        'business' => true,
        'service' => true,
        'user' => true,
        'customer' => true,
    ];
}
