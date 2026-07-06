<?php

namespace PrestaShop\Module\EuWithdrawalButton\Domain;

final class WithdrawalStatus
{
    const NEW = 'new';
    const MATCHED = 'matched';
    const MANUAL_REVIEW = 'manual_review';
    const IN_REVIEW = 'in_review';
    const ACCEPTED = 'accepted';
    const REJECTED = 'rejected';
    const AWAITING_RETURN = 'awaiting_return';
    const REFUNDED = 'refunded';
    const CLOSED = 'closed';

    public static function all()
    {
        return [
            self::NEW,
            self::MATCHED,
            self::MANUAL_REVIEW,
            self::IN_REVIEW,
            self::ACCEPTED,
            self::REJECTED,
            self::AWAITING_RETURN,
            self::REFUNDED,
            self::CLOSED,
        ];
    }

    public static function isValid($status)
    {
        return in_array($status, self::all(), true);
    }
}

