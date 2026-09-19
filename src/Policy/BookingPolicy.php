<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Booking;
use Authorization\IdentityInterface;

class BookingPolicy
{
    /**
     * Any User may view/manage Bookings within their own Business.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Booking $booking Booking.
     * @return bool
     */
    public function canView(IdentityInterface $identity, Booking $booking): bool
    {
        return (int)$identity['business_id'] === $booking->business_id;
    }

    /**
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Booking $booking Booking.
     * @return bool
     */
    public function canEdit(IdentityInterface $identity, Booking $booking): bool
    {
        return $this->canView($identity, $booking);
    }

    /**
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Booking $booking Booking.
     * @return bool
     */
    public function canDelete(IdentityInterface $identity, Booking $booking): bool
    {
        return $this->canView($identity, $booking);
    }

    /**
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Booking $booking Booking.
     * @return bool
     */
    public function canAdd(IdentityInterface $identity, Booking $booking): bool
    {
        return (int)$identity['business_id'] === (int)$booking->business_id;
    }
}
