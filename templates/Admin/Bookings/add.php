<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Booking $booking
 * @var \Cake\Collection\CollectionInterface|string[] $services
 * @var \Cake\Collection\CollectionInterface|string[] $users
 * @var \Cake\Collection\CollectionInterface|string[] $customers
 */
?>
<div class="mx-auto max-w-lg">
    <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; <?= __('Back to bookings') ?>
    </a>

    <h1 class="mt-2 text-2xl font-semibold text-gray-900"><?= __('New booking') ?></h1>

    <?= $this->Form->create($booking) ?>
    <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6">
        <?php
            echo $this->Form->control('service_id', ['label' => __('Service'), 'options' => $services]);
            echo $this->Form->control('user_id', ['label' => __('Staff'), 'options' => $users]);
            echo $this->Form->control('customer_id', ['label' => __('Customer'), 'options' => $customers]);
            echo $this->Form->control('start_time', ['label' => __('Start time')]);
            echo $this->Form->control('end_time', ['label' => __('End time')]);
            echo $this->Form->control('status', ['label' => __('Status')]);
            echo $this->Form->control('reminder_sent_at', ['label' => __('Reminder sent at'), 'empty' => true]);
        ?>
        <?= $this->Form->button(__('Create booking'), ['class' => 'rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
