<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

use Db;

final class OrderMatcher
{
    public function match(array $submission, \Context $context)
    {
        $orderReference = trim((string) ($submission['order_reference'] ?? ''));
        if ($orderReference === '') {
            return [
                'id_order' => null,
                'id_customer' => $this->getLoggedCustomerId($context),
                'manual_review_required' => true,
                'reason' => 'missing_order_reference',
            ];
        }

        $sql = 'SELECT o.`id_order`, o.`id_customer`, c.`email`
            FROM `' . _DB_PREFIX_ . 'orders` o
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = o.`id_customer`
            WHERE o.`reference` = \'' . pSQL($orderReference) . '\'
            AND o.`id_shop` = ' . (int) $context->shop->id . '
            ORDER BY o.`date_add` DESC';
        $order = Db::getInstance()->getRow($sql);

        if (!$order) {
            return [
                'id_order' => null,
                'id_customer' => $this->getLoggedCustomerId($context),
                'manual_review_required' => true,
                'reason' => 'order_not_found',
            ];
        }

        $loggedCustomerId = $this->getLoggedCustomerId($context);
        if ($loggedCustomerId && (int) $order['id_customer'] === $loggedCustomerId) {
            return [
                'id_order' => (int) $order['id_order'],
                'id_customer' => (int) $order['id_customer'],
                'manual_review_required' => false,
                'reason' => 'logged_customer_match',
            ];
        }

        $submittedEmail = strtolower(trim((string) ($submission['customer_email'] ?? '')));
        $orderEmail = strtolower(trim((string) ($order['email'] ?? '')));
        if ($submittedEmail !== '' && $orderEmail !== '' && $submittedEmail === $orderEmail) {
            return [
                'id_order' => (int) $order['id_order'],
                'id_customer' => (int) $order['id_customer'],
                'manual_review_required' => false,
                'reason' => 'email_order_reference_match',
            ];
        }

        return [
            'id_order' => null,
            'id_customer' => $loggedCustomerId,
            'manual_review_required' => true,
            'reason' => 'email_mismatch_or_unverified_guest',
        ];
    }

    public function suggestOrders(array $withdrawal, $limit = 5)
    {
        $where = [];
        if (!empty($withdrawal['order_reference'])) {
            $where[] = 'o.`reference` = \'' . pSQL($withdrawal['order_reference']) . '\'';
        }
        if (!empty($withdrawal['customer_email'])) {
            $where[] = 'c.`email` = \'' . pSQL($withdrawal['customer_email']) . '\'';
        }

        if (!$where) {
            return [];
        }

        $sql = 'SELECT o.`id_order`, o.`reference`, o.`date_add`, c.`email`
            FROM `' . _DB_PREFIX_ . 'orders` o
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = o.`id_customer`
            WHERE (' . implode(' OR ', $where) . ')
            AND o.`id_shop` = ' . (int) $withdrawal['id_shop'] . '
            ORDER BY o.`date_add` DESC
            LIMIT ' . (int) $limit;

        return Db::getInstance()->executeS($sql) ?: [];
    }

    private function getLoggedCustomerId(\Context $context)
    {
        if (isset($context->customer) && $context->customer && (int) $context->customer->id > 0 && $context->customer->isLogged()) {
            return (int) $context->customer->id;
        }

        return null;
    }
}
