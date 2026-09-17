<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Business> $businesses
 */
?>
<div class="businesses index content">
    <?= $this->Html->link(__('New Business'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Businesses') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('slug') ?></th>
                    <th><?= $this->Paginator->sort('timezone') ?></th>
                    <th><?= $this->Paginator->sort('stripe_customer_id') ?></th>
                    <th><?= $this->Paginator->sort('subscription_status') ?></th>
                    <th><?= $this->Paginator->sort('trial_ends_at') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th><?= $this->Paginator->sort('plan_id') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($businesses as $business): ?>
                <tr>
                    <td><?= $this->Number->format($business->id) ?></td>
                    <td><?= h($business->name) ?></td>
                    <td><?= h($business->slug) ?></td>
                    <td><?= h($business->timezone) ?></td>
                    <td><?= h($business->stripe_customer_id) ?></td>
                    <td><?= h($business->subscription_status) ?></td>
                    <td><?= h($business->trial_ends_at) ?></td>
                    <td><?= h($business->created) ?></td>
                    <td><?= h($business->modified) ?></td>
                    <td><?= $business->hasValue('plan') ? $this->Html->link($business->plan->name, ['controller' => 'Plans', 'action' => 'view', $business->plan->id]) : '' ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $business->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $business->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $business->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $business->id),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>