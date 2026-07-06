<?php

namespace PrestaShop\Module\EuWithdrawalButton\Domain;

final class MailStatus
{
    const PENDING = 'pending';
    const SENT = 'sent';
    const FAILED = 'failed';
    const PARTIAL = 'partial';

    public static function all()
    {
        return [
            self::PENDING,
            self::SENT,
            self::FAILED,
            self::PARTIAL,
        ];
    }
}

