<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Booking $booking
 */
$statusColours = [
    'confirmed' => 'bg-green-50 text-green-700 border-green-200',
    'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
    'cancelled' => 'bg-gray-50 text-gray-500 border-gray-200',
    'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
];
$statusClass = $statusColours[$booking->status] ?? 'bg-gray-50 text-gray-600 border-gray-200';
?>
<a href="<?= $this->Url->build(['action' => 'index']) ?>" class="text-sm text-gray-500 hover:text-gray-700">
    &larr; <?= __('Back to bookings') ?>
</a>

<div class="mt-2 flex items-start justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900"><?= $booking->hasValue('service') ? h($booking->service->name) : __('Booking') ?></h1>
        <span class="mt-2 inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium <?= $statusClass ?>">
            <?= h(ucfirst($booking->status)) ?>
        </span>
    </div>
    <div class="flex gap-2">
        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $booking->id], ['class' => 'rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50']) ?>
        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $booking->id], ['confirm' => __('Are you sure you want to delete # {0}?', $booking->id), 'class' => 'rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50']) ?>
    </div>
</div>

<div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white">
    <dl class="divide-y divide-gray-100 text-sm">
        <div class="grid grid-cols-3 gap-4 px-4 py-3">
            <dt class="font-medium text-gray-500"><?= __('Service') ?></dt>
            <dd class="col-span-2 text-gray-900"><?= $booking->hasValue('service') ? $this->Html->link($booking->service->name, ['controller' => 'Services', 'action' => 'view', $booking->service->id], ['class' => 'text-gray-900 hover:underline']) : '' ?></dd>
        </div>
        <div class="grid grid-cols-3 gap-4 px-4 py-3">
            <dt class="font-medium text-gray-500"><?= __('Staff') ?></dt>
            <dd class="col-span-2 text-gray-900"><?= $booking->hasValue('user') ? $this->Html->link($booking->user->email, ['controller' => 'Users', 'action' => 'view', $booking->user->id], ['class' => 'text-gray-900 hover:underline']) : '' ?></dd>
        </div>
        <div class="grid grid-cols-3 gap-4 px-4 py-3">
            <dt class="font-medium text-gray-500"><?= __('Customer') ?></dt>
            <dd class="col-span-2 text-gray-900"><?= $booking->hasValue('customer') ? h($booking->customer->name) : '' ?></dd>
        </div>
        <div class="grid grid-cols-3 gap-4 px-4 py-3">
            <dt class="font-medium text-gray-500"><?= __('Start time') ?></dt>
            <dd class="col-span-2 text-gray-900"><?= h($booking->start_time) ?></dd>
        </div>
        <div class="grid grid-cols-3 gap-4 px-4 py-3">
            <dt class="font-medium text-gray-500"><?= __('End time') ?></dt>
            <dd class="col-span-2 text-gray-900"><?= h($booking->end_time) ?></dd>
        </div>
        <div class="grid grid-cols-3 gap-4 px-4 py-3">
            <dt class="font-medium text-gray-500"><?= __('Reminder sent') ?></dt>
            <dd class="col-span-2 text-gray-900"><?= $booking->reminder_sent_at !== null ? h($booking->reminder_sent_at) : __('Not yet') ?></dd>
        </div>
    </dl>
</div>
