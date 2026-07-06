<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

use Configuration;
use Db;

final class DeadlineCalculator
{
    public function isPossiblyOutOfConfiguredPeriod($idOrder)
    {
        $idOrder = (int) $idOrder;
        if ($idOrder <= 0) {
            return false;
        }

        $periodDays = (int) Configuration::get('EUWB_PERIOD_DAYS');
        if ($periodDays <= 0) {
            return false;
        }

        $sql = 'SELECT `date_add` FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_order` = ' . $idOrder;
        $dateAdd = Db::getInstance()->getValue($sql);
        if (!$dateAdd) {
            return false;
        }

        $deadline = strtotime($dateAdd . ' +' . $periodDays . ' days');
        if (!$deadline) {
            return false;
        }

        return time() > $deadline;
    }
}

