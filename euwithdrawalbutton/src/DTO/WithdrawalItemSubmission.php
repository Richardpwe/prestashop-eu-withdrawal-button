<?php

namespace PrestaShop\Module\EuWithdrawalButton\DTO;

final class WithdrawalItemSubmission
{
    public $idOrderDetail;
    public $idProduct;
    public $idProductAttribute;
    public $productNameSnapshot;
    public $quantityRequested;
    public $freeTextItem;

    public function __construct(array $data)
    {
        $this->idOrderDetail = isset($data['id_order_detail']) ? (int) $data['id_order_detail'] : null;
        $this->idProduct = isset($data['id_product']) ? (int) $data['id_product'] : null;
        $this->idProductAttribute = isset($data['id_product_attribute']) ? (int) $data['id_product_attribute'] : null;
        $this->productNameSnapshot = isset($data['product_name_snapshot']) ? (string) $data['product_name_snapshot'] : null;
        $this->quantityRequested = isset($data['quantity_requested']) ? (int) $data['quantity_requested'] : null;
        $this->freeTextItem = isset($data['free_text_item']) ? (string) $data['free_text_item'] : null;
    }

    public function toArray()
    {
        return [
            'id_order_detail' => $this->idOrderDetail,
            'id_product' => $this->idProduct,
            'id_product_attribute' => $this->idProductAttribute,
            'product_name_snapshot' => $this->productNameSnapshot,
            'quantity_requested' => $this->quantityRequested,
            'free_text_item' => $this->freeTextItem,
        ];
    }
}

