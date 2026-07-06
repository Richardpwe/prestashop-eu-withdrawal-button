<?php

namespace PrestaShop\Module\EuWithdrawalButton\DTO;

final class MailPayload
{
    public $idLang;
    public $template;
    public $subject;
    public $recipientEmail;
    public $recipientName;
    public $variables;

    public function __construct($idLang, $template, $subject, $recipientEmail, $recipientName, array $variables)
    {
        $this->idLang = (int) $idLang;
        $this->template = (string) $template;
        $this->subject = (string) $subject;
        $this->recipientEmail = (string) $recipientEmail;
        $this->recipientName = (string) $recipientName;
        $this->variables = $variables;
    }
}

