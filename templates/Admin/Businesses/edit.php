<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Business $business
 * @var string[]|\Cake\Collection\CollectionInterface $plans
 */
?>
<div class="mx-auto max-w-lg">
    <a href="<?= $this->Url->build(['action' => 'view', $business->id]) ?>" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; <?= __('Back to overview') ?>
    </a>

    <h1 class="mt-2 text-2xl font-semibold text-gray-900"><?= __('Edit business') ?></h1>

    <?= $this->Form->create($business) ?>
    <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6">
        <?php
            echo $this->Form->control('name', ['label' => __('Name')]);
            echo $this->Form->control('slug', ['label' => __('Slug')]);
            echo $this->Form->control('timezone', ['label' => __('Timezone')]);
            echo $this->Form->control('plan_id', ['label' => __('Plan'), 'options' => $plans, 'empty' => true]);
        ?>

        <div class="mb-4 grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 text-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= __('Subscription status') ?></p>
                <p class="mt-1 text-gray-900"><?= h($business->subscription_status ?? __('None')) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= __('Stripe customer') ?></p>
                <p class="mt-1 text-gray-900"><?= h($business->stripe_customer_id ?? __('Not set')) ?></p>
            </div>
        </div>
        <p class="mb-4 text-xs text-gray-400"><?= __('Subscription status and billing are managed through Stripe, not edited here.') ?></p>

        <?= $this->Form->button(__('Save changes'), ['class' => 'rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
