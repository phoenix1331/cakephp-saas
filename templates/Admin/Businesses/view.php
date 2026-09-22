<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Business $business
 * @var iterable<\App\Model\Entity\Plan> $plans
 */
$statusColours = [
    'active' => 'bg-green-50 text-green-700 border-green-200',
    'trialing' => 'bg-blue-50 text-blue-700 border-blue-200',
    'past_due' => 'bg-amber-50 text-amber-700 border-amber-200',
    'canceled' => 'bg-gray-50 text-gray-600 border-gray-200',
];
$statusClass = $statusColours[$business->subscription_status] ?? 'bg-gray-50 text-gray-600 border-gray-200';
?>
<div class="flex items-start justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900"><?= h($business->name) ?></h1>
        <p class="mt-1 text-sm text-gray-500"><?= h($business->slug) ?> &middot; <?= h($business->timezone) ?></p>
    </div>
    <div class="flex gap-2">
        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $business->id], ['class' => 'rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50']) ?>
        <?php if (!empty($business->stripe_customer_id)) : ?>
        <?= $this->Html->link(__('Manage billing'), ['action' => 'billingPortal', $business->id], ['class' => 'rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
        <?php endif; ?>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= __('Subscription') ?></p>
        <span class="mt-2 inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium <?= $statusClass ?>">
            <?= h(ucwords(str_replace('_', ' ', $business->subscription_status ?? 'none'))) ?>
        </span>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= __('Plan') ?></p>
        <p class="mt-2 text-sm font-medium text-gray-900">
            <?= $business->hasValue('plan') ? h($business->plan->name) : __('No plan selected') ?>
        </p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= __('Trial ends') ?></p>
        <p class="mt-2 text-sm font-medium text-gray-900">
            <?= $business->trial_ends_at !== null ? h($business->trial_ends_at->format('j M Y')) : __('N/A') ?>
        </p>
    </div>
</div>

<?php if (!empty($plans)) : ?>
<div class="mt-8">
    <h2 class="text-sm font-medium text-gray-900"><?= __('Subscribe') ?></h2>
    <div class="mt-3 flex flex-wrap gap-2">
        <?php foreach ($plans as $planId => $planName) : ?>
        <?= $this->Html->link(
            __('Subscribe to {0}', $planName),
            ['action' => 'checkout', $business->id, $planId],
            ['class' => 'rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50']
        ) ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="mt-8">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-medium text-gray-900"><?= __('Staff') ?></h2>
        <?= $this->Html->link(__('View all'), ['controller' => 'Users', 'action' => 'index'], ['class' => 'text-sm text-gray-500 hover:text-gray-700']) ?>
    </div>
    <?php if (!empty($business->users)) : ?>
    <div class="mt-3 overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Email') ?></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-500"><?= __('Role') ?></th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($business->users as $user) : ?>
                <tr>
                    <td class="px-4 py-2 text-gray-900"><?= h($user->email) ?></td>
                    <td class="px-4 py-2 text-gray-500"><?= h(ucfirst($user->role)) ?></td>
                    <td class="px-4 py-2 text-right">
                        <?= $this->Html->link(__('View'), ['controller' => 'Users', 'action' => 'view', $user->id], ['class' => 'text-gray-500 hover:text-gray-700']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else : ?>
    <p class="mt-3 text-sm text-gray-500"><?= __('No staff yet.') ?></p>
    <?php endif; ?>
</div>

<div class="mt-8">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-medium text-gray-900"><?= __('Recent bookings') ?></h2>
        <?= $this->Html->link(__('View all'), ['controller' => 'Bookings', 'action' => 'index'], ['class' => 'text-sm text-gray-500 hover:text-gray-700']) ?>
    </div>
    <?php if (!empty($business->bookings)) : ?>
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
                <?php foreach ($business->bookings as $booking) : ?>
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
