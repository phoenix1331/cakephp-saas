<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
?>
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900"><?= __('Staff') ?></h1>
    <?= $this->Html->link(__('New staff member'), ['action' => 'add'], ['class' => 'rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
</div>

<div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left font-medium text-gray-500"><?= $this->Paginator->sort('email', __('Email')) ?></th>
                <th class="px-4 py-2 text-left font-medium text-gray-500"><?= $this->Paginator->sort('role', __('Role')) ?></th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($users as $user): ?>
            <tr>
                <td class="px-4 py-2 font-medium text-gray-900"><?= h($user->email) ?></td>
                <td class="px-4 py-2 text-gray-500"><?= h(ucfirst($user->role)) ?></td>
                <td class="px-4 py-2 text-right whitespace-nowrap">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $user->id], ['class' => 'text-gray-500 hover:text-gray-700']) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id], ['class' => 'ml-3 text-gray-500 hover:text-gray-700']) ?>
                    <?= $this->Form->postLink(
                        __('Delete'),
                        ['action' => 'delete', $user->id],
                        [
                            'method' => 'delete',
                            'confirm' => __('Are you sure you want to delete # {0}?', $user->id),
                            'class' => 'ml-3 text-red-600 hover:text-red-700',
                        ]
                    ) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!count($users)) : ?>
            <tr>
                <td colspan="3" class="px-4 py-6 text-center text-gray-500"><?= __('No staff yet.') ?></td>
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
