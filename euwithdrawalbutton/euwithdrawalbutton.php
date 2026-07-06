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

    public $tabs = [
        [
            'name' => 'EU Withdrawals',
            'class_name' => 'AdminEuWithdrawalButtonWithdrawals',
            'route_name' => 'euwithdrawalbutton_withdrawals',
            'parent_class_name' => 'AdminParentOrders',
            'wording' => 'EU Withdrawals',
            'wording_domain' => 'Modules.Euwithdrawalbutton.Admin',
        ],
        [
            'name' => 'EU Withdrawal Settings',
            'class_name' => 'AdminEuWithdrawalButtonConfiguration',
            'route_name' => 'euwithdrawalbutton_configuration',
            'parent_class_name' => 'AdminParentCustomer',
            'wording' => 'EU Withdrawal Settings',
            'wording_domain' => 'Modules.Euwithdrawalbutton.Admin',
            'visible' => false,
        ],
        [
            'name' => 'EU Withdrawal Compliance',
            'class_name' => 'AdminEuWithdrawalButtonCompliance',
            'route_name' => 'euwithdrawalbutton_compliance',
            'parent_class_name' => 'AdminParentCustomer',
            'wording' => 'EU Withdrawal Compliance',
            'wording_domain' => 'Modules.Euwithdrawalbutton.Admin',
            'visible' => false,
        ],
    ];

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
        return parent::install()
            && (new Installer($this))->install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('displayOrderDetail')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('moduleRoutes');
    }

    public function uninstall()
    {
        return (new Uninstaller($this))->uninstall() && parent::uninstall();
    }

    public function getContent()
    {
        $configurationUrl = $this->context->link->getAdminLink('AdminEuWithdrawalButtonConfiguration');
        if ($configurationUrl) {
            Tools::redirectAdmin($configurationUrl);
        }

        return '<div class="panel"><h3>' . $this->displayName . '</h3>'
            . '<p>' . $this->trans('This module is installed. Open the EU Withdrawal settings tab to configure it.', [], 'Modules.Euwithdrawalbutton.Admin') . '</p>'
            . '<p class="alert alert-warning">' . $this->trans('This module is a technical implementation aid and not legal advice.', [], 'Modules.Euwithdrawalbutton.Admin') . '</p>'
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
}
