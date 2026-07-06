<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

use PrestaShop\Module\EuWithdrawalButton\Repository\WithdrawalLogRepository;

final class AuditLogger
{
    private $logs;

    public function __construct(WithdrawalLogRepository $logs)
    {
        $this->logs = $logs;
    }

    public function log($idWithdrawal, $eventType, $oldStatus = null, $newStatus = null, $note = null, $idEmployee = null)
    {
        return $this->logs->add($idWithdrawal, $eventType, $oldStatus, $newStatus, $note, $idEmployee);
    }
}

