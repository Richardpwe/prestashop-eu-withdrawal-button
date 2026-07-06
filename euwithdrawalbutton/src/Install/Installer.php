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

        $this->installDefaultConfiguration();

        return true;
    }

    private function executeSqlFile($file)
    {
        $path = dirname(__DIR__, 2) . '/sql/' . $file;
        $statements = include $path;

        foreach ($statements as $statement) {
            if (!Db::getInstance()->execute($statement)) {
                return false;
            }
        }

        return true;
    }

    private function installDefaultConfiguration()
    {
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_ENABLED, 1);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_FOOTER_ENABLED, 1);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_ACCOUNT_ENABLED, 1);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_ORDER_DETAIL_ENABLED, 1);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_ORDER_CONFIRMATION_ENABLED, 1);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_STICKY_ENABLED, 0);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_ADMIN_EMAIL, (string) Configuration::get('PS_SHOP_EMAIL'));
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_RETENTION_DAYS, 365);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_PRIVACY_URL, '');
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_HASH_IP, 0);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_COMPATIBILITY_MODE, 'ps8_official_ps9_experimental');
        Configuration::updateValue('EUWB_PERIOD_DAYS', 14);

        foreach (Language::getLanguages(false) as $language) {
            $idLang = (int) $language['id_lang'];
            $iso = strtolower((string) $language['iso_code']);
            Configuration::updateValue(\EuWithdrawalButton::CONFIG_LINK_LABEL_PREFIX . $idLang, $iso === 'de' ? 'Vertrag widerrufen' : 'Withdraw from contract');
            Configuration::updateValue(\EuWithdrawalButton::CONFIG_FINAL_LABEL_PREFIX . $idLang, $iso === 'de' ? 'Widerruf bestätigen' : 'Confirm withdrawal');
            Configuration::updateValue(\EuWithdrawalButton::CONFIG_SLUG_PREFIX . $idLang, $iso === 'de' ? 'vertrag-widerrufen' : 'withdraw-contract');
        }
    }
}

