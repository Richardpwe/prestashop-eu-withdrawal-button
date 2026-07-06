<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

use Configuration;
use PrestaShop\Module\EuWithdrawalButton\Repository\WithdrawalRepository;

final class RetentionAnonymizer
{
    private $withdrawals;

    public function __construct(WithdrawalRepository $withdrawals)
    {
        $this->withdrawals = $withdrawals;
    }

    public function anonymizeDueWithdrawals()
    {
        $days = (int) Configuration::get(\EuWithdrawalButton::CONFIG_RETENTION_DAYS);
        if ($days <= 0) {
            return false;
        }

        $cutoff = gmdate('Y-m-d H:i:s', time() - ($days * 86400));

        return $this->withdrawals->anonymizeOlderThan($cutoff);
    }
}

