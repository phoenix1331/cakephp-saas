<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Service $service
 * @var string[]|\Cake\Collection\CollectionInterface $businesses
 * @var string[]|\Cake\Collection\CollectionInterface $users
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $service->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $service->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Services'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="services form content">
            <?= $this->Form->create($service) ?>
            <fieldset>
                <legend><?= __('Edit Service') ?></legend>
                <?php
                    echo $this->Form->control('business_id', ['options' => $businesses]);
                    echo $this->Form->control('name');
                    echo $this->Form->control('duration_minutes');
                    echo $this->Form->control('price');
                    echo $this->Form->control('users._ids', ['options' => $users]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
