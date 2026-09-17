<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Booking $booking
 * @var \Cake\Collection\CollectionInterface|string[] $businesses
 * @var \Cake\Collection\CollectionInterface|string[] $services
 * @var \Cake\Collection\CollectionInterface|string[] $users
 * @var \Cake\Collection\CollectionInterface|string[] $customers
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Bookings'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="bookings form content">
            <?= $this->Form->create($booking) ?>
            <fieldset>
                <legend><?= __('Add Booking') ?></legend>
                <?php
                    echo $this->Form->control('business_id', ['options' => $businesses]);
                    echo $this->Form->control('service_id', ['options' => $services]);
                    echo $this->Form->control('user_id', ['options' => $users]);
                    echo $this->Form->control('customer_id', ['options' => $customers]);
                    echo $this->Form->control('start_time');
                    echo $this->Form->control('end_time');
                    echo $this->Form->control('status');
                    echo $this->Form->control('reminder_sent_at', ['empty' => true]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
