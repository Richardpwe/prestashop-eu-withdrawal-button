<?php

class AdminEuWithdrawalButtonConfigurationController extends ModuleAdminController
{
    public function initContent()
    {
        parent::initContent();
        $this->content .= $this->module->getContent();
        $this->context->smarty->assign('content', $this->content);
    }
}

