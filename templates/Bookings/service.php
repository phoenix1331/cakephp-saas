<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Business $business
 * @var \App\Model\Entity\Service $service
 */
?>
<div>
    <a
        href="<?= $this->Url->build(['action' => 'index', 'slug' => $business->slug]) ?>"
        class="text-sm text-gray-500 hover:text-gray-700"
    >&larr; <?= __('Back to services') ?></a>

    <h1 class="mt-2 text-2xl font-semibold text-gray-900"><?= h($service->name) ?></h1>
    <p class="mt-1 text-sm text-gray-500">
        <?= h($service->duration_minutes) ?> <?= __('minutes') ?>
        &middot;
        <?= $this->Number->currency($service->price, 'GBP') ?>
    </p>

    <h2 class="mt-6 text-sm font-medium text-gray-900"><?= __('Choose a staff member') ?></h2>
    <ul class="mt-3 divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
        <?php foreach ($service->users as $user): ?>
        <li>
            <a
                href="<?= $this->Url->build(['action' => 'book', $service->id, $user->id, 'slug' => $business->slug]) ?>"
                class="flex items-center justify-between gap-4 px-4 py-4 hover:bg-gray-50"
            >
                <p class="font-medium text-gray-900"><?= h($user->email) ?></p>
            </a>
        </li>
        <?php endforeach; ?>
        <?php if (!count($service->users)) : ?>
        <li class="px-4 py-6 text-center text-sm text-gray-500">
            <?= __('No staff are currently available for this service.') ?>
        </li>
        <?php endif; ?>
    </ul>
</div>
