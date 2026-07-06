<?php

namespace PrestaShop\Module\EuWithdrawalButton\Controller\Admin;

use Configuration;
use Language;
use PrestaShop\Module\EuWithdrawalButton\Service\ServiceFactory;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;

final class ConfigurationController extends FrameworkBundleAdminController
{
    public function indexAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $this->saveConfiguration($request);
            $this->addFlash('success', $this->trans('Settings updated.', 'Admin.Notifications.Success'));
        }

        $module = \Module::getInstanceByName('euwithdrawalbutton');
        $factory = new ServiceFactory($module);

        return $this->render('@Modules/euwithdrawalbutton/views/templates/admin/configuration.html.twig', [
            'settings' => $this->readConfiguration(),
            'languages' => Language::getLanguages(false),
            'compliance_checks' => $factory->complianceChecker()->check(\Context::getContext()),
            'withdrawals_url' => $this->generateUrl('euwithdrawalbutton_withdrawals'),
            'compliance_url' => $this->generateUrl('euwithdrawalbutton_compliance'),
        ]);
    }

    private function saveConfiguration(Request $request)
    {
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_ENABLED, $request->request->get('enabled') ? 1 : 0);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_FOOTER_ENABLED, $request->request->get('footer_enabled') ? 1 : 0);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_ACCOUNT_ENABLED, $request->request->get('account_enabled') ? 1 : 0);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_ORDER_DETAIL_ENABLED, $request->request->get('order_detail_enabled') ? 1 : 0);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_ORDER_CONFIRMATION_ENABLED, $request->request->get('order_confirmation_enabled') ? 1 : 0);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_STICKY_ENABLED, $request->request->get('sticky_enabled') ? 1 : 0);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_HASH_IP, $request->request->get('hash_ip') ? 1 : 0);
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_ADMIN_EMAIL, trim((string) $request->request->get('admin_email')));
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_PRIVACY_URL, trim((string) $request->request->get('privacy_url')));
        Configuration::updateValue(\EuWithdrawalButton::CONFIG_RETENTION_DAYS, max(0, (int) $request->request->get('retention_days')));
        Configuration::updateValue('EUWB_PERIOD_DAYS', max(0, (int) $request->request->get('period_days')));

        foreach (Language::getLanguages(false) as $language) {
            $idLang = (int) $language['id_lang'];
            Configuration::updateValue(\EuWithdrawalButton::CONFIG_LINK_LABEL_PREFIX . $idLang, trim((string) $request->request->get('link_label_' . $idLang)));
            Configuration::updateValue(\EuWithdrawalButton::CONFIG_FINAL_LABEL_PREFIX . $idLang, trim((string) $request->request->get('final_label_' . $idLang)));
            Configuration::updateValue(\EuWithdrawalButton::CONFIG_SLUG_PREFIX . $idLang, trim((string) $request->request->get('slug_' . $idLang)));
        }

        if (class_exists('\Tools') && method_exists('\Tools', 'clearSf2Cache')) {
            \Tools::clearSf2Cache();
        }
    }

    private function readConfiguration()
    {
        $labels = [];
        foreach (Language::getLanguages(false) as $language) {
            $idLang = (int) $language['id_lang'];
            $labels[$idLang] = [
                'link_label' => Configuration::get(\EuWithdrawalButton::CONFIG_LINK_LABEL_PREFIX . $idLang),
                'final_label' => Configuration::get(\EuWithdrawalButton::CONFIG_FINAL_LABEL_PREFIX . $idLang),
                'slug' => Configuration::get(\EuWithdrawalButton::CONFIG_SLUG_PREFIX . $idLang),
            ];
        }

        return [
            'enabled' => (bool) Configuration::get(\EuWithdrawalButton::CONFIG_ENABLED),
            'footer_enabled' => (bool) Configuration::get(\EuWithdrawalButton::CONFIG_FOOTER_ENABLED),
            'account_enabled' => (bool) Configuration::get(\EuWithdrawalButton::CONFIG_ACCOUNT_ENABLED),
            'order_detail_enabled' => (bool) Configuration::get(\EuWithdrawalButton::CONFIG_ORDER_DETAIL_ENABLED),
            'order_confirmation_enabled' => (bool) Configuration::get(\EuWithdrawalButton::CONFIG_ORDER_CONFIRMATION_ENABLED),
            'sticky_enabled' => (bool) Configuration::get(\EuWithdrawalButton::CONFIG_STICKY_ENABLED),
            'hash_ip' => (bool) Configuration::get(\EuWithdrawalButton::CONFIG_HASH_IP),
            'admin_email' => Configuration::get(\EuWithdrawalButton::CONFIG_ADMIN_EMAIL),
            'privacy_url' => Configuration::get(\EuWithdrawalButton::CONFIG_PRIVACY_URL),
            'retention_days' => (int) Configuration::get(\EuWithdrawalButton::CONFIG_RETENTION_DAYS),
            'period_days' => (int) Configuration::get('EUWB_PERIOD_DAYS'),
            'labels' => $labels,
        ];
    }
}
