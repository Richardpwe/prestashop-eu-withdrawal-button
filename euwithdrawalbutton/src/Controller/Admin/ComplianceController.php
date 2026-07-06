<?php

namespace PrestaShop\Module\EuWithdrawalButton\Controller\Admin;

use PrestaShop\Module\EuWithdrawalButton\Service\ServiceFactory;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;

final class ComplianceController extends FrameworkBundleAdminController
{
    public function indexAction(Request $request)
    {
        $module = \Module::getInstanceByName('euwithdrawalbutton');
        $factory = new ServiceFactory($module);

        if ($request->isMethod('POST') && $request->request->has('run_anonymization')) {
            $factory->retentionAnonymizer()->anonymizeDueWithdrawals();
            $this->addFlash('success', $this->trans('Retention anonymization executed.', 'Admin.Notifications.Success'));
        }

        return $this->render('@Modules/euwithdrawalbutton/views/templates/admin/compliance.html.twig', [
            'checks' => $factory->complianceChecker()->check(\Context::getContext()),
            'configuration_url' => $this->generateUrl('euwithdrawalbutton_configuration'),
        ]);
    }
}

