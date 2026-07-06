<?php

namespace PrestaShop\Module\EuWithdrawalButton\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\EuWithdrawalButton\Service\IdempotencyService;

final class IdempotencyServiceTest extends TestCase
{
    public function testHashIsStableForSameSecret()
    {
        $service = new IdempotencyService();

        self::assertSame(
            $service->hashKey('abc', 'secret'),
            $service->hashKey('abc', 'secret')
        );
    }

    public function testGeneratedKeyIsNotEmpty()
    {
        $service = new IdempotencyService();

        self::assertNotSame('', $service->generateKey());
    }
}

