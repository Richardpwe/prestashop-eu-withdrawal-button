<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

use Mail;
use PrestaShop\Module\EuWithdrawalButton\DTO\MailPayload;

final class MailSender
{
    public function send(\Module $module, \Context $context, MailPayload $payload)
    {
        return (bool) Mail::Send(
            (int) $payload->idLang,
            $payload->template,
            $payload->subject,
            $payload->variables,
            $payload->recipientEmail,
            $payload->recipientName,
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

