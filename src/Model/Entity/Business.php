<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Business Entity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $timezone
 * @property string|null $stripe_customer_id
 * @property string|null $subscription_status
 * @property \Cake\I18n\DateTime|null $trial_ends_at
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Business extends Entity
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
        'name' => true,
        'slug' => true,
        'timezone' => true,
        'stripe_customer_id' => true,
        'subscription_status' => true,
        'trial_ends_at' => true,
        'created' => true,
        'modified' => true,
    ];
}
