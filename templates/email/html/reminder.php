<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Booking $booking
 */
?>
<p>Hi <?= h($booking->customer->name) ?>,</p>

<p>Just a reminder about your upcoming booking.</p>

<p>
    <strong><?= h($booking->service->name) ?></strong> at <strong><?= h($booking->business->name) ?></strong><br>
    <?= h($booking->start_time->format('l j F Y, g:ia')) ?> - <?= h($booking->end_time->format('g:ia')) ?>
</p>

<p>See you soon,<br><?= h($booking->business->name) ?></p>
