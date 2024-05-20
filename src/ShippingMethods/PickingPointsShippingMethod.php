<?php

namespace Adue\LivrikaPickingPoints\ShippingMethods;

class PickingPointsShippingMethod extends \WC_Shipping_Method
{

    public $cost;
    //public $default;

    public function __construct() {
        $this->id             = 'livrika_picking_points';
        $this->title          = __( 'Puntos de retiro', 'mi-text-domain' );
        $this->method_title   = __( 'Puntos de retiro', 'mi-text-domain' );
        $this->method_description = __( 'Descripción de Mi Método de Envío.', 'mi-text-domain' );
        $this->supports             = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal', 'settings' );
        $this->cost = 0;
        $this->init();

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init()
    {
        // Load the settings API
        $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

        $this->instance_form_fields = array(
            'title' => array(
                'title'         => __( 'Method title', 'dokan' ),
                'type'          => 'text',
                'description'   => __( 'This controls the title which the user sees during checkout.', 'dokan' ),
                'default'       => __( 'Puntos de retiro', 'dokan' ),
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

    public function is_available($package)
    {
        return true;
    }

    public function calculate_shipping( $package = [] )
    {
        // Aquí puedes implementar la lógica para calcular el costo de envío
        // $package contiene información sobre el pedido, como el destino y los productos

        // Calcula el costo de envío (por ejemplo, basado en la distancia, peso, etc.)
        $shipping_cost = 10.00; // Costo de envío fijo por ejemplo

        // Añade el método de envío al arreglo de métodos de envío disponibles
        $this->add_rate( array(
            'id'       => $this->id,
            'label'    => $this->title,
            'cost'     => $shipping_cost,
            'tax_class'=> '',
            'calc_tax' => 'per_order'
        ) );
    }
}