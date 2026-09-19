<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Business $business
 * @var iterable<\App\Model\Entity\Service> $services
 */
?>
<div>
    <h1 class="text-2xl font-semibold text-gray-900"><?= h($business->name) ?></h1>
    <p class="mt-1 text-sm text-gray-500"><?= __('Choose a service to book.') ?></p>

    <ul class="mt-6 divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
        <?php foreach ($services as $service): ?>
        <li>
            <a
                href="<?= $this->Url->build(['action' => 'service', $service->id]) ?>"
                class="flex items-center justify-between gap-4 px-4 py-4 hover:bg-gray-50"
            >
                <div>
                    <p class="font-medium text-gray-900"><?= h($service->name) ?></p>
                    <p class="text-sm text-gray-500"><?= h($service->duration_minutes) ?> <?= __('minutes') ?></p>
                </div>
                <p class="font-medium text-gray-900"><?= $this->Number->currency($service->price, 'GBP') ?></p>
            </a>
        </li>
        <?php endforeach; ?>
        <?php if (!count($services)) : ?>
        <li class="px-4 py-6 text-center text-sm text-gray-500">
            <?= __('This business has no bookable services yet.') ?>
        </li>
        <?php endif; ?>
    </ul>
</div>
