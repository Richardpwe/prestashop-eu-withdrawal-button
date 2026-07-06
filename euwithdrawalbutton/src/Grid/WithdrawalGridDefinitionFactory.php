<?php

namespace PrestaShop\Module\EuWithdrawalButton\Grid;

final class WithdrawalGridDefinitionFactory
{
    public function getColumns()
    {
        return [
            'public_reference',
            'status',
            'submitted_at',
            'id_shop',
            'id_lang',
            'customer_email',
            'order_reference',
            'id_order',
            'mail_status',
        ];
    }
}

