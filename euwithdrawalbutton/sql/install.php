<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

return [
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'euwb_withdrawal` (
        `id_withdrawal` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_shop` INT UNSIGNED NOT NULL,
        `id_lang` INT UNSIGNED NOT NULL,
        `id_customer` INT UNSIGNED NULL,
        `id_order` INT UNSIGNED NULL,
        `order_reference` VARCHAR(64) NULL,
        `invoice_number` VARCHAR(64) NULL,
        `customer_name` VARCHAR(255) NOT NULL,
        `customer_email` VARCHAR(255) NOT NULL,
        `confirmation_email` VARCHAR(255) NOT NULL,
        `contract_identification_text` TEXT NOT NULL,
        `withdrawal_scope` VARCHAR(32) NOT NULL DEFAULT \'unknown\',
        `customer_message` TEXT NULL,
        `declaration_snapshot` MEDIUMTEXT NOT NULL,
        `status` VARCHAR(32) NOT NULL DEFAULT \'new\',
        `manual_review_required` TINYINT(1) NOT NULL DEFAULT 0,
        `possibly_out_of_period` TINYINT(1) NOT NULL DEFAULT 0,
        `submitted_at` DATETIME NOT NULL,
        `shop_timezone` VARCHAR(64) NOT NULL,
        `acknowledgement_sent_at` DATETIME NULL,
        `admin_notified_at` DATETIME NULL,
        `mail_status` VARCHAR(32) NOT NULL DEFAULT \'pending\',
        `public_reference` VARCHAR(40) NOT NULL,
        `idempotency_key_hash` CHAR(64) NOT NULL,
        `ip_hash` CHAR(64) NULL,
        `user_agent_hash` CHAR(64) NULL,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        `anonymized_at` DATETIME NULL,
        PRIMARY KEY (`id_withdrawal`),
        UNIQUE KEY `uniq_public_reference` (`public_reference`),
        UNIQUE KEY `uniq_idempotency` (`id_shop`, `idempotency_key_hash`),
        KEY `idx_status_date` (`status`, `submitted_at`),
        KEY `idx_shop_lang` (`id_shop`, `id_lang`),
        KEY `idx_customer_email` (`customer_email`(191)),
        KEY `idx_order_reference` (`order_reference`),
        KEY `idx_id_order` (`id_order`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'euwb_withdrawal_item` (
        `id_withdrawal_item` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_withdrawal` INT UNSIGNED NOT NULL,
        `id_order_detail` INT UNSIGNED NULL,
        `id_product` INT UNSIGNED NULL,
        `id_product_attribute` INT UNSIGNED NULL,
        `product_name_snapshot` VARCHAR(255) NULL,
        `quantity_requested` INT UNSIGNED NULL,
        `free_text_item` TEXT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id_withdrawal_item`),
        KEY `idx_withdrawal` (`id_withdrawal`),
        KEY `idx_order_detail` (`id_order_detail`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'euwb_withdrawal_log` (
        `id_withdrawal_log` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_withdrawal` INT UNSIGNED NOT NULL,
        `id_employee` INT UNSIGNED NULL,
        `event_type` VARCHAR(64) NOT NULL,
        `old_status` VARCHAR(32) NULL,
        `new_status` VARCHAR(32) NULL,
        `note` TEXT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id_withdrawal_log`),
        KEY `idx_withdrawal_created` (`id_withdrawal`, `created_at`),
        KEY `idx_employee` (`id_employee`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'euwb_rate_limit` (
        `id_rate_limit` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_shop` INT UNSIGNED NOT NULL,
        `scope` VARCHAR(64) NOT NULL,
        `subject_hash` CHAR(64) NOT NULL,
        `window_start` DATETIME NOT NULL,
        `attempts` INT UNSIGNED NOT NULL DEFAULT 1,
        PRIMARY KEY (`id_rate_limit`),
        UNIQUE KEY `uniq_window` (`id_shop`, `scope`, `subject_hash`, `window_start`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',
];
