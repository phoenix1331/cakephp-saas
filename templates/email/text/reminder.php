<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Booking $booking
 */
?>
Hi <?= $booking->customer->name ?>,

Just a reminder about your upcoming booking.

<?= $booking->service->name ?> at <?= $booking->business->name ?>

<?= $booking->start_time->format('l j F Y, g:ia') ?> - <?= $booking->end_time->format('g:ia') ?>

See you soon,
<?= $booking->business->name ?>
