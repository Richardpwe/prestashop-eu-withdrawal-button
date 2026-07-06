<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

final class IdempotencyService
{
    public function generateKey()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(16));
        }

        return sha1(uniqid('', true));
    }

    public function ensureKey($key)
    {
        $key = trim((string) $key);

        return $key !== '' ? $key : $this->generateKey();
    }

    public function hashKey($key, $secret)
    {
        return hash_hmac('sha256', (string) $key, (string) $secret);
    }
}

