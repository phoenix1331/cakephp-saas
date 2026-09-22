<?php
/**
 * @var \App\View\AppView $this
 */

$identity = $this->request->getAttribute('identity');
$navLinks = [
    'Overview' => ['controller' => 'Businesses', 'action' => 'view', $identity['business_id'] ?? null],
    'Bookings' => ['controller' => 'Bookings', 'action' => 'index'],
    'Services' => ['controller' => 'Services', 'action' => 'index'],
    'Staff' => ['controller' => 'Users', 'action' => 'index'],
];
$currentController = $this->request->getParam('controller');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= __('Dashboard') ?><?= $this->fetch('title') ? ' - ' . $this->fetch('title') : '' ?></title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('app') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    <?php if ($identity !== null) : ?>
    <nav class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <div class="flex items-center gap-8">
                <a href="<?= $this->Url->build(['controller' => 'Businesses', 'action' => 'view', $identity['business_id']]) ?>" class="text-sm font-semibold text-gray-900">
                    <?= __('Dashboard') ?>
                </a>
                <div class="hidden gap-1 sm:flex">
                    <?php foreach ($navLinks as $label => $url) : ?>
                    <a
                        href="<?= $this->Url->build($url) ?>"
                        class="rounded-md px-3 py-1.5 text-sm font-medium <?= $currentController === $url['controller'] ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' ?>"
                    ><?= __($label) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-gray-500 sm:inline"><?= h($identity['email']) ?></span>
                <?= $this->Form->postLink(
                    __('Log out'),
                    ['controller' => 'Users', 'action' => 'logout'],
                    ['class' => 'rounded-md border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100']
                ) ?>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    <main class="mx-auto max-w-6xl px-4 py-8">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>
</body>
</html>
