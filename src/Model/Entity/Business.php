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
 * @property int|null $plan_id
 * @property \Cake\I18n\DateTime|null $trial_ends_at
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Plan|null $plan
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
        'plan_id' => true,
        'trial_ends_at' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * A Business has access to the dashboard if its 14-day trial hasn't
     * expired yet, or if it has an active/trialing Stripe subscription -
     * the two are independent, since a Business can subscribe before its
     * trial ends, and subscription_status stops being meaningful once it
     * does (a card is only required after the trial, per the brief).
     *
     * @return bool
     */
    public function hasAccess(): bool
    {
        if (in_array($this->subscription_status, ['active', 'trialing'], true)) {
            return true;
        }

        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }
}
