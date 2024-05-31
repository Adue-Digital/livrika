<?php

namespace Adue\LivrikaPickingPoints;

use Adue\LivrikaPickingPoints\DashboardPages\PickingPointsDashboardPage;
use Adue\LivrikaPickingPoints\DashboardPages\PrintTrackingCodesDashboardPage;
use Adue\LivrikaPickingPoints\Notifications\WcDelivered\WcDeliveredNotificationToClient;
use Adue\LivrikaPickingPoints\Notifications\WcDelivered\WcDeliveredNotificationToVendor;
use Adue\LivrikaPickingPoints\Notifications\WcWithdrawn\WcWithdrawnNotificationToClient;
use Adue\LivrikaPickingPoints\Notifications\WcWithdrawn\WcWithdrawnNotificationToVendor;
use Adue\LivrikaPickingPoints\Overrides\VendorShipping;
use Adue\LivrikaPickingPoints\PostTypes\PickingPointPostType;
use Adue\LivrikaPickingPoints\PostTypes\VendorPickingPointPostType;
use Adue\LivrikaPickingPoints\Roles\PickingPointRole;
use Adue\LivrikaPickingPoints\ShippingMethods\PickingPointsShippingMethod;
use Adue\LivrikaPickingPoints\Shortcodes\ChangeOrderStatus;
use Adue\WordPressBasePlugin\Base\Loader;
use Adue\WordPressBasePlugin\BasePlugin;
use Adue\WordPressBasePlugin\Modules\Views\Assets;
use Spipu\Html2Pdf\Html2Pdf;
use WeDevs\DokanPro\Shipping\ShippingZone;

class Plugin extends BasePlugin
{

    public function init()
    {
        $postType = $this->getContainer()->get(PickingPointPostType::class);
        $postType->runHooks();

        $vendorPickingPointspostType = $this->getContainer()->get(VendorPickingPointPostType::class);
        $vendorPickingPointspostType->register();

        $dashboardPage = $this->getContainer()->get(PickingPointsDashboardPage::class);
        $dashboardPage->register();

        $printPage = $this->getContainer()->get(PrintTrackingCodesDashboardPage::class);
        $printPage->register();

        $assets = new Assets();
        $assets->enqueueStyles('picking-points-styles', plugin_dir_url(__DIR__).'/resources/assets/css/style.css');

        $role = new PickingPointRole();
        $role->register();

        $changeOrderStatusShortcode = $this->getContainer()->get(ChangeOrderStatus::class);
        $changeOrderStatusShortcode->add();

        add_filter( 'woocommerce_shipping_methods', [$this, 'livrika_picking_points'] );
        add_action( 'woocommerce_shipping_init', [$this, 'livrika_picking_points_init'] );

        add_action( 'wp_ajax_dokan-get-shipping-zone', [ $this, 'get_shipping_zone' ], 0 );

        add_action( 'init', [ $this, 'register_custom_statuses'] );
        add_filter( 'wc_order_statuses', [ $this, 'add_ongoing_to_order_statuses'] );
        add_filter( 'dokan_get_order_status_class', [ $this, 'dokan_add_custom_order_status_button_class'], 10, 2 );
        add_filter( 'dokan_get_order_status_translated', [ $this, 'dokan_add_custom_order_status_translated'], 10, 2 );

        add_action('woocommerce_checkout_create_order',  [ $this, 'save_branch_office_code'] , 20, 2);
        add_action('dokan_checkout_update_order_meta',  [ $this, 'save_livrika_picking_point'] , 20);
        add_filter( 'woocommerce_email_classes', [ $this, 'register_livrika_emails'], 90, 1 );

    }

    public function livrika_picking_points( $methods )
    {
        $methods['livrika_picking_points'] = PickingPointsShippingMethod::class;
        $methods['livrika_vendor_shipping'] = VendorShipping::class;

        return $methods;
    }
    public function livrika_picking_points_init()
    {

        //global $woocommerce;

        /*if ( ! class_exists( 'Dokan_Shipping_Method' ) ) {
            return;
        }
*/
        //WC()->shipping->register_shipping_method( 'PickingPointsShippingMethod' );

        require_once __DIR__.'/ShippingMethods/PickingPointsShippingMethod.php';
        require_once __DIR__.'/Overrides/VendorShipping.php';

    }

    public function get_shipping_zone() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'dokan_reviews' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ) );
        }

        if ( isset( $_POST['zoneID'] ) ) {
            $zones = \Adue\LivrikaPickingPoints\Overrides\ShippingZone::get_zone( absint( $_POST['zoneID'] ) );
        } else {
            $zones = \Adue\LivrikaPickingPoints\Overrides\ShippingZone::get_zones();
            // we are sorting by `zone_order` key of the zone.
            usort(
                $zones,
                function ( $zone1, $zone2 ) {
                    // handle the `Locations not covered by your other zones`
                    // here zone id = 0 is the zone "not covered by your other zones";.
                    if ( 0 === $zone1['id'] ) {
                        return 1;
                    } elseif ( 0 === $zone2['id'] ) {
                        return -1;
                    }

                    return $zone1['zone_order'] <=> $zone2['zone_order'];
                }
            );
        }

        //$zones['available_methods']['livrika_picking_posts'] = 'Puntos de retiro';

        wp_send_json_success( $zones );
    }

    public function register_custom_statuses() {
        register_post_status( 'wc-ongoing', array(
            'label'                     => _x( 'En camino', 'Order status', 'woocommerce' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'En camino <span class="count">(%s)</span>', 'En camino <span class="count">(%s)</span>', 'woocommerce' )
        ) );

        register_post_status( 'wc-delivered', array(
            'label'                     => _x( 'En destino', 'Order status', 'woocommerce' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'En destino <span class="count">(%s)</span>', 'En destino <span class="count">(%s)</span>', 'woocommerce' )
        ) );

        register_post_status( 'wc-withdrawn', array(
            'label'                     => _x( 'Retirado', 'Order status', 'woocommerce' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Retirado <span class="count">(%s)</span>', 'Retirado <span class="count">(%s)</span>', 'woocommerce' )
        ) );
    }
    public function add_ongoing_to_order_statuses( $order_statuses ) {
        $new_order_statuses = array();
        // add new order status after processing
        foreach ( $order_statuses as $key => $status ) {
            $new_order_statuses[ $key ] = $status;
            if ( 'wc-completed' === $key ) {
                $new_order_statuses['wc-ongoing'] = 'En camino a punto de retiro';
                $new_order_statuses['wc-delivered'] = 'Llegado a punto de retiro';
                $new_order_statuses['wc-withdrawn'] = 'Retirado por el cliente';
            }
        }
        return $new_order_statuses;
    }

    public function dokan_add_custom_order_status_button_class($text, $status)
    {
        switch ( $status ) {
            case 'wc-ongoing':
            case 'wc-delivered':
            case 'ongoing':
            case 'delivered':
                $text = 'info';
                break;
            case 'wc-withdrawn':
            case 'withdrawn':
                $text = 'success';
                break;
        }
        return $text;
    }

    public function dokan_add_custom_order_status_translated( $text, $status ) {
        switch ( $status ) {
            case 'wc-ongoing':
            case 'ongoing':
                $text = __( 'En camino', 'text_domain' );
                break;
            case 'wc-delivered':
            case 'delivered':
                $text = __( 'Llegado a punto de retiro', 'text_domain' );
                break;
            case 'wc-withdrawn':
            case 'withdrawn':
                $text = __( 'Retirado por el cliente', 'text_domain' );
                break;
        }
        return $text;
    }

    function save_branch_office_code( $order, $data ) {
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
        $chosen_shipping = $chosen_methods[0];
        $shippingMethods = $order->get_shipping_methods();
        $shippingMethodId = @array_shift($shippingMethods)['method_id'];

        var_dump([
            $chosen_methods,
            $chosen_shipping,
            $shippingMethods,
            $shippingMethodId,
        ]);die;

        $order->update_meta_data( 'shipping_method_id', $shippingMethodId);

        if($shippingMethodId == 'livrika_picking_points') {
            $order->update_meta_data( 'has_picking_point', true);
            $order->update_meta_data( 'picking_point_id', str_replace('livrika_picking_points-', '', $chosen_shipping));
        }
    }

    public function save_livrika_picking_point($orderId)
    {
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
        $chosen_shipping = $chosen_methods[0];
        $order = wc_get_order( $orderId );
        $shippingMethods = $order->get_shipping_methods();
        $shippingMethodId = @array_shift($shippingMethods)['method_id'];

        if(str_contains($chosen_shipping, 'livrika_picking_points')) {
            $order->update_meta_data( 'has_picking_point', true);
            $chosenShippingArray = explode('-', $chosen_shipping);
            $pickingPointId = count($chosenShippingArray) ? array_pop($chosenShippingArray) : 0;
            if($pickingPointId) {
                $order->update_meta_data( 'picking_point_id', $pickingPointId);
                $order->save();
            }
        }
    }

    public function register_livrika_emails( $emails ) {
        $emails['WC_Delivered'] = new WcDeliveredNotificationToClient();
        $emails['WC_Delivered_To_Admin'] = new WcDeliveredNotificationToVendor();
        $emails['WC_Withdrawn'] = new WcWithdrawnNotificationToClient();
        $emails['WC_Withdrawn_To_Vendor'] = new WcWithdrawnNotificationToVendor();
        return $emails;
    }

    public function run()
    {
        $loader = $this->getContainer()->get(Loader::class);
        $loader->run();
    }

}