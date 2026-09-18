<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="row">
    <div class="column column-50">
        <div class="users form content">
            <?= $this->Form->create() ?>
            <fieldset>
                <legend><?= __('Log in') ?></legend>
                <?php
                    echo $this->Form->control('email');
                    echo $this->Form->control('password');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Log in')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
