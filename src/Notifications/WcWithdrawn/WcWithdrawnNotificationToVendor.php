<?php

namespace Adue\LivrikaPickingPoints\Notifications\WcWithdrawn;

class WcWithdrawnNotificationToVendor extends WcWithdrawnBaseNotification {

    /**
     * Create an instance of the class.
     *
     * @access public
     * @return void
     */
    function __construct() {
        // Email slug we can use to filter other data.
        $this->id          = 'wc_withdrawn_to_vendor';
        $this->title       = __( 'El pedido ya fue retirado por el cliente', 'adue-woo-ca' );
        $this->description = __( 'Notificación que se le envía al vendedor cuando el pedido ya fue retirado por el cliente', 'adue-woo-ca' );
        // For admin area to let the user know we are sending this email to customers.
        $this->customer_email = false;
        $this->heading     = __( 'El pedido ya fue retirado por el cliente', 'adue-woo-ca' );
        $this->subject     = __( 'El pedido ya fue retirado por el cliente', 'adue-woo-ca' );

        // Template paths.
        $this->template_html  = 'wc_delivered.php';
        $this->template_plain = 'plain/wc_delivered.php';
        $this->template_base  = __DIR__ . '/../../resources/views/emails/';

        parent::__construct();
    }

}