<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

use Configuration;
use PrestaShop\Module\EuWithdrawalButton\DTO\MailPayload;

final class MailPayloadBuilder
{
    public function buildCustomerAcknowledgement(array $withdrawal, array $items, \Context $context)
    {
        $variables = $this->buildVariables($withdrawal, $items, $context);
        $subject = $this->isGerman((int) $withdrawal['id_lang'])
            ? 'Eingangsbestätigung Ihres Widerrufs'
            : 'Receipt confirmation of your withdrawal';

        return new MailPayload(
            (int) $withdrawal['id_lang'],
            'withdrawal_acknowledgement',
            $subject,
            (string) $withdrawal['confirmation_email'],
            (string) $withdrawal['customer_name'],
            $variables
        );
    }

    public function buildAdminNotification(array $withdrawal, array $items, \Context $context, $adminEmail)
    {
        $variables = $this->buildVariables($withdrawal, $items, $context);
        $subject = 'New withdrawal declaration ' . $withdrawal['public_reference'];

        return new MailPayload(
            (int) $withdrawal['id_lang'],
            'admin_notification',
            $subject,
            (string) $adminEmail,
            (string) Configuration::get('PS_SHOP_NAME'),
            $variables
        );
    }

    public function buildVariables(array $withdrawal, array $items, \Context $context)
    {
        $shopTimezone = (string) ($withdrawal['shop_timezone'] ?: date_default_timezone_get());
        $submittedAt = (string) $withdrawal['submitted_at'];
        $submittedAtTimezone = $this->formatInTimezone($submittedAt, $shopTimezone);
        $withdrawalContent = $this->buildWithdrawalContent($withdrawal, $items);

        return [
            '{withdrawal_reference}' => (string) $withdrawal['public_reference'],
            '{customer_name}' => (string) $withdrawal['customer_name'],
            '{customer_email}' => (string) $withdrawal['customer_email'],
            '{submitted_at}' => $submittedAt . ' UTC',
            '{submitted_at_timezone}' => $submittedAtTimezone,
            '{contract_identification}' => (string) $withdrawal['contract_identification_text'],
            '{withdrawal_content}' => $withdrawalContent,
            '{shop_name}' => (string) Configuration::get('PS_SHOP_NAME'),
            '{shop_url}' => $context->link->getPageLink('index', true),
            '{privacy_policy_url}' => (string) Configuration::get(\EuWithdrawalButton::CONFIG_PRIVACY_URL),
        ];
    }

    private function buildWithdrawalContent(array $withdrawal, array $items)
    {
        $lines = [
            'Reference: ' . $withdrawal['public_reference'],
            'Name: ' . $withdrawal['customer_name'],
            'Email: ' . $withdrawal['customer_email'],
            'Contract identification: ' . $withdrawal['contract_identification_text'],
        ];

        if (!empty($withdrawal['order_reference'])) {
            $lines[] = 'Order reference: ' . $withdrawal['order_reference'];
        }

        if (!empty($withdrawal['invoice_number'])) {
            $lines[] = 'Invoice number: ' . $withdrawal['invoice_number'];
        }

        if (!empty($withdrawal['customer_message'])) {
            $lines[] = 'Customer message: ' . $withdrawal['customer_message'];
        }

        if ($items) {
            $lines[] = 'Affected items:';
            foreach ($items as $item) {
                $label = $item['product_name_snapshot'] ?: $item['free_text_item'] ?: ('Order detail #' . $item['id_order_detail']);
                $quantity = $item['quantity_requested'] ? ' x ' . (int) $item['quantity_requested'] : '';
                $lines[] = '- ' . $label . $quantity;
            }
        }

        return implode("\n", $lines);
    }

    private function formatInTimezone($dateUtc, $timezone)
    {
        try {
            $date = new \DateTimeImmutable($dateUtc, new \DateTimeZone('UTC'));
            return $date->setTimezone(new \DateTimeZone($timezone))->format('Y-m-d H:i:s T');
        } catch (\Exception $exception) {
            return $dateUtc . ' UTC';
        }
    }

    private function isGerman($idLang)
    {
        $iso = \Language::getIsoById((int) $idLang);

        return strtolower((string) $iso) === 'de';
    }
}

