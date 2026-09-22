<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string[]|\Cake\Collection\CollectionInterface $services
 */
?>
<div class="mx-auto max-w-lg">
    <a href="<?= $this->Url->build(['action' => 'view', $user->id]) ?>" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; <?= __('Back to staff member') ?>
    </a>

    <div class="mt-2 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900"><?= __('Edit staff member') ?></h1>
        <?= $this->Form->postLink(
            __('Delete'),
            ['action' => 'delete', $user->id],
            ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'text-sm font-medium text-red-600 hover:text-red-700']
        ) ?>
    </div>

    <?= $this->Form->create($user) ?>
    <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6">
        <?php
            echo $this->Form->control('email', ['label' => __('Email')]);
            echo $this->Form->control('password', ['label' => __('Password')]);
            echo $this->Form->control('role', ['label' => __('Role')]);
            echo $this->Form->control('services._ids', ['label' => __('Services this staff member can perform'), 'options' => $services, 'multiple' => 'checkbox']);
        ?>
        <?= $this->Form->button(__('Save changes'), ['class' => 'rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
