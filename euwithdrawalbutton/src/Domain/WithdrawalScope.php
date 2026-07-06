<?php

namespace PrestaShop\Module\EuWithdrawalButton\Domain;

final class WithdrawalScope
{
    const UNKNOWN = 'unknown';
    const FULL_ORDER = 'full_order';
    const PARTIAL_ORDER = 'partial_order';
    const FREE_TEXT = 'free_text';

    public static function all()
    {
        return [
            self::UNKNOWN,
            self::FULL_ORDER,
            self::PARTIAL_ORDER,
            self::FREE_TEXT,
        ];
    }

    public static function normalize($scope)
    {
        return in_array($scope, self::all(), true) ? $scope : self::UNKNOWN;
    }
}

