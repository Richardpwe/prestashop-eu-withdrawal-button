<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

use Configuration;
use PrestaShop\Module\EuWithdrawalButton\Domain\MailStatus;
use PrestaShop\Module\EuWithdrawalButton\Domain\WithdrawalStatus;
use PrestaShop\Module\EuWithdrawalButton\DTO\WithdrawalSubmission;
use PrestaShop\Module\EuWithdrawalButton\Repository\RateLimitRepository;
use PrestaShop\Module\EuWithdrawalButton\Repository\WithdrawalItemRepository;
use PrestaShop\Module\EuWithdrawalButton\Repository\WithdrawalRepository;

final class SubmissionService
{
    private $validator;
    private $idempotency;
    private $matcher;
    private $deadlineCalculator;
    private $mailPayloadBuilder;
    private $mailSender;
    private $withdrawals;
    private $items;
    private $rateLimits;
    private $auditLogger;

    public function __construct(
        ValidationService $validator,
        IdempotencyService $idempotency,
        OrderMatcher $matcher,
        DeadlineCalculator $deadlineCalculator,
        MailPayloadBuilder $mailPayloadBuilder,
        MailSender $mailSender,
        WithdrawalRepository $withdrawals,
        WithdrawalItemRepository $items,
        RateLimitRepository $rateLimits,
        AuditLogger $auditLogger
    ) {
        $this->validator = $validator;
        $this->idempotency = $idempotency;
        $this->matcher = $matcher;
        $this->deadlineCalculator = $deadlineCalculator;
        $this->mailPayloadBuilder = $mailPayloadBuilder;
        $this->mailSender = $mailSender;
        $this->withdrawals = $withdrawals;
        $this->items = $items;
        $this->rateLimits = $rateLimits;
        $this->auditLogger = $auditLogger;
    }

    public function submit(array $input, \Context $context, \Module $module)
    {
        $this->validator->assertPublicInputIsValid($input);
        $data = $this->validator->normalizePublicInput($input);
        $data['id_shop'] = (int) $context->shop->id;
        $data['id_lang'] = (int) $context->language->id;
        $data['idempotency_key'] = $this->idempotency->ensureKey($data['idempotency_key']);

        $secret = $this->getSecret();
        $subjectHash = hash_hmac('sha256', $data['customer_email'] . '|' . $this->remoteAddress(), $secret);
        if (!$this->rateLimits->isAllowed((int) $context->shop->id, 'withdrawal_submit', $subjectHash)) {
            throw new ValidationException(['generic' => 'Please try again later.']);
        }

        $idempotencyHash = $this->idempotency->hashKey($data['idempotency_key'], $secret);
        $existing = $this->withdrawals->findByIdempotencyHash((int) $context->shop->id, $idempotencyHash);
        if ($existing) {
            return [
                'id_withdrawal' => (int) $existing['id_withdrawal'],
                'public_reference' => $existing['public_reference'],
                'mail_status' => $existing['mail_status'],
                'duplicate' => true,
            ];
        }

        $match = $this->matcher->match($data, $context);
        $data['id_customer'] = $match['id_customer'];
        $data['id_order'] = $match['id_order'];
        $submission = new WithdrawalSubmission($data);

        $now = gmdate('Y-m-d H:i:s');
        $publicReference = $this->generatePublicReference();
        $possiblyOutOfPeriod = $this->deadlineCalculator->isPossiblyOutOfConfiguredPeriod($submission->idOrder);
        $manualReviewRequired = (bool) $match['manual_review_required'] || $possiblyOutOfPeriod;
        $status = $manualReviewRequired ? WithdrawalStatus::MANUAL_REVIEW : WithdrawalStatus::MATCHED;
        $snapshot = json_encode($submission->toDeclarationArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $idWithdrawal = $this->withdrawals->insert([
            'id_shop' => $submission->idShop,
            'id_lang' => $submission->idLang,
            'id_customer' => $submission->idCustomer,
            'id_order' => $submission->idOrder,
            'order_reference' => $submission->orderReference,
            'invoice_number' => $submission->invoiceNumber,
            'customer_name' => $submission->customerName,
            'customer_email' => $submission->customerEmail,
            'confirmation_email' => $submission->confirmationEmail,
            'contract_identification_text' => $submission->contractIdentificationText,
            'withdrawal_scope' => $submission->withdrawalScope,
            'customer_message' => $submission->customerMessage,
            'declaration_snapshot' => $snapshot,
            'status' => $status,
            'manual_review_required' => $manualReviewRequired ? 1 : 0,
            'possibly_out_of_period' => $possiblyOutOfPeriod ? 1 : 0,
            'submitted_at' => $now,
            'shop_timezone' => $this->shopTimezone(),
            'acknowledgement_sent_at' => null,
            'admin_notified_at' => null,
            'mail_status' => MailStatus::PENDING,
            'public_reference' => $publicReference,
            'idempotency_key_hash' => $idempotencyHash,
            'ip_hash' => $this->optionalRequestHash($this->remoteAddress(), $secret),
            'user_agent_hash' => $this->optionalRequestHash($_SERVER['HTTP_USER_AGENT'] ?? '', $secret),
            'created_at' => $now,
            'updated_at' => $now,
            'anonymized_at' => null,
        ]);

        $this->items->insertItems($idWithdrawal, $submission->items);
        $this->auditLogger->log($idWithdrawal, 'submitted', null, $status, 'Match result: ' . $match['reason']);

        $withdrawal = $this->withdrawals->findById($idWithdrawal);
        $items = $this->items->findByWithdrawal($idWithdrawal);
        $customerMailSent = $this->mailSender->send(
            $module,
            $context,
            $this->mailPayloadBuilder->buildCustomerAcknowledgement($withdrawal, $items, $context)
        );

        $adminMailSent = true;
        $adminEmail = trim((string) Configuration::get(\EuWithdrawalButton::CONFIG_ADMIN_EMAIL));
        if ($adminEmail !== '') {
            $adminMailSent = $this->mailSender->send(
                $module,
                $context,
                $this->mailPayloadBuilder->buildAdminNotification($withdrawal, $items, $context, $adminEmail)
            );
        }

        $mailStatus = $customerMailSent && $adminMailSent ? MailStatus::SENT : ($customerMailSent || $adminMailSent ? MailStatus::PARTIAL : MailStatus::FAILED);
        $this->withdrawals->updateMailStatus(
            $idWithdrawal,
            $mailStatus,
            $customerMailSent ? gmdate('Y-m-d H:i:s') : null,
            $adminMailSent && $adminEmail !== '' ? gmdate('Y-m-d H:i:s') : null
        );
        $this->auditLogger->log($idWithdrawal, 'mail_sent', null, null, 'Mail status: ' . $mailStatus);

        return [
            'id_withdrawal' => $idWithdrawal,
            'public_reference' => $publicReference,
            'mail_status' => $mailStatus,
            'duplicate' => false,
        ];
    }

    private function generatePublicReference()
    {
        $random = function_exists('random_bytes') ? strtoupper(bin2hex(random_bytes(4))) : strtoupper(substr(sha1(uniqid('', true)), 0, 8));

        return 'EWB-' . gmdate('Ymd') . '-' . $random;
    }

    private function getSecret()
    {
        if (defined('_COOKIE_KEY_')) {
            return _COOKIE_KEY_;
        }

        return 'euwithdrawalbutton';
    }

    private function remoteAddress()
    {
        if (class_exists('\\Tools')) {
            return (string) \Tools::getRemoteAddr();
        }

        return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    }

    private function optionalRequestHash($value, $secret)
    {
        if (!Configuration::get(\EuWithdrawalButton::CONFIG_HASH_IP)) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return hash_hmac('sha256', $value, (string) $secret);
    }

    private function shopTimezone()
    {
        $timezone = (string) Configuration::get('PS_TIMEZONE');

        return $timezone ?: date_default_timezone_get();
    }
}

