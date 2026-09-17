<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Business $business
 * @var \Cake\Collection\CollectionInterface|string[] $plans
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Businesses'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="businesses form content">
            <?= $this->Form->create($business) ?>
            <fieldset>
                <legend><?= __('Add Business') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('slug');
                    echo $this->Form->control('timezone');
                    echo $this->Form->control('stripe_customer_id');
                    echo $this->Form->control('subscription_status');
                    echo $this->Form->control('trial_ends_at', ['empty' => true]);
                    echo $this->Form->control('plan_id', ['options' => $plans, 'empty' => true]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
