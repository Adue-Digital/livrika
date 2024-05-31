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
                <h1>Puntos de retiro</h1>
                <p>En esta sección vas a poder seleccionar los puntos de retiro que se mostrarán como forma de envío en las compras de tus clientes</p>
            </header>

            <div class="entry-content">

                <div class="picking-points-tabs">
                    <div class="picking-points-tab">
                        <a href="<?php echo dokan_get_navigation_url( 'picking-points' ); ?>?tab=picking-points-list" class="<?php echo $activeTab == 'picking-points-list' ? 'active' : ''; ?>">Listado</a>
                        <a href="<?php echo dokan_get_navigation_url( 'picking-points' ); ?>?tab=my-picking-points" class="<?php echo $activeTab == 'my-picking-points' ? 'active' : ''; ?>">Mis puntos de retiro</a>
                    </div>
                </div>

                <div class="picking-points-tabs-content">

                    <?php if($activeTab == 'picking-points-list') : ?>
                        <div class="picking-points-tab-list">

                            <form>
                                <div class="livrika-filters">
                                    <div class="livrika-filter">
                                        <select name="provincia">
                                            <option value="all">Selecciona una provincia</option>
                                            <?php foreach ($provincias as $code => $provincia) : ?>
                                                <option value="<?php echo $code; ?>"
                                                    <?php echo (!empty($_GET['provincia']) && $_GET['provincia'] == $code) ? 'selected' : '' ?>
                                                ><?php echo $provincia; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="livrika-filter">
                                        <input name="codigo_postal" type="text" placeholder="Código postal" value="<?php echo !empty($_GET['codigo_postal']) ? $_GET['codigo_postal'] : '' ?>" />
                                    </div>
                                    <div class="livrika-filter">
                                        <input name="nombre" type="text" placeholder="Nombre" value="<?php echo !empty($_GET['nombre']) ? $_GET['nombre'] : '' ?>" />
                                    </div>
                                    <div class="livrika-filter">
                                        <button type="submit">Buscar</button> - <a href="/dashboard/picking-points/">Limpiar filtros</a>
                                    </div>
                                </div>
                            </form>

                            <?php if (count($pickingPoints)) : ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Provincia</th>
                                        <th>Ciudad</th>
                                        <th>Código postal</th>
                                        <th>Dirección</th>
                                        <th>Teléfono</th>
                                        <th>Hr. de atención</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pickingPoints as $pickingPoint) : ?>
                                        <tr>
                                            <td><?php echo $pickingPoint->post_title; ?></td>
                                            <td><?php echo $provincias[get_post_meta($pickingPoint->ID, 'provincia', true)]; ?></td>
                                            <td><?php echo get_post_meta($pickingPoint->ID, 'ciudad', true); ?></td>
                                            <td><?php echo get_post_meta($pickingPoint->ID, 'codigo_postal', true); ?></td>
                                            <td><?php echo get_post_meta($pickingPoint->ID, 'direccion', true); ?></td>
                                            <td><?php echo get_post_meta($pickingPoint->ID, 'telefono', true); ?></td>
                                            <td><?php echo get_post_meta($pickingPoint->ID, 'horarios_de_atencion', true); ?></td>
                                            <td>
                                                <?php if(in_array($pickingPoint, $vendorPickingPoints)) : ?>
                                                    <form action="" method="post">
                                                        <input type="hidden" name="remove_picking_point" value="1">
                                                        <input type="hidden" name="picking_point_id" value="<?php echo $pickingPoint->ID; ?>">
                                                        <button type="submit">Eliminar</button>
                                                    </form>
                                                <?php else : ?>
                                                    <form action="" method="post">
                                                        <input type="hidden" name="save_picking_point" value="1">
                                                        <input type="hidden" name="picking_point_id" value="<?php echo $pickingPoint->ID; ?>">
                                                        <button type="submit">Agregar</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                            </table>
                            <?php else : ?>
                                <p>No hay puntos de retiro con ese criterio de búsqueda</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($activeTab == 'my-picking-points') : ?>
                        <div class="picking-points-my-picking-points">
                            <table>
                                <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Provincia</th>
                                    <th>Ciudad</th>
                                    <th>Código postal</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                    <th>Hr. de atención</th>
                                    <th>Acción</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($vendorPickingPoints as $pickingPoint) : ?>
                                    <tr>
                                        <td><?php echo $pickingPoint->post_title; ?></td>
                                        <td><?php echo $provincias[get_post_meta($pickingPoint->ID, 'provincia', true)]; ?></td>
                                        <td><?php echo get_post_meta($pickingPoint->ID, 'ciudad', true); ?></td>
                                        <td><?php echo get_post_meta($pickingPoint->ID, 'codigo_postal', true); ?></td>
                                        <td><?php echo get_post_meta($pickingPoint->ID, 'direccion', true); ?></td>
                                        <td><?php echo get_post_meta($pickingPoint->ID, 'telefono', true); ?></td>
                                        <td><?php echo get_post_meta($pickingPoint->ID, 'horarios_de_atencion', true); ?></td>
                                        <td>
                                            <form action="" method="post">
                                                <input type="hidden" name="remove_picking_point" value="1">
                                                <input type="hidden" name="picking_point_id" value="<?php echo $pickingPoint->ID; ?>">
                                                <button type="submit">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>


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
