<?php

namespace Adue\LivrikaPickingPoints\Overrides;

use WeDevs\DokanPro\Shipping\Methods\VendorShipping as VendorShippingMethod;

/**
 * Table Rate Shipping Method Extender Class
 */

use Automattic\WooCommerce\Utilities\NumberUtil;
use WC_Eval_Math;
use WC_Shipping_Method;
use Adue\LivrikaPickingPoints\Overrides\ShippingZone;
use WeDevs\DokanPro\Shipping\SanitizeCost;

class VendorShipping extends VendorShippingMethod {

    /**
     * Default value.
     *
     * @var string $default
     */
    public $default = '';

    /**
     * Table Rates from Database
     */
    protected $options_save_name;

    /**
     * Table Rates from Database
     */
    public $default_option;

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct( $instance_id = 0 ) {
        $this->id                   = 'livrika_vendor_shipping';
        $this->instance_id          = absint( $instance_id );
        $this->method_title         = __( 'Livrika Vendor Shipping', 'dokan' );
        $this->method_description   = __( 'Charge varying rates based on user defined conditions', 'dokan' );
        $this->supports             = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal' );
        $this->default              = '';

        // Initialize settings
        $this->init();

        // additional hooks for post-calculations settings
        add_filter( 'woocommerce_shipping_chosen_method', array( $this, 'select_default_rate' ), 10, 2 );
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_shipping_zone_method_deleted', array( $this, 'delete_vendor_shipping_methods' ), 10, 3 );
    }

    /**
     * Init function.
     * initialize variables to be used
     *
     * @access public
     * @return void
     */
    public function init() {
        $this->instance_form_fields = array(
            'title' => array(
                'title'         => __( 'Method title', 'dokan' ),
                'type'          => 'text',
                'description'   => __( 'This controls the title which the user sees during checkout.', 'dokan' ),
                'default'       => __( 'Vendor Shipping', 'dokan' ),
                'desc_tip'      => true,
            ),
            'tax_status' => array(
                'title'         => __( 'Tax status', 'dokan' ),
                'type'          => 'select',
                'class'         => 'wc-enhanced-select',
                'default'       => 'taxable',
                'options'       => array(
                    'taxable'   => __( 'Taxable', 'dokan' ),
                    'none'      => _x( 'None', 'Tax status', 'dokan' ),
                ),
            ),
        );

        $this->title      = $this->get_option( 'title' );
        $this->tax_status = $this->get_option( 'tax_status' );
    }

    /**
     * Calculate_shipping function.
     *
     * @access public
     * @param array $package (default: array())
     * @return void
     */
    public function calculate_shipping( $package = array() ) {
        $rates = array();
        $zone = ShippingZone::get_zone_matching_package( $package );

        $seller_id = $package['seller_id'];

        if ( empty( $seller_id ) ) {
            return;
        }

        $shipping_methods = ShippingZone::get_shipping_methods( $zone->get_id(), $seller_id );

        if ( empty( $shipping_methods ) ) {
            return;
        }

        $sanitizer = new SanitizeCost();
        $pickingPoints = false;
        foreach ( $shipping_methods as $key => $method ) {
            $tax_rate  = ( $method['settings']['tax_status'] === 'none' ) ? false : '';
            $has_costs = false;
            $cost      = 0;

            if (
                'yes' !== $method['enabled'] ||
                'dokan_table_rate_shipping' === $method['id'] ||
                'dokan_distance_rate_shipping' === $method['id']
            ) {
                continue;
            }

            if ( $method['id'] === 'flat_rate' ) {
                $setting_cost = isset( $method['settings']['cost'] ) ? stripslashes_deep( $method['settings']['cost'] ) : '';

                if ( '' !== $setting_cost ) {
                    $has_costs = true;
                    $cost = $sanitizer->evaluate_cost(
                        $setting_cost, array(
                            'qty'  => $this->get_package_item_qty( $package ),
                            'cost' => $package['contents_cost'],
                        )
                    );
                }

                // Add shipping class costs.
                $shipping_classes = WC()->shipping->get_shipping_classes();

                if ( ! empty( $shipping_classes ) ) {
                    $found_shipping_classes = $this->find_shipping_classes( $package );
                    $highest_class_cost     = 0;
                    $calculation_type       = ! empty( $method['settings']['calculation_type'] ) ? $method['settings']['calculation_type'] : 'class';
                    foreach ( $found_shipping_classes as $shipping_class => $products ) {
                        // Also handles BW compatibility when slugs were used instead of ids
                        $shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
                        $class_cost_string   = $shipping_class_term && $shipping_class_term->term_id
                                                ? ( ! empty( $method['settings'][ 'class_cost_' . $shipping_class_term->term_id ] ) ? stripslashes_deep( $method['settings'][ 'class_cost_' . $shipping_class_term->term_id ] ) : '' )
                                                : ( ! empty( $method['settings']['no_class_cost'] ) ? $method['settings']['no_class_cost'] : '' );

                        if ( '' === $class_cost_string ) {
                            continue;
                        }

                        $has_costs = true;

                        $class_cost = $sanitizer->evaluate_cost(
                            $class_cost_string, array(
                                'qty'  => array_sum( wp_list_pluck( $products, 'quantity' ) ),
                                'cost' => array_sum( wp_list_pluck( $products, 'line_total' ) ),
                            )
                        );

                        if ( 'class' === $calculation_type ) {
                            $cost += $class_cost;
                        } else {
                            $highest_class_cost = $class_cost > $highest_class_cost ? $class_cost : $highest_class_cost;
                        }
                    }

                    if ( 'order' === $calculation_type && $highest_class_cost ) {
                        $cost += $highest_class_cost;
                    }
                }
            } elseif ( 'free_shipping' === $method['id'] ) {
                $is_available = $this->free_shipping_is_available( $package, $method );

                if ( $is_available ) {
                    $cost      = '0';
                    $has_costs = true;
                }
            } elseif ( 'livrika_picking_points' === $method['id'] ) {

                $has_costs = true;
                $pickingPoints = $this->getVendorPickingPoints($seller_id);

            } elseif ( ! empty( $method['settings']['cost'] ) ) {
                $has_costs = true;
                $cost      = $method['settings']['cost'];
            } else {
                $has_costs = true;
                $cost      = '0';
            }

            if ( ! $has_costs ) {
                continue;
            }

            if($pickingPoints) {
                foreach ($pickingPoints as $pickingPoint) {
                    $rates[] = array(
                        'id'          => $this->get_method_rate_id( $method )."-".$pickingPoint->ID,
                        'label'       => $method['title'].": ".$pickingPoint->post_title . " - " .
                            rwmb_get_value( 'direccion', [], $pickingPoint->ID ) . ", " .
                            rwmb_get_value( 'ciudad', [], $pickingPoint->ID ) . " " .
                            " (CP: ".rwmb_get_value( 'codigo_postal', [], $pickingPoint->ID ) . "), " .
                            rwmb_get_value( 'provincia', [], $pickingPoint->ID ),
                        'cost'        => $cost,
                        'description' => "La dirección",
                        'taxes'       => $tax_rate,
                        'default'     => 'off',
                    );
                }
            } else {
                $rates[] = array(
                    'id'          => $this->get_method_rate_id( $method ),
                    'label'       => $method['title'],
                    'cost'        => $cost,
                    'description' => ! empty( $method['settings']['description'] ) ? $method['settings']['description'] : '',
                    'taxes'       => $tax_rate,
                    'default'     => 'off',
                );
            }


        }

        // send shipping rates to WooCommerce
        if ( is_array( $rates ) && count( $rates ) > 0 ) {

            // cycle through rates to send and alter post-add settings
            foreach ( $rates as $key => $rate ) {
                $this->add_rate(
                    array(
                        'id'        => $rate['id'],
                        'label'     => apply_filters( 'dokan_vendor_shipping_rate_label', $rate['label'], $rate ),
                        'cost'      => $rate['cost'],
                        'meta_data' => array( 'description' => $rate['description'] ),
                        'package'   => $package,
                        'taxes'     => $rate['taxes'],
                    )
                );

                if ( $rate['default'] === 'on' ) {
                    $this->default = $rate['id'];
                }
            }
        }
    }

    private function getVendorPickingPoints($sellerId)
    {
        $vendorPickingPointsPosts = new \WP_Query([
            'meta_key' => 'vendor_id',
            'meta_value' => $sellerId,
            'post_type' => 'vendor-picking_point',
            'posts_per_page' => -1
        ]);

        $pickingPointPosts = [];
        if ( $vendorPickingPointsPosts->have_posts() ) {
            while ( $vendorPickingPointsPosts->have_posts() ) {
                $vendorPickingPointsPosts->the_post();
                $pickingPointId = get_post_meta(get_the_ID(), 'picking_point_id', true);
                $pickingPointPosts[] = get_post($pickingPointId);
            }
        }
        wp_reset_postdata();

        return $pickingPointPosts;
    }

    /**
     * Hide shipping rates when one has option enabled.
     *
     * @access public
     *
     * @param array $rates Array of rates found for the package.
     *
     * @return array
     */
    public function hide_other_options( $rates ) {
        $hide_key = false;

        // return if no rates have been added
        if ( ! isset( $rates ) || empty( $rates ) ) {
            return $rates;
        }

        // cycle through available rates
        foreach ( $rates as $key => $rate ) {
            if ( $rate['hide_ops'] === 'on' ) {
                $hide_key = $key;
            }
        }

        if ( $hide_key ) {
            return array( $hide_key => $rates[ $hide_key ] );
        }

        return $rates;
    }

    /**
     * Get shpping method id
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function get_method_rate_id( $method ) {
        return apply_filters( 'dokan_get_vendor_shipping_method_id', $method['id'] . ':' . $method['instance_id'] );
    }

    /**
     * Delete Vendor shipping methods if Admin delete 'Vendor Shipping' in WC > Settings > Shipping > Zone
     *
     * @since 3.7.0
     *
     * @param int $instance_id
     * @param string $method_id
     * @param int $zone_id
     */
    public function delete_vendor_shipping_methods( $instance_id, $method_id, $zone_id ) {
        global $wpdb;

        if ( 'livrika_vendor_shipping' !== $method_id ) {
            return;
        }

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}dokan_shipping_zone_methods WHERE zone_id = %d AND method_id IN ( 'flat_rate', 'free_shipping', 'local_pickup' )",
                $zone_id
            )
        );
    }
}
