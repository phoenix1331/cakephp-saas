<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Business $business
 */
?>
<div class="mx-auto max-w-lg rounded-lg border border-gray-200 bg-white p-8 text-center">
    <h1 class="text-2xl font-semibold text-gray-900"><?= __('Thanks!') ?></h1>
    <p class="mt-2 text-sm text-gray-500">
        <?= __('Your subscription is being set up. It may take a few moments for the {0} plan to show as active.', $business->hasValue('plan') ? h($business->plan->name) : __('chosen')) ?>
    </p>
    <?= $this->Html->link(__('Back to {0}', h($business->name)), ['action' => 'view', $business->id], ['class' => 'mt-6 inline-block rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
</div>
