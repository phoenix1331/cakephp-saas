<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit User'), ['action' => 'edit', $user->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete User'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Users'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New User'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="users view content">
            <h3><?= h($user->email) ?></h3>
            <table>
                <tr>
                    <th><?= __('Business') ?></th>
                    <td><?= $user->hasValue('business') ? $this->Html->link($user->business->name, ['controller' => 'Businesses', 'action' => 'view', $user->business->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Email') ?></th>
                    <td><?= h($user->email) ?></td>
                </tr>
                <tr>
                    <th><?= __('Role') ?></th>
                    <td><?= h($user->role) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($user->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($user->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($user->modified) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Services') ?></h4>
                <?php if (!empty($user->services)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Business Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Duration Minutes') ?></th>
                            <th><?= __('Price') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($user->services as $service) : ?>
                        <tr>
                            <td><?= h($service->id) ?></td>
                            <td><?= h($service->business_id) ?></td>
                            <td><?= h($service->name) ?></td>
                            <td><?= h($service->duration_minutes) ?></td>
                            <td><?= h($service->price) ?></td>
                            <td><?= h($service->created) ?></td>
                            <td><?= h($service->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Services', 'action' => 'view', $service->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Services', 'action' => 'edit', $service->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Services', 'action' => 'delete', $service->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $service->id),
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
                <h4><?= __('Related Availabilities') ?></h4>
                <?php if (!empty($user->availabilities)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Day Of Week') ?></th>
                            <th><?= __('Date') ?></th>
                            <th><?= __('Start Time') ?></th>
                            <th><?= __('End Time') ?></th>
                            <th><?= __('Is Available') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($user->availabilities as $availability) : ?>
                        <tr>
                            <td><?= h($availability->id) ?></td>
                            <td><?= h($availability->day_of_week) ?></td>
                            <td><?= h($availability->date) ?></td>
                            <td><?= h($availability->start_time) ?></td>
                            <td><?= h($availability->end_time) ?></td>
                            <td><?= h($availability->is_available) ?></td>
                            <td><?= h($availability->created) ?></td>
                            <td><?= h($availability->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Availabilities', 'action' => 'view', $availability->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Availabilities', 'action' => 'edit', $availability->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Availabilities', 'action' => 'delete', $availability->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $availability->id),
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
                <?php if (!empty($user->bookings)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Business Id') ?></th>
                            <th><?= __('Service Id') ?></th>
                            <th><?= __('Customer Id') ?></th>
                            <th><?= __('Start Time') ?></th>
                            <th><?= __('End Time') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Reminder Sent At') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($user->bookings as $booking) : ?>
                        <tr>
                            <td><?= h($booking->id) ?></td>
                            <td><?= h($booking->business_id) ?></td>
                            <td><?= h($booking->service_id) ?></td>
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