<?php
/**
 * EU Withdrawal Button module.
 *
 * This module provides a public two-step withdrawal declaration flow without
 * changing order, payment, refund, or return-label state automatically.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$euwbAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($euwbAutoload)) {
    require_once $euwbAutoload;
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'PrestaShop\\Module\\EuWithdrawalButton\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = __DIR__ . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}

use PrestaShop\Module\EuWithdrawalButton\Install\Installer;
use PrestaShop\Module\EuWithdrawalButton\Install\Uninstaller;
use PrestaShop\Module\EuWithdrawalButton\Service\ServiceFactory;

class EuWithdrawalButton extends Module
{
    const CONFIG_ENABLED = 'EUWB_ENABLED';
    const CONFIG_LINK_LABEL_PREFIX = 'EUWB_LINK_LABEL_';
    const CONFIG_FINAL_LABEL_PREFIX = 'EUWB_FINAL_LABEL_';
    const CONFIG_SLUG_PREFIX = 'EUWB_SLUG_';
    const CONFIG_FOOTER_ENABLED = 'EUWB_FOOTER_ENABLED';
    const CONFIG_ACCOUNT_ENABLED = 'EUWB_ACCOUNT_ENABLED';
    const CONFIG_ORDER_DETAIL_ENABLED = 'EUWB_ORDER_DETAIL_ENABLED';
    const CONFIG_ORDER_CONFIRMATION_ENABLED = 'EUWB_ORDER_CONFIRMATION_ENABLED';
    const CONFIG_STICKY_ENABLED = 'EUWB_STICKY_ENABLED';
    const CONFIG_ADMIN_EMAIL = 'EUWB_ADMIN_EMAIL';
    const CONFIG_RETENTION_DAYS = 'EUWB_RETENTION_DAYS';
    const CONFIG_PRIVACY_URL = 'EUWB_PRIVACY_URL';
    const CONFIG_HASH_IP = 'EUWB_HASH_IP';
    const CONFIG_COMPATIBILITY_MODE = 'EUWB_COMPATIBILITY_MODE';

    public $tabs = [];

    public function __construct()
    {
        $this->name = 'euwithdrawalbutton';
        $this->tab = 'administration';
        $this->version = '0.1.0';
        $this->author = 'Richard Weyer and contributors';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => '9.99.99',
        ];

        parent::__construct();

        $this->displayName = $this->trans('EU Withdrawal Button', [], 'Modules.Euwithdrawalbutton.Admin');
        $this->description = $this->trans('Adds a public two-step online withdrawal declaration flow.', [], 'Modules.Euwithdrawalbutton.Admin');
        $this->confirmUninstall = $this->trans('Uninstalling removes module tables and stored withdrawal declarations. Continue?', [], 'Modules.Euwithdrawalbutton.Admin');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $installed = (new Installer($this))->install();

        if (!$installed) {
            (new Uninstaller($this))->uninstall();
            parent::uninstall();

            return false;
        }

        $this->registerModuleHooks();

        return true;
    }

    public function uninstall()
    {
        return (new Uninstaller($this))->uninstall() && parent::uninstall();
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitEuWithdrawalButton')) {
            Configuration::updateValue(self::CONFIG_ENABLED, Tools::getValue('enabled') ? 1 : 0);
            Configuration::updateValue(self::CONFIG_FOOTER_ENABLED, Tools::getValue('footer_enabled') ? 1 : 0);
            Configuration::updateValue(self::CONFIG_ACCOUNT_ENABLED, Tools::getValue('account_enabled') ? 1 : 0);
            Configuration::updateValue(self::CONFIG_ORDER_DETAIL_ENABLED, Tools::getValue('order_detail_enabled') ? 1 : 0);
            Configuration::updateValue(self::CONFIG_ORDER_CONFIRMATION_ENABLED, Tools::getValue('order_confirmation_enabled') ? 1 : 0);
            Configuration::updateValue(self::CONFIG_STICKY_ENABLED, Tools::getValue('sticky_enabled') ? 1 : 0);
            Configuration::updateValue(self::CONFIG_ADMIN_EMAIL, trim((string) Tools::getValue('admin_email')));
            Configuration::updateValue(self::CONFIG_PRIVACY_URL, trim((string) Tools::getValue('privacy_url')));
            Configuration::updateValue(self::CONFIG_RETENTION_DAYS, max(0, (int) Tools::getValue('retention_days')));

            $output .= $this->displayConfirmation($this->trans('Settings updated.', [], 'Admin.Notifications.Success'));
        }

        $withdrawalUrl = $this->getWithdrawalUrl();

        return $output . '<div class="panel"><h3>' . $this->displayName . '</h3>'
            . '<p class="alert alert-warning">' . $this->trans('This module is a technical implementation aid and not legal advice.', [], 'Modules.Euwithdrawalbutton.Admin') . '</p>'
            . '<p>' . $this->trans('Public withdrawal URL:', [], 'Modules.Euwithdrawalbutton.Admin') . ' <a href="' . htmlspecialchars($withdrawalUrl, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener">' . htmlspecialchars($withdrawalUrl, ENT_QUOTES, 'UTF-8') . '</a></p>'
            . '<form method="post">'
            . $this->renderSwitch('enabled', $this->trans('Enable module', [], 'Modules.Euwithdrawalbutton.Admin'), (bool) Configuration::get(self::CONFIG_ENABLED))
            . $this->renderSwitch('footer_enabled', $this->trans('Show footer link', [], 'Modules.Euwithdrawalbutton.Admin'), (bool) Configuration::get(self::CONFIG_FOOTER_ENABLED))
            . $this->renderSwitch('account_enabled', $this->trans('Show customer account link', [], 'Modules.Euwithdrawalbutton.Admin'), (bool) Configuration::get(self::CONFIG_ACCOUNT_ENABLED))
            . $this->renderSwitch('order_detail_enabled', $this->trans('Show order detail link', [], 'Modules.Euwithdrawalbutton.Admin'), (bool) Configuration::get(self::CONFIG_ORDER_DETAIL_ENABLED))
            . $this->renderSwitch('order_confirmation_enabled', $this->trans('Show order confirmation link', [], 'Modules.Euwithdrawalbutton.Admin'), (bool) Configuration::get(self::CONFIG_ORDER_CONFIRMATION_ENABLED))
            . $this->renderSwitch('sticky_enabled', $this->trans('Show sticky button', [], 'Modules.Euwithdrawalbutton.Admin'), (bool) Configuration::get(self::CONFIG_STICKY_ENABLED))
            . $this->renderTextInput('admin_email', $this->trans('Admin notification email', [], 'Modules.Euwithdrawalbutton.Admin'), (string) Configuration::get(self::CONFIG_ADMIN_EMAIL))
            . $this->renderTextInput('privacy_url', $this->trans('Privacy policy URL', [], 'Modules.Euwithdrawalbutton.Admin'), (string) Configuration::get(self::CONFIG_PRIVACY_URL))
            . $this->renderTextInput('retention_days', $this->trans('Retention period in days', [], 'Modules.Euwithdrawalbutton.Admin'), (string) Configuration::get(self::CONFIG_RETENTION_DAYS))
            . '<button type="submit" name="submitEuWithdrawalButton" class="btn btn-primary">' . $this->trans('Save', [], 'Admin.Actions') . '</button>'
            . '</form>'
            . '</div>';
    }

    public function hookDisplayHeader()
    {
        if (!$this->isEnabledForCurrentShop()) {
            return '';
        }

        $this->context->controller->registerStylesheet(
            'module-euwithdrawalbutton-front',
            'modules/' . $this->name . '/views/css/front.css',
            ['media' => 'all', 'priority' => 150]
        );

        $this->context->controller->registerJavascript(
            'module-euwithdrawalbutton-front',
            'modules/' . $this->name . '/views/js/front.js',
            ['position' => 'bottom', 'priority' => 150]
        );

        return '';
    }

    public function hookDisplayFooter()
    {
        if (!Configuration::get(self::CONFIG_FOOTER_ENABLED)) {
            return '';
        }

        return $this->renderWithdrawalLink('footer');
    }

    public function hookDisplayCustomerAccount()
    {
        if (!Configuration::get(self::CONFIG_ACCOUNT_ENABLED)) {
            return '';
        }

        return $this->renderWithdrawalLink('account');
    }

    public function hookDisplayOrderDetail()
    {
        if (!Configuration::get(self::CONFIG_ORDER_DETAIL_ENABLED)) {
            return '';
        }

        return $this->renderWithdrawalLink('order_detail');
    }

    public function hookDisplayOrderConfirmation()
    {
        if (!Configuration::get(self::CONFIG_ORDER_CONFIRMATION_ENABLED)) {
            return '';
        }

        return $this->renderWithdrawalLink('order_confirmation');
    }

    public function hookModuleRoutes()
    {
        $routes = [];
        foreach (Language::getLanguages(false) as $language) {
            $idLang = (int) $language['id_lang'];
            $slug = Configuration::get(self::CONFIG_SLUG_PREFIX . $idLang) ?: 'vertrag-widerrufen';
            $routes['module-' . $this->name . '-withdraw-' . $idLang] = [
                'controller' => 'withdraw',
                'rule' => $slug,
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                    'controller' => 'withdraw',
                ],
            ];
        }

        return $routes;
    }

    public function getWithdrawalUrl($idLang = null)
    {
        $idLang = $idLang ? (int) $idLang : (int) $this->context->language->id;
        $slug = Configuration::get(self::CONFIG_SLUG_PREFIX . $idLang);

        return $this->context->link->getModuleLink($this->name, 'withdraw', [
            'rewrite' => $slug ?: 'vertrag-widerrufen',
        ], true, $idLang);
    }

    public function getLinkLabel($idLang = null)
    {
        $idLang = $idLang ? (int) $idLang : (int) $this->context->language->id;
        $label = Configuration::get(self::CONFIG_LINK_LABEL_PREFIX . $idLang);

        return $label ?: $this->trans('Vertrag widerrufen', [], 'Modules.Euwithdrawalbutton.Shop');
    }

    public function getFinalButtonLabel($idLang = null)
    {
        $idLang = $idLang ? (int) $idLang : (int) $this->context->language->id;
        $label = Configuration::get(self::CONFIG_FINAL_LABEL_PREFIX . $idLang);

        return $label ?: $this->trans('Widerruf bestätigen', [], 'Modules.Euwithdrawalbutton.Shop');
    }

    public function isEnabledForCurrentShop()
    {
        return (bool) Configuration::get(self::CONFIG_ENABLED);
    }

    public function getServiceFactory()
    {
        return new ServiceFactory($this);
    }

    private function renderWithdrawalLink($position)
    {
        if (!$this->isEnabledForCurrentShop()) {
            return '';
        }

        $this->context->smarty->assign([
            'euwb_position' => $position,
            'euwb_url' => $this->getWithdrawalUrl(),
            'euwb_label' => $this->getLinkLabel(),
            'euwb_sticky' => (bool) Configuration::get(self::CONFIG_STICKY_ENABLED),
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/front/link.tpl');
    }

    private function renderSwitch($name, $label, $enabled)
    {
        return '<div class="form-group">'
            . '<label class="control-label col-lg-3">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label>'
            . '<div class="col-lg-9">'
            . '<span class="switch prestashop-switch fixed-width-lg">'
            . '<input type="radio" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" id="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '_on" value="1"' . ($enabled ? ' checked="checked"' : '') . '>'
            . '<label for="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '_on">' . $this->trans('Yes', [], 'Admin.Global') . '</label>'
            . '<input type="radio" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" id="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '_off" value="0"' . (!$enabled ? ' checked="checked"' : '') . '>'
            . '<label for="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '_off">' . $this->trans('No', [], 'Admin.Global') . '</label>'
            . '<a class="slide-button btn"></a>'
            . '</span>'
            . '</div>'
            . '<div class="clearfix"></div>'
            . '</div>';
    }

    private function renderTextInput($name, $label, $value)
    {
        return '<div class="form-group">'
            . '<label class="control-label col-lg-3" for="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label>'
            . '<div class="col-lg-9">'
            . '<input class="form-control" type="text" id="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">'
            . '</div>'
            . '<div class="clearfix"></div>'
            . '</div>';
    }

    private function registerModuleHooks()
    {
        $hooks = [
            'displayHeader',
            'displayFooter',
            'displayCustomerAccount',
            'displayOrderDetail',
            'displayOrderConfirmation',
            'moduleRoutes',
        ];

        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                $this->logInstallWarning('Could not register hook ' . $hook . '.');
            }
        }
    }

    private function logInstallWarning($message)
    {
        if (class_exists('PrestaShopLogger')) {
            \PrestaShopLogger::addLog('[euwithdrawalbutton] ' . $message, 2);
        }
    }
}
