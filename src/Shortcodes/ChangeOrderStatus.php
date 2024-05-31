<?php

namespace Adue\LivrikaPickingPoints\Shortcodes;

use Adue\WordPressBasePlugin\Modules\Shortcodes\BaseShortcode;
use Adue\WordPressBasePlugin\Traits\ViewTrait;

class ChangeOrderStatus extends BaseShortcode
{

    use ViewTrait;

    protected $signature = 'picking-points-change-order-status';

    public function run($args)
    {

        $orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
        $order = wc_get_order($orderId);

        if(!is_user_logged_in()) {
            $this->view()->render('public/picking-points/change-order-status');
            return;
        }

        if(!$this->checkPickingPoint($order)) {
            $this->view()->set('error', 'La orden leída no corresponde al punto de retiro actual');
            $this->view()->render('public/picking-points/change-order-status');
            return;
        }

        if($_GET['picking_point_change_order_status']) {
            $order->set_status($_GET['new_status']);
            $order->save();

            $order = wc_get_order($orderId);

            $this->sendNotifications($order);

            wp_redirect('/estado-de-orden?order_id=' . $order->get_id().'&success=true');

        }

        if($_GET['success']) {
            $this->view()->set('successMessage', 'La orden ha sido cambiada de estado correctamente');
        }

        $this->view()->set('checkPickingPoint', $this->checkPickingPoint($order));
        $this->view()->set('order', $order);
        $this->view()->render('public/picking-points/change-order-status');
    }

    private function checkPickingPoint($order)
    {
        return $order->get_meta('picking_point_id') == $this->getPickingPointFromSession();
    }

    private function getPickingPointFromSession()
    {
        return get_user_meta(get_current_user_id(), 'picking_point_id', true);
    }

    private function sendNotifications($order)
    {
        if($order->get_status() == 'wc-delivered' || $order->get_status() == 'delivered') {
            $mailer = WC()->mailer();
            $emails = $mailer->get_emails();
            $emails['WC_Delivered']->trigger($order->get_id());
            $emails['WC_Delivered_To_Admin']->trigger($order->get_id());
        }

        if($order->get_status() == 'wc-withdrawn' || $order->get_status() == 'withdrawn') {
            $mailer = WC()->mailer();
            $emails = $mailer->get_emails();
            $emails['WC_Withdrawn']->trigger($order->get_id());
            $emails['WC_Withdrawn_To_Vendor']->trigger($order->get_id());
        }

    }

}