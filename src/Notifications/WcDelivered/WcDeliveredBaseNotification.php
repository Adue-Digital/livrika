<?php

namespace Adue\LivrikaPickingPoints\Notifications\WcDelivered;

use \WC_Email;

class WcDeliveredBaseNotification extends WC_Email {

    /**
     * Create an instance of the class.
     *
     * @access public
     * @return void
     */
    function __construct() {
        parent::__construct();
    }

    public function trigger( $order_id ) {
        $this->object = wc_get_order( $order_id );

        if ( version_compare( '3.0.0', WC()->version, '>' ) ) {
            $order_email = $this->object->billing_email;
        } else {
            $order_email = $this->object->get_billing_email();
        }

        if($this->customer_email) {
            $this->recipient = $order_email;
        } else {
            $sellerId = dokan_get_seller_id_by_order( $order_id );
            $user = get_user_by('ID', $sellerId);
            $this->recipient = $user->user_email;
        }

        if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
            return;
        }
        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }

    public function get_content_html() {
        return wc_get_template_html( $this->template_html, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'			=> $this
        ), '', $this->template_base );
    }

    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => true,
            'email'			=> $this
        ), '', $this->template_base );
    }
}