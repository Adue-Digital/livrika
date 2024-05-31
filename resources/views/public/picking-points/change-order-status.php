
<?php if(!is_user_logged_in()) : ?>

    <p>Debes iniciar sesión con tu usuario de punto de retiro para cambiar el estado de la orden</p>

    <?php echo wp_login_form([
        'redirect' => site_url().'/estado-de-orden?orden_id='.$_GET['order_id'],
    ]); ?>

<?php else : ?>

    <?php if(isset($error) && $error) : ?>

        <?php echo $error; ?>

    <?php else : ?>

        <?php if(isset($successMessage)) : ?>

            <div class="alert alert-success" style="display: block; background: #D6FFD6; color: #5B841B; padding: 5px; border-radius: 5px;text-align: center;margin-bottom: 10px">
                <?php echo $successMessage; ?>
            </div>

        <?php endif; ?>

        <?php if($order->get_status() == 'processing'): ?>

            ¿Cambiar estado a "En camino"?

        <?php elseif ($order->get_status() == 'wc-ongoing' || $order->get_status() == 'ongoing') : ?>

            <h3>¿La orden ha llegado correctamente?</h3>
            <p>Por favor, corroborá que todos los datos sean los correctos antes de confirmar</p>

            <?php do_action( 'woocommerce_view_order', $order->ID ); ?>

            <form action="">
                <input type="hidden" name="picking_point_change_order_status" value="1">
                <input type="hidden" name="order_id" value="<?php echo $order->ID; ?>">
                <input type="hidden" name="new_status" value="wc-delivered">
                <button type="submit">Confirmar recepción</button>
            </form>

        <?php elseif ($order->get_status() == 'wc-delivered' || $order->get_status() == 'delivered') : ?>

            <h3>¿El cliente está retirando la orden?</h3>
            <p>Por favor, corroborá que todos los datos sean los correctos antes de confirmar</p>

            <?php do_action( 'woocommerce_view_order', $order->ID ); ?>

            <form action="">
                <input type="hidden" name="picking_point_change_order_status" value="1">
                <input type="hidden" name="order_id" value="<?php echo $order->ID; ?>">
                <input type="hidden" name="new_status" value="wc-withdrawn">
                <button type="submit">Confirmar retiro</button>
            </form>

        <?php elseif ($order->get_status() == 'wc-withdrawn' || $order->get_status() == 'withdrawn') : ?>

            La orden ya ha sido retirada.

        <?php else : ?>

            No se puede cambiar el estado de la orden ya que no se encuentra en ninguno de los permitidos.

        <?php endif; ?>

    <?php endif; ?>

<?php endif; ?>