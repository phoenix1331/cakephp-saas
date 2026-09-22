<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Service $service
 * @var \Cake\Collection\CollectionInterface|string[] $users
 */
?>
<div class="mx-auto max-w-lg">
    <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; <?= __('Back to services') ?>
    </a>

    <h1 class="mt-2 text-2xl font-semibold text-gray-900"><?= __('New service') ?></h1>

    <?= $this->Form->create($service) ?>
    <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6">
        <?php
            echo $this->Form->control('name', ['label' => __('Name')]);
            echo $this->Form->control('duration_minutes', ['label' => __('Duration (minutes)')]);
            echo $this->Form->control('price', ['label' => __('Price (GBP)')]);
            echo $this->Form->control('users._ids', ['label' => __('Staff who can perform this service'), 'options' => $users, 'multiple' => 'checkbox']);
        ?>
        <?= $this->Form->button(__('Create service'), ['class' => 'rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
