<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="mx-auto max-w-sm rounded-lg border border-gray-200 bg-white p-8">
    <h1 class="text-xl font-semibold text-gray-900"><?= __('Log in') ?></h1>

    <?= $this->Form->create() ?>
    <div class="mt-6">
        <?php
            echo $this->Form->control('email', ['label' => __('Email')]);
            echo $this->Form->control('password', ['label' => __('Password')]);
        ?>
        <?= $this->Form->button(__('Log in'), ['class' => 'w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
    </div>
    <?= $this->Form->end() ?>

    <p class="mt-4 text-center text-sm text-gray-500">
        <?= $this->Html->link(__('Sign up'), ['action' => 'signup'], ['class' => 'font-medium text-gray-900 hover:underline']) ?>
    </p>
</div>
