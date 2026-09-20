<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Business $business
 */
?>
<div class="businesses checkout-success content">
    <h3><?= __('Thanks!') ?></h3>
    <p>
        <?= __('Your subscription is being set up. It may take a few moments for the {0} plan to show as active below.', $business->hasValue('plan') ? h($business->plan->name) : __('chosen')) ?>
    </p>
    <?= $this->Html->link(__('Back to {0}', h($business->name)), ['action' => 'view', $business->id]) ?>
</div>
