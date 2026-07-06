<?php

namespace PrestaShop\Module\EuWithdrawalButton\Repository;

use Db;
use PrestaShop\Module\EuWithdrawalButton\DTO\WithdrawalItemSubmission;

final class WithdrawalItemRepository
{
    const TABLE = 'euwb_withdrawal_item';

    public function insertItems($idWithdrawal, array $items)
    {
        foreach ($items as $item) {
            if (!$item instanceof WithdrawalItemSubmission) {
                $item = new WithdrawalItemSubmission((array) $item);
            }

            Db::getInstance()->insert(self::TABLE, [
                'id_withdrawal' => (int) $idWithdrawal,
                'id_order_detail' => $item->idOrderDetail,
                'id_product' => $item->idProduct,
                'id_product_attribute' => $item->idProductAttribute,
                'product_name_snapshot' => $item->productNameSnapshot,
                'quantity_requested' => $item->quantityRequested,
                'free_text_item' => $item->freeTextItem,
                'created_at' => gmdate('Y-m-d H:i:s'),
            ]);
        }
    }

    public function findByWithdrawal($idWithdrawal)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '`
            WHERE `id_withdrawal` = ' . (int) $idWithdrawal . '
            ORDER BY `id_withdrawal_item` ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }
}

