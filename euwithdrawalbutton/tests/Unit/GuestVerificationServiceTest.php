<?php

namespace PrestaShop\Module\EuWithdrawalButton\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\EuWithdrawalButton\Service\GuestVerificationService;

final class GuestVerificationServiceTest extends TestCase
{
    public function testSignedTokenCanBeVerified()
    {
        $service = new GuestVerificationService();
        $token = $service->createToken(['order_reference' => 'ABC123'], 'secret', 60);
        $payload = $service->verifyToken($token, 'secret');

        self::assertSame('ABC123', $payload['order_reference']);
    }

    public function testWrongSecretFails()
    {
        $service = new GuestVerificationService();
        $token = $service->createToken(['order_reference' => 'ABC123'], 'secret', 60);

        self::assertNull($service->verifyToken($token, 'other-secret'));
    }
}

