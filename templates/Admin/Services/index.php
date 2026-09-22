<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Service> $services
 */
?>
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900"><?= __('Services') ?></h1>
    <?= $this->Html->link(__('New service'), ['action' => 'add'], ['class' => 'rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
</div>

<div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left font-medium text-gray-500"><?= $this->Paginator->sort('name', __('Name')) ?></th>
                <th class="px-4 py-2 text-left font-medium text-gray-500"><?= $this->Paginator->sort('duration_minutes', __('Duration')) ?></th>
                <th class="px-4 py-2 text-left font-medium text-gray-500"><?= $this->Paginator->sort('price', __('Price')) ?></th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($services as $service): ?>
            <tr>
                <td class="px-4 py-2 font-medium text-gray-900"><?= h($service->name) ?></td>
                <td class="px-4 py-2 text-gray-500"><?= $this->Number->format($service->duration_minutes) ?> <?= __('min') ?></td>
                <td class="px-4 py-2 text-gray-500"><?= $this->Number->currency($service->price, 'GBP') ?></td>
                <td class="px-4 py-2 text-right whitespace-nowrap">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $service->id], ['class' => 'text-gray-500 hover:text-gray-700']) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $service->id], ['class' => 'ml-3 text-gray-500 hover:text-gray-700']) ?>
                    <?= $this->Form->postLink(
                        __('Delete'),
                        ['action' => 'delete', $service->id],
                        [
                            'method' => 'delete',
                            'confirm' => __('Are you sure you want to delete # {0}?', $service->id),
                            'class' => 'ml-3 text-red-600 hover:text-red-700',
                        ]
                    ) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!count($services)) : ?>
            <tr>
                <td colspan="4" class="px-4 py-6 text-center text-gray-500"><?= __('No services yet.') ?></td>
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
