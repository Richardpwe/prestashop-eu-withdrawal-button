<?php

namespace PrestaShop\Module\EuWithdrawalButton\Controller\Admin;

use PrestaShop\Module\EuWithdrawalButton\Domain\MailStatus;
use PrestaShop\Module\EuWithdrawalButton\Domain\WithdrawalStatus;
use PrestaShop\Module\EuWithdrawalButton\Service\ServiceFactory;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WithdrawalController extends FrameworkBundleAdminController
{
    public function indexAction(Request $request)
    {
        $factory = $this->factory();
        $filters = [
            'status' => $request->query->get('status'),
            'id_shop' => $request->query->get('id_shop'),
            'customer_email' => $request->query->get('customer_email'),
            'order_reference' => $request->query->get('order_reference'),
            'mail_status' => $request->query->get('mail_status'),
        ];

        return $this->render('@Modules/euwithdrawalbutton/views/templates/admin/withdrawals/index.html.twig', [
            'withdrawals' => $factory->withdrawalRepository()->search($filters, 100, 0),
            'filters' => $filters,
            'statuses' => WithdrawalStatus::all(),
            'mail_statuses' => MailStatus::all(),
            'export_url' => $this->generateUrl('euwithdrawalbutton_export', array_filter($filters)),
        ]);
    }

    public function detailAction(Request $request, $idWithdrawal)
    {
        $factory = $this->factory();
        $withdrawal = $factory->withdrawalRepository()->findById((int) $idWithdrawal);
        if (!$withdrawal) {
            throw $this->createNotFoundException('Withdrawal not found.');
        }

        if ($request->isMethod('POST')) {
            $this->handleDetailPost($request, $factory, $withdrawal);
            $withdrawal = $factory->withdrawalRepository()->findById((int) $idWithdrawal);
        }

        $items = $factory->withdrawalItemRepository()->findByWithdrawal((int) $idWithdrawal);
        $logs = $factory->withdrawalLogRepository()->findByWithdrawal((int) $idWithdrawal);
        $suggestions = $factory->orderMatcher()->suggestOrders($withdrawal);

        return $this->render('@Modules/euwithdrawalbutton/views/templates/admin/withdrawals/detail.html.twig', [
            'withdrawal' => $withdrawal,
            'items' => $items,
            'logs' => $logs,
            'suggestions' => $suggestions,
            'statuses' => WithdrawalStatus::all(),
            'list_url' => $this->generateUrl('euwithdrawalbutton_withdrawals'),
        ]);
    }

    public function exportAction(Request $request)
    {
        $factory = $this->factory();
        $filters = [
            'status' => $request->query->get('status'),
            'id_shop' => $request->query->get('id_shop'),
            'customer_email' => $request->query->get('customer_email'),
            'order_reference' => $request->query->get('order_reference'),
            'mail_status' => $request->query->get('mail_status'),
        ];
        $csv = $factory->csvExporter()->exportWithdrawals($factory->withdrawalRepository()->search($filters, 5000, 0));

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="withdrawals.csv"',
        ]);
    }

    private function handleDetailPost(Request $request, ServiceFactory $factory, array $withdrawal)
    {
        $employeeId = \Context::getContext()->employee ? (int) \Context::getContext()->employee->id : null;

        if ($request->request->has('new_status')) {
            $newStatus = (string) $request->request->get('new_status');
            if (WithdrawalStatus::isValid($newStatus) && $newStatus !== $withdrawal['status']) {
                $factory->withdrawalRepository()->updateStatus((int) $withdrawal['id_withdrawal'], $newStatus);
                $factory->auditLogger()->log((int) $withdrawal['id_withdrawal'], 'status_changed', $withdrawal['status'], $newStatus, (string) $request->request->get('note'), $employeeId);
                $this->addFlash('success', $this->trans('Status updated.', 'Admin.Notifications.Success'));
            }
        }

        if ($request->request->has('assign_order')) {
            $idOrder = (int) $request->request->get('id_order');
            if ($idOrder > 0) {
                $factory->withdrawalRepository()->assignOrder((int) $withdrawal['id_withdrawal'], $idOrder);
                $factory->auditLogger()->log((int) $withdrawal['id_withdrawal'], 'order_assigned', null, null, 'Assigned order #' . $idOrder, $employeeId);
                $this->addFlash('success', $this->trans('Order assigned.', 'Admin.Notifications.Success'));
            }
        }

        if ($request->request->has('resend_acknowledgement')) {
            $module = \Module::getInstanceByName('euwithdrawalbutton');
            $context = \Context::getContext();
            $items = $factory->withdrawalItemRepository()->findByWithdrawal((int) $withdrawal['id_withdrawal']);
            $sent = $factory->mailSender()->send(
                $module,
                $context,
                $factory->mailPayloadBuilder()->buildCustomerAcknowledgement($withdrawal, $items, $context)
            );
            $factory->withdrawalRepository()->updateMailStatus(
                (int) $withdrawal['id_withdrawal'],
                $sent ? MailStatus::SENT : MailStatus::FAILED,
                $sent ? gmdate('Y-m-d H:i:s') : null,
                null
            );
            $factory->auditLogger()->log((int) $withdrawal['id_withdrawal'], 'acknowledgement_resent', null, null, $sent ? 'Resent successfully.' : 'Resend failed.', $employeeId);
            $this->addFlash($sent ? 'success' : 'error', $sent ? $this->trans('Acknowledgement resent.', 'Admin.Notifications.Success') : $this->trans('Acknowledgement could not be sent.', 'Admin.Notifications.Error'));
        }
    }

    private function factory()
    {
        return new ServiceFactory(\Module::getInstanceByName('euwithdrawalbutton'));
    }
}

