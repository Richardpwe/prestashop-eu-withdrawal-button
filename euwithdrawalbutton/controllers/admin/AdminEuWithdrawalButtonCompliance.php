<?php

class AdminEuWithdrawalButtonComplianceController extends ModuleAdminController
{
    public function initContent()
    {
        parent::initContent();
        $this->content .= '<div class="panel"><h3>EU Withdrawal Compliance</h3><p>Symfony admin route unavailable. Please use a PrestaShop 8.0+ compatible Back Office route setup.</p></div>';
        $this->context->smarty->assign('content', $this->content);
    }
}

