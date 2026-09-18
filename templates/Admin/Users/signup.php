<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Business $business
 * @var \App\Model\Entity\User $user
 */
?>
<div class="row">
    <div class="column column-50">
        <div class="users form content">
            <?= $this->Form->create() ?>
            <fieldset>
                <legend><?= __('Sign up') ?></legend>
                <?php
                    echo $this->Form->control('business_name', [
                        'label' => __('Business name'),
                        'error' => $business->getError('name'),
                    ]);
                    echo $this->Form->control('timezone', ['default' => 'Europe/London']);
                    echo $this->Form->control('email', ['error' => $user->getError('email')]);
                    echo $this->Form->control('password', ['error' => $user->getError('password')]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Sign up')) ?>
            <?= $this->Form->end() ?>
            <?= $this->Html->link(__('Already have an account? Log in'), ['action' => 'login']) ?>
        </div>
    </div>
</div>
