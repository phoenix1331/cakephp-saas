<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Booking $booking
 */
?>
<p>Hi <?= h($booking->customer->name) ?>,</p>

<p>Your booking is confirmed.</p>

<p>
    <strong><?= h($booking->service->name) ?></strong> at <strong><?= h($booking->business->name) ?></strong><br>
    <?= h($booking->start_time->format('l j F Y, g:ia')) ?> - <?= h($booking->end_time->format('g:ia')) ?>
</p>

<p>A calendar invite is attached to this email.</p>

<p>See you soon,<br><?= h($booking->business->name) ?></p>
