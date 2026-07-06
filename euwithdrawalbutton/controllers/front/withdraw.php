<?php

use PrestaShop\Module\EuWithdrawalButton\Service\ValidationException;

class EuWithdrawalButtonWithdrawModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        if (!$this->module->isEnabledForCurrentShop()) {
            $this->redirect_after = $this->context->link->getPageLink('index');
            $this->redirect();
        }

        if (Tools::isSubmit('euwb_confirm')) {
            $this->handleConfirm();
            return;
        }

        if (Tools::isSubmit('euwb_edit')) {
            $this->renderStep1([], $this->collectInput());
            return;
        }

        if (Tools::isSubmit('euwb_review')) {
            $this->handleReview();
            return;
        }

        $this->renderStep1([], $this->defaultFormData());
    }

    private function handleReview()
    {
        $input = $this->collectInput();
        $errors = $this->validateToken();
        $errors = array_merge($errors, $this->module->getServiceFactory()->validationService()->validatePublicInput($input));

        if ($errors) {
            $this->renderStep1($errors, $input);
            return;
        }

        $normalized = $this->module->getServiceFactory()->validationService()->normalizePublicInput($input);
        $normalized['idempotency_key'] = $this->module->getServiceFactory()->idempotencyService()->generateKey();

        $this->context->smarty->assign([
            'euwb_form_action' => $this->context->link->getModuleLink($this->module->name, 'withdraw', [], true),
            'euwb_token' => $this->token(),
            'euwb_final_label' => $this->module->getFinalButtonLabel(),
            'euwb_data' => $normalized,
            'euwb_hidden_fields' => $this->hiddenFields($normalized),
        ]);

        $this->setTemplate('module:euwithdrawalbutton/views/templates/front/withdraw-review.tpl');
    }

    private function handleConfirm()
    {
        $input = $this->collectInput();
        $errors = $this->validateToken();

        if ($errors) {
            $this->renderStep1($errors, $input);
            return;
        }

        try {
            $result = $this->module->getServiceFactory()->submissionService()->submit($input, $this->context, $this->module);
            $this->context->smarty->assign([
                'euwb_reference' => $result['public_reference'],
                'euwb_mail_status' => $result['mail_status'],
                'euwb_duplicate' => (bool) $result['duplicate'],
            ]);
            $this->setTemplate('module:euwithdrawalbutton/views/templates/front/withdraw-success.tpl');
        } catch (ValidationException $exception) {
            $this->renderStep1($exception->getErrors(), $input);
        } catch (Exception $exception) {
            PrestaShopLogger::addLog('EU withdrawal submission failed: ' . $exception->getMessage(), 3, null, 'EuWithdrawalButton');
            $this->renderStep1([
                'generic' => $this->module->l('The declaration could not be processed right now. Please try again later.'),
            ], $input);
        }
    }

    private function renderStep1(array $errors, array $data)
    {
        $this->context->smarty->assign([
            'euwb_form_action' => $this->context->link->getModuleLink($this->module->name, 'withdraw', [], true),
            'euwb_token' => $this->token(),
            'euwb_errors' => $errors,
            'euwb_data' => $data,
            'euwb_privacy_url' => (string) Configuration::get(EuWithdrawalButton::CONFIG_PRIVACY_URL),
            'euwb_logged_in' => $this->context->customer && $this->context->customer->isLogged(),
            'euwb_account_orders' => $this->getLoggedCustomerOrders(),
        ]);

        $this->setTemplate('module:euwithdrawalbutton/views/templates/front/withdraw-step1.tpl');
    }

    private function collectInput()
    {
        return [
            'customer_name' => Tools::getValue('customer_name'),
            'customer_email' => Tools::getValue('customer_email'),
            'confirmation_email' => Tools::getValue('confirmation_email'),
            'order_reference' => Tools::getValue('order_reference'),
            'invoice_number' => Tools::getValue('invoice_number'),
            'contract_identification_text' => Tools::getValue('contract_identification_text'),
            'withdrawal_scope' => Tools::getValue('withdrawal_scope'),
            'affected_contract_part' => Tools::getValue('affected_contract_part'),
            'customer_message' => Tools::getValue('customer_message'),
            'items_json' => Tools::getValue('items_json'),
            'idempotency_key' => Tools::getValue('idempotency_key'),
            'euwb_website' => Tools::getValue('euwb_website'),
        ];
    }

    private function defaultFormData()
    {
        $name = '';
        $email = '';
        if ($this->context->customer && $this->context->customer->isLogged()) {
            $name = trim($this->context->customer->firstname . ' ' . $this->context->customer->lastname);
            $email = (string) $this->context->customer->email;
        }

        return [
            'customer_name' => $name,
            'customer_email' => $email,
            'confirmation_email' => $email,
            'order_reference' => '',
            'invoice_number' => '',
            'contract_identification_text' => '',
            'withdrawal_scope' => 'unknown',
            'affected_contract_part' => '',
            'customer_message' => '',
            'items_json' => '',
            'idempotency_key' => '',
            'euwb_website' => '',
        ];
    }

    private function hiddenFields(array $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            if ($key === 'items' && is_array($value)) {
                $fields['items_json'] = json_encode($value, JSON_UNESCAPED_UNICODE);
                continue;
            }
            if (is_scalar($value) || $value === null) {
                $fields[$key] = (string) $value;
            }
        }

        return $fields;
    }

    private function validateToken()
    {
        $submitted = (string) Tools::getValue('euwb_token');
        if ($submitted === '' || !hash_equals($this->token(), $submitted)) {
            return ['generic' => $this->module->l('The form token is invalid. Please try again.')];
        }

        return [];
    }

    private function token()
    {
        return Tools::getToken(false);
    }

    private function getLoggedCustomerOrders()
    {
        if (!$this->context->customer || !$this->context->customer->isLogged()) {
            return [];
        }

        $sql = 'SELECT `id_order`, `reference`, `date_add`, `total_paid_tax_incl`
            FROM `' . _DB_PREFIX_ . 'orders`
            WHERE `id_customer` = ' . (int) $this->context->customer->id . '
            AND `id_shop` = ' . (int) $this->context->shop->id . '
            ORDER BY `date_add` DESC
            LIMIT 20';

        return Db::getInstance()->executeS($sql) ?: [];
    }
}
