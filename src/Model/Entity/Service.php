<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Service Entity
 *
 * @property int $id
 * @property int $business_id
 * @property string $name
 * @property int $duration_minutes
 * @property string $price
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Business $business
 * @property \App\Model\Entity\User[] $users
 */
class Service extends Entity
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
        'name' => true,
        'duration_minutes' => true,
        'price' => true,
        'created' => true,
        'modified' => true,
        'business' => true,
        'users' => true,
    ];
}
