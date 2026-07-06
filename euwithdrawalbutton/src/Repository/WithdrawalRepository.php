<?php

namespace PrestaShop\Module\EuWithdrawalButton\Repository;

use Db;
use PrestaShop\Module\EuWithdrawalButton\Domain\MailStatus;
use PrestaShop\Module\EuWithdrawalButton\Domain\WithdrawalStatus;

final class WithdrawalRepository
{
    const TABLE = 'euwb_withdrawal';

    public function insert(array $data)
    {
        Db::getInstance()->insert(self::TABLE, $data);

        return (int) Db::getInstance()->Insert_ID();
    }

    public function findById($idWithdrawal)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '` WHERE `id_withdrawal` = ' . (int) $idWithdrawal;
        $row = Db::getInstance()->getRow($sql);

        return $row ?: null;
    }

    public function findByPublicReference($reference)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '` WHERE `public_reference` = \'' . pSQL($reference) . '\'';
        $row = Db::getInstance()->getRow($sql);

        return $row ?: null;
    }

    public function findByIdempotencyHash($idShop, $hash)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '`
            WHERE `id_shop` = ' . (int) $idShop . '
            AND `idempotency_key_hash` = \'' . pSQL($hash) . '\'';
        $row = Db::getInstance()->getRow($sql);

        return $row ?: null;
    }

    public function search(array $filters = [], $limit = 50, $offset = 0)
    {
        $where = $this->buildWhere($filters);
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '`'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY `submitted_at` DESC'
            . ' LIMIT ' . (int) $offset . ', ' . (int) $limit;

        return Db::getInstance()->executeS($sql) ?: [];
    }

    public function count(array $filters = [])
    {
        $where = $this->buildWhere($filters);
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . self::TABLE . '`'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '');

        return (int) Db::getInstance()->getValue($sql);
    }

    public function updateMailStatus($idWithdrawal, $mailStatus, $acknowledgementSentAt = null, $adminNotifiedAt = null)
    {
        $data = [
            'mail_status' => in_array($mailStatus, MailStatus::all(), true) ? $mailStatus : MailStatus::FAILED,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ];

        if ($acknowledgementSentAt !== null) {
            $data['acknowledgement_sent_at'] = $acknowledgementSentAt;
        }

        if ($adminNotifiedAt !== null) {
            $data['admin_notified_at'] = $adminNotifiedAt;
        }

        return (bool) Db::getInstance()->update(self::TABLE, $data, '`id_withdrawal` = ' . (int) $idWithdrawal);
    }

    public function updateStatus($idWithdrawal, $status)
    {
        if (!WithdrawalStatus::isValid($status)) {
            return false;
        }

        return (bool) Db::getInstance()->update(self::TABLE, [
            'status' => $status,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ], '`id_withdrawal` = ' . (int) $idWithdrawal);
    }

    public function assignOrder($idWithdrawal, $idOrder, $idCustomer = null)
    {
        $data = [
            'id_order' => (int) $idOrder,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ];

        if ($idCustomer !== null) {
            $data['id_customer'] = (int) $idCustomer;
        }

        return (bool) Db::getInstance()->update(self::TABLE, $data, '`id_withdrawal` = ' . (int) $idWithdrawal);
    }

    public function anonymizeOlderThan($cutoffDate)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . self::TABLE . '`
            SET
                `customer_name` = \'[anonymized]\',
                `customer_email` = CONCAT(\'anon+\', `id_withdrawal`, \'@example.invalid\'),
                `confirmation_email` = CONCAT(\'anon+\', `id_withdrawal`, \'@example.invalid\'),
                `customer_message` = NULL,
                `declaration_snapshot` = \'[anonymized]\',
                `ip_hash` = NULL,
                `user_agent_hash` = NULL,
                `anonymized_at` = \'' . pSQL(gmdate('Y-m-d H:i:s')) . '\',
                `updated_at` = \'' . pSQL(gmdate('Y-m-d H:i:s')) . '\'
            WHERE `submitted_at` < \'' . pSQL($cutoffDate) . '\'
            AND `anonymized_at` IS NULL';

        return (bool) Db::getInstance()->execute($sql);
    }

    private function buildWhere(array $filters)
    {
        $where = [];

        if (!empty($filters['status'])) {
            $where[] = '`status` = \'' . pSQL((string) $filters['status']) . '\'';
        }

        if (!empty($filters['id_shop'])) {
            $where[] = '`id_shop` = ' . (int) $filters['id_shop'];
        }

        if (!empty($filters['customer_email'])) {
            $where[] = '`customer_email` LIKE \'%' . pSQL((string) $filters['customer_email']) . '%\'';
        }

        if (!empty($filters['order_reference'])) {
            $where[] = '`order_reference` LIKE \'%' . pSQL((string) $filters['order_reference']) . '%\'';
        }

        if (!empty($filters['mail_status'])) {
            $where[] = '`mail_status` = \'' . pSQL((string) $filters['mail_status']) . '\'';
        }

        return $where;
    }
}
