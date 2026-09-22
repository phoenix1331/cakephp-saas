<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Business $business
 * @var \App\Model\Entity\User $user
 */

use Cake\Datasource\EntityInterface;
?>
<div class="mx-auto max-w-sm rounded-lg border border-gray-200 bg-white p-8">
    <h1 class="text-xl font-semibold text-gray-900"><?= __('Sign up') ?></h1>
    <p class="mt-1 text-sm text-gray-500"><?= __('Start your 14-day free trial, no card required.') ?></p>

    <?php
        // Form->create() here is unbound to either $business or $user (the
        // form spans both entities), so its context is a NullContext -
        // Form->control()'s 'error' option is a no-op in that case, since
        // FormHelper::error() checks the bound context for an error before
        // ever looking at the option's value. Errors are rendered by hand
        // below instead, reusing the same Tailwind classes as the 'error'
        // form template (see config/form_templates.php).
        $fieldError = static function (EntityInterface $entity, string $field): string {
            $message = current($entity->getError($field)) ?: null;

            return $message !== null
                ? '<div class="mt-1 text-sm text-red-600">' . h($message) . '</div>'
                : '';
        };
    ?>
    <?= $this->Form->create() ?>
    <div class="mt-6">
        <?php
            echo $this->Form->control('business_name', ['label' => __('Business name')]);
            echo $fieldError($business, 'name');
            echo $this->Form->control('timezone', ['label' => __('Timezone'), 'default' => 'Europe/London']);
            echo $this->Form->control('email', ['label' => __('Email')]);
            echo $fieldError($user, 'email');
            echo $this->Form->control('password', ['label' => __('Password')]);
            echo $fieldError($user, 'password');
        ?>
        <?= $this->Form->button(__('Sign up'), ['class' => 'w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700']) ?>
    </div>
    <?= $this->Form->end() ?>

    <p class="mt-4 text-center text-sm text-gray-500">
        <?= $this->Html->link(__('Already have an account? Log in'), ['action' => 'login'], ['class' => 'font-medium text-gray-900 hover:underline']) ?>
    </p>
</div>
