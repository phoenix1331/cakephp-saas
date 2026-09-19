<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Business $business
 * @var \App\Model\Entity\Service $service
 * @var \Cake\I18n\Date $date
 * @var array<\Cake\I18n\Time> $slots
 * @var \App\Model\Entity\Customer $customer
 * @var \App\Model\Entity\Booking $booking
 */
$user = $service->users[0] ?? null;
?>
<div>
    <a
        href="<?= $this->Url->build(['action' => 'service', $service->id, 'slug' => $business->slug]) ?>"
        class="text-sm text-gray-500 hover:text-gray-700"
    >&larr; <?= __('Back to staff') ?></a>

    <h1 class="mt-2 text-2xl font-semibold text-gray-900"><?= h($service->name) ?></h1>
    <p class="mt-1 text-sm text-gray-500">
        <?= $user ? h($user->email) : '' ?>
        &middot;
        <?= h($service->duration_minutes) ?> <?= __('minutes') ?>
    </p>

    <div class="mt-6 flex items-center justify-between">
        <a
            href="<?= $this->Url->build([
                'action' => 'book',
                $service->id,
                $user->id ?? null,
                'slug' => $business->slug,
                '?' => ['date' => $date->subDays(1)->format('Y-m-d')],
            ]) ?>"
            class="text-sm text-gray-500 hover:text-gray-700"
        >&larr; <?= __('Previous day') ?></a>
        <h2 class="text-sm font-medium text-gray-900"><?= h($date->format('l, j F Y')) ?></h2>
        <a
            href="<?= $this->Url->build([
                'action' => 'book',
                $service->id,
                $user->id ?? null,
                'slug' => $business->slug,
                '?' => ['date' => $date->addDays(1)->format('Y-m-d')],
            ]) ?>"
            class="text-sm text-gray-500 hover:text-gray-700"
        ><?= __('Next day') ?> &rarr;</a>
    </div>

    <?= $this->Form->create($booking) ?>

    <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4">
        <?php foreach ($slots as $slot): ?>
        <?php $time = $slot->format('H:i'); ?>
        <label class="cursor-pointer">
            <input type="radio" name="slot" value="<?= h($time) ?>" class="peer sr-only" required>
            <span class="block rounded-lg border border-gray-200 bg-white px-3 py-2 text-center text-sm font-medium text-gray-900 peer-checked:border-gray-900 peer-checked:bg-gray-900 peer-checked:text-white">
                <?= h($time) ?>
            </span>
        </label>
        <?php endforeach; ?>
        <?php if (!count($slots)) : ?>
        <p class="col-span-full text-sm text-gray-500"><?= __('No slots available on this day.') ?></p>
        <?php endif; ?>
    </div>

    <?php if (count($slots)) : ?>
    <fieldset class="mt-8 max-w-sm">
        <legend class="text-sm font-medium text-gray-900"><?= __('Your details') ?></legend>
        <?php
            echo $this->Form->control('name', [
                'label' => __('Name'),
                'value' => $customer->name ?? null,
                'error' => $customer->getError('name'),
            ]);
            echo $this->Form->control('email', [
                'label' => __('Email'),
                'value' => $customer->email ?? null,
                'error' => $customer->getError('email'),
            ]);
            echo $this->Form->control('phone', [
                'label' => __('Phone (optional)'),
                'required' => false,
                'value' => $customer->phone ?? null,
            ]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Confirm booking'), ['class' => 'mt-4 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white']) ?>
    <?php endif; ?>
    <?= $this->Form->end() ?>
</div>
