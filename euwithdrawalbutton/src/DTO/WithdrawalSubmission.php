<?php

namespace PrestaShop\Module\EuWithdrawalButton\DTO;

use PrestaShop\Module\EuWithdrawalButton\Domain\WithdrawalScope;

final class WithdrawalSubmission
{
    public $idShop;
    public $idLang;
    public $idCustomer;
    public $idOrder;
    public $orderReference;
    public $invoiceNumber;
    public $customerName;
    public $customerEmail;
    public $confirmationEmail;
    public $contractIdentificationText;
    public $withdrawalScope;
    public $customerMessage;
    public $idempotencyKey;
    public $items = [];

    public function __construct(array $data)
    {
        $this->idShop = (int) $data['id_shop'];
        $this->idLang = (int) $data['id_lang'];
        $this->idCustomer = isset($data['id_customer']) ? (int) $data['id_customer'] : null;
        $this->idOrder = isset($data['id_order']) ? (int) $data['id_order'] : null;
        $this->orderReference = isset($data['order_reference']) ? trim((string) $data['order_reference']) : null;
        $this->invoiceNumber = isset($data['invoice_number']) ? trim((string) $data['invoice_number']) : null;
        $this->customerName = trim((string) $data['customer_name']);
        $this->customerEmail = trim((string) $data['customer_email']);
        $this->confirmationEmail = trim((string) ($data['confirmation_email'] ?? $data['customer_email']));
        $this->contractIdentificationText = trim((string) $data['contract_identification_text']);
        $this->withdrawalScope = WithdrawalScope::normalize($data['withdrawal_scope'] ?? WithdrawalScope::UNKNOWN);
        $this->customerMessage = isset($data['customer_message']) ? trim((string) $data['customer_message']) : null;
        $this->idempotencyKey = trim((string) $data['idempotency_key']);

        foreach (($data['items'] ?? []) as $item) {
            $this->items[] = $item instanceof WithdrawalItemSubmission ? $item : new WithdrawalItemSubmission($item);
        }
    }

    public function toDeclarationArray()
    {
        return [
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'confirmation_email' => $this->confirmationEmail,
            'order_reference' => $this->orderReference,
            'invoice_number' => $this->invoiceNumber,
            'contract_identification_text' => $this->contractIdentificationText,
            'withdrawal_scope' => $this->withdrawalScope,
            'customer_message' => $this->customerMessage,
            'items' => array_map(function (WithdrawalItemSubmission $item) {
                return $item->toArray();
            }, $this->items),
        ];
    }
}

