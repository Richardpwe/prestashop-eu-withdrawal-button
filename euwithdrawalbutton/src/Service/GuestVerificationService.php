<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

use Db;
use Mail;

final class GuestVerificationService
{
    public function createToken(array $payload, $secret, $ttlSeconds = 900)
    {
        $payload['expires_at'] = time() + (int) $ttlSeconds;
        $encoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $encoded, (string) $secret);

        return $encoded . '.' . $signature;
    }

    public function verifyToken($token, $secret)
    {
        $parts = explode('.', (string) $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        list($encoded, $signature) = $parts;
        $expected = hash_hmac('sha256', $encoded, (string) $secret);
        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $encodedPadded = $encoded . str_repeat('=', (4 - strlen($encoded) % 4) % 4);
        $json = base64_decode(strtr($encodedPadded, '-_', '+/'));
        $payload = json_decode((string) $json, true);
        if (!is_array($payload) || empty($payload['expires_at']) || (int) $payload['expires_at'] < time()) {
            return null;
        }

        return $payload;
    }

    public function sendVerificationLinkIfSafeMatch(array $input, \Context $context, \Module $module, $secret)
    {
        $orderReference = trim((string) ($input['order_reference'] ?? ''));
        $email = strtolower(trim((string) ($input['customer_email'] ?? '')));
        if ($orderReference === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $sql = 'SELECT o.`id_order`, o.`reference`, o.`id_customer`, c.`email`
            FROM `' . _DB_PREFIX_ . 'orders` o
            INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = o.`id_customer`
            WHERE o.`reference` = \'' . pSQL($orderReference) . '\'
            AND c.`email` = \'' . pSQL($email) . '\'
            AND o.`id_shop` = ' . (int) $context->shop->id . '
            ORDER BY o.`date_add` DESC';
        $order = Db::getInstance()->getRow($sql);
        if (!$order) {
            return false;
        }

        $token = $this->createToken([
            'id_order' => (int) $order['id_order'],
            'order_reference' => (string) $order['reference'],
            'customer_email' => (string) $order['email'],
        ], $secret, 900);

        $verificationUrl = $context->link->getModuleLink($module->name, 'verify', ['token' => $token], true);
        $templateVars = [
            '{customer_email}' => (string) $order['email'],
            '{order_reference}' => (string) $order['reference'],
            '{verification_url}' => $verificationUrl,
            '{shop_name}' => (string) \Configuration::get('PS_SHOP_NAME'),
            '{shop_url}' => $context->link->getPageLink('index', true),
        ];

        $subject = strtolower((string) \Language::getIsoById((int) $context->language->id)) === 'de'
            ? 'Verifizierung Ihrer Bestellung'
            : 'Verify your order';

        return (bool) Mail::Send(
            (int) $context->language->id,
            'guest_verification_link',
            $subject,
            $templateVars,
            (string) $order['email'],
            null,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_ . $module->name . '/mails/',
            false,
            (int) $context->shop->id
        );
    }
}
