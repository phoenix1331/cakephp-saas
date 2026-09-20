<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Business $business
 * @var iterable<\App\Model\Entity\Plan> $plans
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Business'), ['action' => 'edit', $business->id], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="businesses view content">
            <h3><?= h($business->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($business->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Slug') ?></th>
                    <td><?= h($business->slug) ?></td>
                </tr>
                <tr>
                    <th><?= __('Timezone') ?></th>
                    <td><?= h($business->timezone) ?></td>
                </tr>
                <tr>
                    <th><?= __('Stripe Customer Id') ?></th>
                    <td><?= h($business->stripe_customer_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Subscription Status') ?></th>
                    <td><?= h($business->subscription_status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Plan') ?></th>
                    <td><?= $business->hasValue('plan') ? $this->Html->link($business->plan->name, ['controller' => 'Plans', 'action' => 'view', $business->plan->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($business->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Trial Ends At') ?></th>
                    <td><?= h($business->trial_ends_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($business->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($business->modified) ?></td>
                </tr>
            </table>
            <?php if (!empty($plans)) : ?>
            <div class="related">
                <h4><?= __('Subscribe') ?></h4>
                <ul>
                    <?php foreach ($plans as $planId => $planName) : ?>
                    <li>
                        <?= $this->Html->link(
                            __('Subscribe to {0}', $planName),
                            ['action' => 'checkout', $business->id, $planId],
                        ) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <div class="related">
                <h4><?= __('Related Users') ?></h4>
                <?php if (!empty($business->users)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Email') ?></th>
                            <th><?= __('Password') ?></th>
                            <th><?= __('Role') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($business->users as $user) : ?>
                        <tr>
                            <td><?= h($user->id) ?></td>
                            <td><?= h($user->email) ?></td>
                            <td><?= h($user->password) ?></td>
                            <td><?= h($user->role) ?></td>
                            <td><?= h($user->created) ?></td>
                            <td><?= h($user->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Users', 'action' => 'view', $user->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Users', 'action' => 'edit', $user->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Users', 'action' => 'delete', $user->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $user->id),
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Bookings') ?></h4>
                <?php if (!empty($business->bookings)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Service Id') ?></th>
                            <th><?= __('User Id') ?></th>
                            <th><?= __('Customer Id') ?></th>
                            <th><?= __('Start Time') ?></th>
                            <th><?= __('End Time') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Reminder Sent At') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($business->bookings as $booking) : ?>
                        <tr>
                            <td><?= h($booking->id) ?></td>
                            <td><?= h($booking->service_id) ?></td>
                            <td><?= h($booking->user_id) ?></td>
                            <td><?= h($booking->customer_id) ?></td>
                            <td><?= h($booking->start_time) ?></td>
                            <td><?= h($booking->end_time) ?></td>
                            <td><?= h($booking->status) ?></td>
                            <td><?= h($booking->reminder_sent_at) ?></td>
                            <td><?= h($booking->created) ?></td>
                            <td><?= h($booking->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Bookings', 'action' => 'view', $booking->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Bookings', 'action' => 'edit', $booking->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Bookings', 'action' => 'delete', $booking->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $booking->id),
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>