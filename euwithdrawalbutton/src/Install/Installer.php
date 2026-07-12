<?php

namespace PrestaShop\Module\EuWithdrawalButton\Install;

use Configuration;
use Db;
use Language;

final class Installer
{
    private $module;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    public function install()
    {
        if (!$this->executeSqlFile('install.php')) {
            return false;
        }

        if (!$this->installDefaultConfiguration()) {
            return false;
        }

        return true;
    }

    private function executeSqlFile($file)
    {
        $path = dirname(__DIR__, 2) . '/sql/' . $file;
        $statements = include $path;

        foreach ($statements as $statement) {
            if (!Db::getInstance()->execute($statement)) {
                $this->logInstallError('SQL install failed: ' . Db::getInstance()->getMsgError());
                return false;
            }
        }

        return true;
    }

    private function installDefaultConfiguration()
    {
        $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_ENABLED, 1);
        $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_FOOTER_ENABLED, 1);
        $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_ACCOUNT_ENABLED, 1);
        $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_ORDER_DETAIL_ENABLED, 1);
        $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_ORDER_CONFIRMATION_ENABLED, 1);
        $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_STICKY_ENABLED, 0);
        $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_ADMIN_EMAIL, (string) Configuration::get('PS_SHOP_EMAIL'));
        $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_RETENTION_DAYS, 365);
        $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_PRIVACY_URL, '');
        $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_HASH_IP, 0);
        $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_COMPATIBILITY_MODE, 'ps8_official_ps9_experimental');
        $this->updateConfigurationValue('EUWB_PERIOD_DAYS', 14);

        foreach (Language::getLanguages(false) as $language) {
            $idLang = (int) $language['id_lang'];
            $iso = strtolower((string) $language['iso_code']);
            $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_LINK_LABEL_PREFIX . $idLang, $iso === 'de' ? 'Vertrag widerrufen' : 'Withdraw from contract');
            $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_FINAL_LABEL_PREFIX . $idLang, $iso === 'de' ? 'Widerruf bestaetigen' : 'Confirm withdrawal');
            $this->updateConfigurationValue(\EuWithdrawalButton::CONFIG_SLUG_PREFIX . $idLang, $iso === 'de' ? 'vertrag-widerrufen' : 'withdraw-contract');
        }

        return true;
    }

    private function updateConfigurationValue($key, $value)
    {
        if (!Configuration::updateValue($key, $value)) {
            $this->logInstallWarning('Could not save configuration key ' . $key . '.');
        }
    }

    private function logInstallError($message)
    {
        if (class_exists('PrestaShopLogger')) {
            \PrestaShopLogger::addLog('[euwithdrawalbutton] ' . $message, 3);
        }
    }

    private function logInstallWarning($message)
    {
        if (class_exists('PrestaShopLogger')) {
            \PrestaShopLogger::addLog('[euwithdrawalbutton] ' . $message, 2);
        }
    }
}
