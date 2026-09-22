<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Booking> $bookings
 */
$statusColours = [
    'confirmed' => 'bg-green-50 text-green-700 border-green-200',
    'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
    'cancelled' => 'bg-gray-50 text-gray-500 border-gray-200',
    'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
];
?>
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900"><?= __('Bookings') ?></h1>
    <?= $this->Html->link(__('New booking'), ['action' => 'add'], ['class' => 'rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
</div>

<div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left font-medium text-gray-500"><?= $this->Paginator->sort('start_time', __('Start')) ?></th>
                <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Service') ?></th>
                <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Staff') ?></th>
                <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Customer') ?></th>
                <th class="px-4 py-2 text-left font-medium text-gray-500"><?= $this->Paginator->sort('status', __('Status')) ?></th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($bookings as $booking): ?>
            <tr>
                <td class="px-4 py-2 text-gray-900"><?= h($booking->start_time) ?></td>
                <td class="px-4 py-2 text-gray-500"><?= $booking->hasValue('service') ? $this->Html->link($booking->service->name, ['controller' => 'Services', 'action' => 'view', $booking->service->id], ['class' => 'text-gray-700 hover:text-gray-900']) : '' ?></td>
                <td class="px-4 py-2 text-gray-500"><?= $booking->hasValue('user') ? $this->Html->link($booking->user->email, ['controller' => 'Users', 'action' => 'view', $booking->user->id], ['class' => 'text-gray-700 hover:text-gray-900']) : '' ?></td>
                <td class="px-4 py-2 text-gray-500"><?= $booking->hasValue('customer') ? h($booking->customer->name) : '' ?></td>
                <td class="px-4 py-2">
                    <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium <?= $statusColours[$booking->status] ?? 'bg-gray-50 text-gray-600 border-gray-200' ?>">
                        <?= h(ucfirst($booking->status)) ?>
                    </span>
                </td>
                <td class="px-4 py-2 text-right whitespace-nowrap">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $booking->id], ['class' => 'text-gray-500 hover:text-gray-700']) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $booking->id], ['class' => 'ml-3 text-gray-500 hover:text-gray-700']) ?>
                    <?= $this->Form->postLink(
                        __('Delete'),
                        ['action' => 'delete', $booking->id],
                        [
                            'method' => 'delete',
                            'confirm' => __('Are you sure you want to delete # {0}?', $booking->id),
                            'class' => 'ml-3 text-red-600 hover:text-red-700',
                        ]
                    ) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!count($bookings)) : ?>
            <tr>
                <td colspan="6" class="px-4 py-6 text-center text-gray-500"><?= __('No bookings yet.') ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 flex items-center justify-between text-sm text-gray-500">
    <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    <ul class="flex gap-2">
        <?= $this->Paginator->first('&laquo; ' . __('first'), ['escape' => false]) ?>
        <?= $this->Paginator->prev('&lsaquo; ' . __('previous'), ['escape' => false]) ?>
        <?= $this->Paginator->numbers() ?>
        <?= $this->Paginator->next(__('next') . ' &rsaquo;', ['escape' => false]) ?>
        <?= $this->Paginator->last(__('last') . ' &raquo;', ['escape' => false]) ?>
    </ul>
</div>
