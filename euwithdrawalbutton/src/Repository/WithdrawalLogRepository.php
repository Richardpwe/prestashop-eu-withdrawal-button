<?php

namespace PrestaShop\Module\EuWithdrawalButton\Repository;

use Db;

final class WithdrawalLogRepository
{
    const TABLE = 'euwb_withdrawal_log';

    public function add($idWithdrawal, $eventType, $oldStatus = null, $newStatus = null, $note = null, $idEmployee = null)
    {
        return (bool) Db::getInstance()->insert(self::TABLE, [
            'id_withdrawal' => (int) $idWithdrawal,
            'id_employee' => $idEmployee !== null ? (int) $idEmployee : null,
            'event_type' => (string) $eventType,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    public function findByWithdrawal($idWithdrawal)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '`
            WHERE `id_withdrawal` = ' . (int) $idWithdrawal . '
            ORDER BY `created_at` ASC, `id_withdrawal_log` ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }
}

