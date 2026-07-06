<?php

namespace PrestaShop\Module\EuWithdrawalButton\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\EuWithdrawalButton\Service\ValidationService;

final class ValidationServiceTest extends TestCase
{
    public function testRequiredFieldsAreValidated()
    {
        $validator = new ValidationService();
        $errors = $validator->validatePublicInput([]);

        self::assertArrayHasKey('customer_name', $errors);
        self::assertArrayHasKey('customer_email', $errors);
        self::assertArrayHasKey('contract_identification_text', $errors);
    }

    public function testNoWithdrawalReasonIsRequired()
    {
        $validator = new ValidationService();
        $errors = $validator->validatePublicInput([
            'customer_name' => 'Erika Muster',
            'customer_email' => 'erika@example.com',
            'contract_identification_text' => 'Order ABC123',
        ]);

        self::assertSame([], $errors);
    }

    public function testHoneypotCreatesGenericError()
    {
        $validator = new ValidationService();
        $errors = $validator->validatePublicInput([
            'customer_name' => 'Erika Muster',
            'customer_email' => 'erika@example.com',
            'contract_identification_text' => 'Order ABC123',
            'euwb_website' => 'bot',
        ]);

        self::assertArrayHasKey('generic', $errors);
    }
}

