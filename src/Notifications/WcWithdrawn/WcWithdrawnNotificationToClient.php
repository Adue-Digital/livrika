<?php

namespace Adue\LivrikaPickingPoints\Notifications\WcWithdrawn;

class WcWithdrawnNotificationToClient extends WcWithdrawnBaseNotification {

    /**
     * Create an instance of the class.
     *
     * @access public
     * @return void
     */
    function __construct() {
        // Email slug we can use to filter other data.
        $this->id          = 'wc_withdrawn_notification_to_client';
        $this->title       = __( '¡Tu pedido ya fue retirado!', 'adue-woo-ca' );
        $this->description = __( 'Notificación que se le envía al cliente cuando su pedido ya fue retirado del punto de retiro', 'adue-woo-ca' );
        // For admin area to let the user know we are sending this email to customers.
        $this->customer_email = true;
        $this->heading     = __( '¡Tu pedido ya fue retirado!', 'adue-woo-ca' );

        $this->subject     = __( '¡Tu pedido ya fue retirado!', 'adue-woo-ca' );

        // Template paths.
        $this->template_html  = 'wc_delivered.php';
        $this->template_plain = 'plain/wc_delivered.php';
        $this->template_base  = __DIR__ . '/../../resources/views/emails/';

        parent::__construct();
    }

}