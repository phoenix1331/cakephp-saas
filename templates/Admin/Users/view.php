<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<a href="<?= $this->Url->build(['action' => 'index']) ?>" class="text-sm text-gray-500 hover:text-gray-700">
    &larr; <?= __('Back to staff') ?>
</a>

<div class="mt-2 flex items-start justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900"><?= h($user->email) ?></h1>
        <p class="mt-1 text-sm text-gray-500"><?= h(ucfirst($user->role)) ?></p>
    </div>
    <div class="flex gap-2">
        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id], ['class' => 'rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50']) ?>
        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50']) ?>
    </div>
</div>

<div class="mt-8">
    <h2 class="text-sm font-medium text-gray-900"><?= __('Services') ?></h2>
    <?php if (!empty($user->services)) : ?>
    <div class="mt-3 overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Name') ?></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Duration') ?></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Price') ?></th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($user->services as $service) : ?>
                <tr>
                    <td class="px-4 py-2 text-gray-900"><?= h($service->name) ?></td>
                    <td class="px-4 py-2 text-gray-500"><?= $this->Number->format($service->duration_minutes) ?> <?= __('min') ?></td>
                    <td class="px-4 py-2 text-gray-500"><?= $this->Number->currency($service->price, 'GBP') ?></td>
                    <td class="px-4 py-2 text-right">
                        <?= $this->Html->link(__('View'), ['controller' => 'Services', 'action' => 'view', $service->id], ['class' => 'text-gray-500 hover:text-gray-700']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else : ?>
    <p class="mt-3 text-sm text-gray-500"><?= __('Not assigned to any services yet.') ?></p>
    <?php endif; ?>
</div>

<div class="mt-8">
    <h2 class="text-sm font-medium text-gray-900"><?= __('Availability') ?></h2>
    <?php if (!empty($user->availabilities)) : ?>
    <div class="mt-3 overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Day') ?></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Date') ?></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Start') ?></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('End') ?></th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($user->availabilities as $availability) : ?>
                <tr>
                    <td class="px-4 py-2 text-gray-900"><?= h($availability->day_of_week) ?></td>
                    <td class="px-4 py-2 text-gray-500"><?= h($availability->date) ?></td>
                    <td class="px-4 py-2 text-gray-500"><?= h($availability->start_time) ?></td>
                    <td class="px-4 py-2 text-gray-500"><?= h($availability->end_time) ?></td>
                    <td class="px-4 py-2 text-right">
                        <?= $this->Html->link(__('View'), ['controller' => 'Availabilities', 'action' => 'view', $availability->id], ['class' => 'text-gray-500 hover:text-gray-700']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else : ?>
    <p class="mt-3 text-sm text-gray-500"><?= __('No availability set yet.') ?></p>
    <?php endif; ?>
</div>

<div class="mt-8">
    <h2 class="text-sm font-medium text-gray-900"><?= __('Bookings') ?></h2>
    <?php if (!empty($user->bookings)) : ?>
    <div class="mt-3 overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Start') ?></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Status') ?></th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($user->bookings as $booking) : ?>
                <tr>
                    <td class="px-4 py-2 text-gray-900"><?= h($booking->start_time) ?></td>
                    <td class="px-4 py-2 text-gray-500"><?= h(ucfirst($booking->status)) ?></td>
                    <td class="px-4 py-2 text-right">
                        <?= $this->Html->link(__('View'), ['controller' => 'Bookings', 'action' => 'view', $booking->id], ['class' => 'text-gray-500 hover:text-gray-700']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else : ?>
    <p class="mt-3 text-sm text-gray-500"><?= __('No bookings yet.') ?></p>
    <?php endif; ?>
</div>
