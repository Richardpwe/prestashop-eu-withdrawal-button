<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

use PrestaShop\Module\EuWithdrawalButton\Repository\RateLimitRepository;
use PrestaShop\Module\EuWithdrawalButton\Repository\WithdrawalItemRepository;
use PrestaShop\Module\EuWithdrawalButton\Repository\WithdrawalLogRepository;
use PrestaShop\Module\EuWithdrawalButton\Repository\WithdrawalRepository;

final class ServiceFactory
{
    private $module;
    private $withdrawals;
    private $items;
    private $logs;
    private $rateLimits;

    public function __construct(\Module $module = null)
    {
        $this->module = $module;
    }

    public function withdrawalRepository()
    {
        return $this->withdrawals ?: $this->withdrawals = new WithdrawalRepository();
    }

    public function withdrawalItemRepository()
    {
        return $this->items ?: $this->items = new WithdrawalItemRepository();
    }

    public function withdrawalLogRepository()
    {
        return $this->logs ?: $this->logs = new WithdrawalLogRepository();
    }

    public function rateLimitRepository()
    {
        return $this->rateLimits ?: $this->rateLimits = new RateLimitRepository();
    }

    public function validationService()
    {
        return new ValidationService();
    }

    public function idempotencyService()
    {
        return new IdempotencyService();
    }

    public function orderMatcher()
    {
        return new OrderMatcher();
    }

    public function deadlineCalculator()
    {
        return new DeadlineCalculator();
    }

    public function mailPayloadBuilder()
    {
        return new MailPayloadBuilder();
    }

    public function mailSender()
    {
        return new MailSender();
    }

    public function complianceChecker()
    {
        return new ComplianceChecker($this->module);
    }

    public function retentionAnonymizer()
    {
        return new RetentionAnonymizer($this->withdrawalRepository());
    }

    public function auditLogger()
    {
        return new AuditLogger($this->withdrawalLogRepository());
    }

    public function csvExporter()
    {
        return new CsvExporter();
    }

    public function guestVerificationService()
    {
        return new GuestVerificationService();
    }

    public function submissionService()
    {
        return new SubmissionService(
            $this->validationService(),
            $this->idempotencyService(),
            $this->orderMatcher(),
            $this->deadlineCalculator(),
            $this->mailPayloadBuilder(),
            $this->mailSender(),
            $this->withdrawalRepository(),
            $this->withdrawalItemRepository(),
            $this->rateLimitRepository(),
            $this->auditLogger()
        );
    }
}

