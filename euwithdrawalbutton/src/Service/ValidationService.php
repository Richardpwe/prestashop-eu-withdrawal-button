<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

use PrestaShop\Module\EuWithdrawalButton\Domain\WithdrawalScope;

final class ValidationService
{
    public function validatePublicInput(array $input)
    {
        $errors = [];

        if (!empty($input['euwb_website'])) {
            $errors['generic'] = 'The request could not be processed.';
        }

        if (trim((string) ($input['customer_name'] ?? '')) === '') {
            $errors['customer_name'] = 'Please enter your name.';
        }

        $email = trim((string) ($input['customer_email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['customer_email'] = 'Please enter a valid email address.';
        }

        if (trim((string) ($input['contract_identification_text'] ?? '')) === '') {
            $errors['contract_identification_text'] = 'Please identify the contract or affected part of the contract.';
        }

        if (!empty($input['quantity_requested']) && (int) $input['quantity_requested'] < 1) {
            $errors['quantity_requested'] = 'Quantity must be at least 1.';
        }

        return $errors;
    }

    public function assertPublicInputIsValid(array $input)
    {
        $errors = $this->validatePublicInput($input);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }

    public function normalizePublicInput(array $input)
    {
        $customerName = $this->cleanText($input['customer_name'] ?? '', 255);
        $customerEmail = strtolower($this->cleanText($input['customer_email'] ?? '', 255));
        $confirmationEmail = strtolower($this->cleanText($input['confirmation_email'] ?? $customerEmail, 255));
        $contractIdentification = $this->cleanText($input['contract_identification_text'] ?? '', 4000);
        $customerMessage = $this->cleanText($input['customer_message'] ?? '', 4000);
        $orderReference = $this->cleanText($input['order_reference'] ?? '', 64);
        $invoiceNumber = $this->cleanText($input['invoice_number'] ?? '', 64);
        $scope = WithdrawalScope::normalize($input['withdrawal_scope'] ?? WithdrawalScope::UNKNOWN);

        return [
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'confirmation_email' => $confirmationEmail ?: $customerEmail,
            'contract_identification_text' => $contractIdentification,
            'withdrawal_scope' => $scope,
            'customer_message' => $customerMessage ?: null,
            'order_reference' => $orderReference ?: null,
            'invoice_number' => $invoiceNumber ?: null,
            'idempotency_key' => $this->cleanText($input['idempotency_key'] ?? '', 128),
            'items' => $this->normalizeItems($input),
        ];
    }

    public function cleanText($value, $maxLength)
    {
        $value = str_replace("\0", '', (string) $value);
        $value = trim(preg_replace('/[ \t]+/', ' ', $value));
        $value = strip_tags($value);

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maxLength, 'UTF-8');
        }

        return substr($value, 0, $maxLength);
    }

    private function normalizeItems(array $input)
    {
        $items = [];
        if (!empty($input['items_json'])) {
            $decoded = json_decode((string) $input['items_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    $items[] = [
                        'id_order_detail' => isset($item['id_order_detail']) ? (int) $item['id_order_detail'] : null,
                        'id_product' => isset($item['id_product']) ? (int) $item['id_product'] : null,
                        'id_product_attribute' => isset($item['id_product_attribute']) ? (int) $item['id_product_attribute'] : null,
                        'product_name_snapshot' => $this->cleanText($item['product_name_snapshot'] ?? '', 255) ?: null,
                        'quantity_requested' => isset($item['quantity_requested']) ? max(1, (int) $item['quantity_requested']) : null,
                        'free_text_item' => $this->cleanText($item['free_text_item'] ?? '', 4000) ?: null,
                    ];
                }
            }
        }

        if (!$items && !empty($input['affected_contract_part'])) {
            $items[] = [
                'free_text_item' => $this->cleanText($input['affected_contract_part'], 4000),
            ];
        }

        return $items;
    }
}

