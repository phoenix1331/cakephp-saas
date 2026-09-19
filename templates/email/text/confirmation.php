<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Booking $booking
 */
?>
Hi <?= $booking->customer->name ?>,

Your booking is confirmed.

<?= $booking->service->name ?> at <?= $booking->business->name ?>

<?= $booking->start_time->format('l j F Y, g:ia') ?> - <?= $booking->end_time->format('g:ia') ?>

A calendar invite is attached to this email.

See you soon,
<?= $booking->business->name ?>
