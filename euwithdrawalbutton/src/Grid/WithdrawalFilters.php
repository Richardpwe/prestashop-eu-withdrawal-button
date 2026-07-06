<?php

namespace PrestaShop\Module\EuWithdrawalButton\Grid;

final class WithdrawalFilters
{
    public static function fromArray(array $input)
    {
        return [
            'status' => $input['status'] ?? null,
            'id_shop' => $input['id_shop'] ?? null,
            'customer_email' => $input['customer_email'] ?? null,
            'order_reference' => $input['order_reference'] ?? null,
            'mail_status' => $input['mail_status'] ?? null,
        ];
    }
}

