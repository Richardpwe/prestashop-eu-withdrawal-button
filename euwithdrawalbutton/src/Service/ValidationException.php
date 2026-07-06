<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

final class ValidationException extends \InvalidArgumentException
{
    private $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Withdrawal submission validation failed.');
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

