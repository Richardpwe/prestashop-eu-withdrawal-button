<?php

class EuWithdrawalButtonVerifyModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        if (Tools::isSubmit('euwb_request_verification')) {
            $this->handleVerificationRequest();
            return;
        }

        $token = (string) Tools::getValue('token');
        if ($token !== '') {
            $payload = $this->module->getServiceFactory()->guestVerificationService()->verifyToken($token, $this->secret());
            $this->context->smarty->assign([
                'euwb_verified' => (bool) $payload,
                'euwb_payload' => $payload ?: [],
                'euwb_withdrawal_url' => $this->module->getWithdrawalUrl(),
            ]);
            $this->setTemplate('module:euwithdrawalbutton/views/templates/front/verify-confirmed.tpl');
            return;
        }

        $this->context->smarty->assign([
            'euwb_form_action' => $this->context->link->getModuleLink($this->module->name, 'verify', [], true),
            'euwb_withdrawal_url' => $this->module->getWithdrawalUrl(),
            'euwb_token' => Tools::getToken(false),
            'euwb_requested' => false,
        ]);

        $this->setTemplate('module:euwithdrawalbutton/views/templates/front/verify-request.tpl');
    }

    private function handleVerificationRequest()
    {
        $submitted = (string) Tools::getValue('euwb_token');
        if ($submitted !== '' && hash_equals(Tools::getToken(false), $submitted)) {
            $this->module->getServiceFactory()->guestVerificationService()->sendVerificationLinkIfSafeMatch([
                'customer_name' => Tools::getValue('customer_name'),
                'customer_email' => Tools::getValue('customer_email'),
                'order_reference' => Tools::getValue('order_reference'),
            ], $this->context, $this->module, $this->secret());
        }

        $this->context->smarty->assign([
            'euwb_form_action' => $this->context->link->getModuleLink($this->module->name, 'verify', [], true),
            'euwb_withdrawal_url' => $this->module->getWithdrawalUrl(),
            'euwb_token' => Tools::getToken(false),
            'euwb_requested' => true,
        ]);

        $this->setTemplate('module:euwithdrawalbutton/views/templates/front/verify-request.tpl');
    }

    private function secret()
    {
        return defined('_COOKIE_KEY_') ? _COOKIE_KEY_ : 'euwithdrawalbutton';
    }
}
