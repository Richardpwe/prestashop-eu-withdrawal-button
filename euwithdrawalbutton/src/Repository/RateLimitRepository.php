<?php

namespace PrestaShop\Module\EuWithdrawalButton\Repository;

use Db;

final class RateLimitRepository
{
    const TABLE = 'euwb_rate_limit';

    public function isAllowed($idShop, $scope, $subjectHash, $maxAttempts = 8, $windowSeconds = 3600)
    {
        $windowStart = gmdate('Y-m-d H:00:00', time() - (time() % $windowSeconds));
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '`
            WHERE `id_shop` = ' . (int) $idShop . '
            AND `scope` = \'' . pSQL((string) $scope) . '\'
            AND `subject_hash` = \'' . pSQL((string) $subjectHash) . '\'
            AND `window_start` = \'' . pSQL($windowStart) . '\'';

        $row = Db::getInstance()->getRow($sql);
        if (!$row) {
            Db::getInstance()->insert(self::TABLE, [
                'id_shop' => (int) $idShop,
                'scope' => (string) $scope,
                'subject_hash' => (string) $subjectHash,
                'window_start' => $windowStart,
                'attempts' => 1,
            ]);

            return true;
        }

        $attempts = (int) $row['attempts'];
        if ($attempts >= (int) $maxAttempts) {
            return false;
        }

        Db::getInstance()->update(self::TABLE, [
            'attempts' => $attempts + 1,
        ], '`id_rate_limit` = ' . (int) $row['id_rate_limit']);

        return true;
    }
}
