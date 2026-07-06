<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

use Configuration;

final class ComplianceChecker
{
    private $module;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    public function check(\Context $context)
    {
        $checks = [];
        $checks[] = $this->checkRoute($context);
        $checks[] = $this->checkFooterHook();
        $checks[] = $this->checkLabels($context);
        $checks[] = $this->checkMailTemplate();
        $checks[] = $this->checkPrivacyUrl();
        $checks[] = $this->checkRetention();
        $checks[] = $this->result('login_only', 'Login-only mode disabled', 'pass', 'Guest submissions are always available in the public controller.');
        $checks[] = $this->result('compatibility', 'Compatibility target', 'warning', 'PS 8.0/8.1/8.2 are official targets; PS 9 remains experimental until milestone 1.2.');

        return $checks;
    }

    private function checkRoute(\Context $context)
    {
        $url = $context->link->getModuleLink($this->module->name, 'withdraw', [], true);

        return $this->result(
            'public_route',
            'Public route generated',
            $url ? 'pass' : 'error',
            $url ?: 'PrestaShop did not generate a module link.'
        );
    }

    private function checkFooterHook()
    {
        return $this->result(
            'footer_hook',
            'Footer hook active',
            Configuration::get(\EuWithdrawalButton::CONFIG_FOOTER_ENABLED) ? 'pass' : 'warning',
            Configuration::get(\EuWithdrawalButton::CONFIG_FOOTER_ENABLED) ? 'Footer link is enabled.' : 'Footer link is disabled.'
        );
    }

    private function checkLabels(\Context $context)
    {
        $idLang = (int) $context->language->id;
        $link = Configuration::get(\EuWithdrawalButton::CONFIG_LINK_LABEL_PREFIX . $idLang);
        $final = Configuration::get(\EuWithdrawalButton::CONFIG_FINAL_LABEL_PREFIX . $idLang);
        $ok = $link && $final;

        return $this->result(
            'labels',
            'Recommended labels configured',
            $ok ? 'pass' : 'warning',
            'Current public label: ' . ($link ?: '[missing]') . '; final label: ' . ($final ?: '[missing]')
        );
    }

    private function checkMailTemplate()
    {
        $path = dirname(__DIR__, 2) . '/mails/de/withdrawal_acknowledgement.txt';
        $required = [
            '{withdrawal_reference}',
            '{submitted_at}',
            '{submitted_at_timezone}',
            '{contract_identification}',
            '{withdrawal_content}',
        ];

        $content = is_file($path) ? file_get_contents($path) : '';
        foreach ($required as $variable) {
            if (strpos($content, $variable) === false) {
                return $this->result('mail_template', 'Required mail variables', 'error', 'Missing variable ' . $variable . ' in German acknowledgement template.');
            }
        }

        return $this->result('mail_template', 'Required mail variables', 'pass', 'Acknowledgement template contains required variables.');
    }

    private function checkPrivacyUrl()
    {
        $url = trim((string) Configuration::get(\EuWithdrawalButton::CONFIG_PRIVACY_URL));

        return $this->result('privacy_url', 'Privacy URL configured', $url ? 'pass' : 'warning', $url ?: 'No privacy URL configured.');
    }

    private function checkRetention()
    {
        $days = (int) Configuration::get(\EuWithdrawalButton::CONFIG_RETENTION_DAYS);

        return $this->result('retention', 'Retention configured', $days > 0 ? 'pass' : 'warning', $days > 0 ? $days . ' days' : 'Retention is disabled.');
    }

    private function result($key, $label, $status, $message)
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'message' => $message,
        ];
    }
}

