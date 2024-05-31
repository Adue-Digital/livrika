<?php do_action( 'dokan_dashboard_wrap_start' ); ?>

<div class="dokan-dashboard-wrap">

    <?php

    /**
     *  Adding dokan_dashboard_content_before hook
     *  dokan_picking-points_content_before hook
     *
     * @hooked get_dashboard_side_navigation
     *
     * @since  2.4
     */
    do_action( 'dokan_dashboard_content_before' );
    ?>

    <div class="dokan-dashboard-content dokan-picking-points-content">

        <article class="dokan-picking-points-area">

            <header class="dokan-dashboard-header">
                <h1>Imprimir QRs de retiro</h1>
            </header>

            <div class="entry-content">

                <form>
                    <div class="livrika-filters">
                        <div class="livrika-filter">
                            <label>Fecha desde<br>
                            <input name="fecha_desde" type="date" value="<?php echo !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '' ?>"
                                   placeholder="dd-mm-yyyy"
                                   min="1997-01-01"
                                   max="2030-12-31"
                            /></label>
                        </div>
                        <div class="livrika-filter">
                            <label>Fecha hasta<br>
                                <input name="fecha_hasta" type="date" placeholder="Fecha hasta" value="<?php echo !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '' ?>" /></label>
                        </div>
                        <div class="livrika-filter">
                            <label>Estado<br>
                            <select name="estado">
                                <option value="any">Selecciona una estado</option>
                                <?php foreach ($orderStates as $code => $state) : ?>
                                    <option value="<?php echo $code; ?>"
                                        <?php echo (!empty($_GET['estado']) && $_GET['estado'] == $code) ? 'selected' : '' ?>
                                    ><?php echo $state; ?></option>
                                <?php endforeach; ?>
                            </select>
                            </label>
                        </div>
                        <div class="livrika-filter">
                            <br>
                            <button type="submit">Buscar</button> - <a href="/dashboard/tracking-qr-codes/">Limpiar filtros</a>
                        </div>
                    </div>
                </form>

                <form method="post" action="">
                    <input type="hidden" name="generate_pdf" value="1">
                    <table>
                        <thead>
                            <th></th>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Punto de retiro</th>
                            <th>Estado</th>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($orders as $orderPost) :
                                $order = wc_get_order($orderPost->ID);
                                $pickingPointId = get_post_meta($order->ID, 'picking_point_id', true);
                                $pickingPointPost = get_post($pickingPointId);
                                if($pickingPointId) :
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="orders[]" value="<?php echo $order->ID; ?>">
                                        </td>
                                        <td>#<?php echo $order->ID; ?></td>
                                        <td><?php echo $order->get_date_created()->format('m-d-Y'); ?></td>
                                        <td><?php echo $order->get_billing_first_name()." ".$order->get_billing_last_name(); ?></td>
                                        <td><?php echo $pickingPointPost->post_title; ?></td>
                                        <td>
                                            <?php echo isset($orderStates['wc-'.$order->status]) ? $orderStates['wc-'.$order->status] : _e($order->status, 'woocommerce'); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit">Generar PDF</button>
                </form>

            </div><!-- .entry-content -->

        </article>

        <?php

        /**
         *  Adding dokan_picking-points_content_inside_after hook
         *
         * @since 2.4
         */
        do_action( 'dokan_picking-points_content_inside_after' );
        ?>
    </div><!-- .dokan-dashboard-content -->

    <?php
    /**
     *  Adding dokan_dashboard_content_after hook
     *  dokan_picking-points_content_after hook
     *
     * @since 2.4
     */
    do_action( 'dokan_dashboard_content_after' );
    do_action( 'dokan_picking-points_content_after' );
    ?>
</div><!-- .dokan-dashboard-wrap -->

<?php do_action( 'dokan_dashboard_wrap_end' ); ?>