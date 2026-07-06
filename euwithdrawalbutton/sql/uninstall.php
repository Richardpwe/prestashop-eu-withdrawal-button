<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

return [
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'euwb_rate_limit`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'euwb_withdrawal_log`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'euwb_withdrawal_item`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'euwb_withdrawal`',
];

