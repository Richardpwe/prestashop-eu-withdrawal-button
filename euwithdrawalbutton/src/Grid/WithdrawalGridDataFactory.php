<?php

namespace PrestaShop\Module\EuWithdrawalButton\Grid;

use PrestaShop\Module\EuWithdrawalButton\Repository\WithdrawalRepository;

final class WithdrawalGridDataFactory
{
    private $repository;

    public function __construct(WithdrawalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getData(array $filters = [], $limit = 100, $offset = 0)
    {
        return [
            'records' => $this->repository->search($filters, $limit, $offset),
            'total' => $this->repository->count($filters),
        ];
    }
}

