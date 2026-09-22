<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Booking $booking
 * @var string[]|\Cake\Collection\CollectionInterface $services
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $customers
 */
?>
<div class="mx-auto max-w-lg">
    <a href="<?= $this->Url->build(['action' => 'view', $booking->id]) ?>" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; <?= __('Back to booking') ?>
    </a>

    <div class="mt-2 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900"><?= __('Edit booking') ?></h1>
        <?= $this->Form->postLink(
            __('Delete'),
            ['action' => 'delete', $booking->id],
            ['confirm' => __('Are you sure you want to delete # {0}?', $booking->id), 'class' => 'text-sm font-medium text-red-600 hover:text-red-700']
        ) ?>
    </div>

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
        <?= $this->Form->button(__('Save changes'), ['class' => 'rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
