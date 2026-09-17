<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Availability Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $day_of_week
 * @property \Cake\I18n\Date|null $date
 * @property \Cake\I18n\Time $start_time
 * @property \Cake\I18n\Time $end_time
 * @property bool $is_available
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\User $user
 */
class Availability extends Entity
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
        'user_id' => true,
        'day_of_week' => true,
        'date' => true,
        'start_time' => true,
        'end_time' => true,
        'is_available' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];
}
