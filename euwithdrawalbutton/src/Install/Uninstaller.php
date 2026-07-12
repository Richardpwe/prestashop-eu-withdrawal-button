<?php

namespace PrestaShop\Module\EuWithdrawalButton\Install;

use Configuration;
use Db;
use Language;

final class Uninstaller
{
    private $module;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    public function uninstall()
    {
        $this->deleteLegacyTabs();
        $this->deleteConfiguration();

        return $this->executeSqlFile('uninstall.php');
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

    private function deleteConfiguration()
    {
        $keys = [
            \EuWithdrawalButton::CONFIG_ENABLED,
            \EuWithdrawalButton::CONFIG_FOOTER_ENABLED,
            \EuWithdrawalButton::CONFIG_ACCOUNT_ENABLED,
            \EuWithdrawalButton::CONFIG_ORDER_DETAIL_ENABLED,
            \EuWithdrawalButton::CONFIG_ORDER_CONFIRMATION_ENABLED,
            \EuWithdrawalButton::CONFIG_STICKY_ENABLED,
            \EuWithdrawalButton::CONFIG_ADMIN_EMAIL,
            \EuWithdrawalButton::CONFIG_RETENTION_DAYS,
            \EuWithdrawalButton::CONFIG_PRIVACY_URL,
            \EuWithdrawalButton::CONFIG_HASH_IP,
            \EuWithdrawalButton::CONFIG_COMPATIBILITY_MODE,
            'EUWB_PERIOD_DAYS',
        ];

        foreach (Language::getLanguages(false) as $language) {
            $idLang = (int) $language['id_lang'];
            $keys[] = \EuWithdrawalButton::CONFIG_LINK_LABEL_PREFIX . $idLang;
            $keys[] = \EuWithdrawalButton::CONFIG_FINAL_LABEL_PREFIX . $idLang;
            $keys[] = \EuWithdrawalButton::CONFIG_SLUG_PREFIX . $idLang;
        }

        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }
    }

    private function deleteLegacyTabs()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab`
            WHERE `module` = \'' . pSQL($this->module->name) . '\'
            OR `class_name` LIKE \'AdminEuWithdrawalButton%\''
        );

        foreach ($rows ?: [] as $row) {
            $idTab = (int) $row['id_tab'];
            if ($idTab <= 0 || !class_exists('Tab')) {
                continue;
            }

            $tab = new \Tab($idTab);
            if (\Validate::isLoadedObject($tab)) {
                $tab->delete();
            }
        }
    }
}
