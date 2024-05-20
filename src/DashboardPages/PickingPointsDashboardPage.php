<?php

namespace Adue\LivrikaPickingPoints\DashboardPages;

use Adue\LivrikaPickingPoints\PostTypes\VendorPickingPointPostType;
use Adue\WordPressBasePlugin\Traits\LoaderTrait;
use Adue\WordPressBasePlugin\Traits\ViewTrait;
use WeDevs\Dokan\Vendor\Vendor;

class PickingPointsDashboardPage
{
    use LoaderTrait, ViewTrait;

    public function register()
    {
        $this->loader()->addFilter( 'dokan_query_var_filter', $this, 'addQueryVar' );
        $this->loader()->addFilter( 'dokan_get_dashboard_nav', $this, 'addPickingPointsPage', 20 );
        //$this->loader()->addAction('dokan_dashboard_wrap_before', $this, 'pageContent');
        $this->loader()->addAction( 'dokan_load_custom_template', $this, 'pageContent', 25 );
    }

    public function addQueryVar($query_vars)
    {
        $query_vars['picking-points'] = 'picking-points';
        return $query_vars;
    }

    public function registerPage()
    {
        $this->loader()->addAction('dokan_dashboard_left_widgets', $this, 'addPickingPointsPage', 11);
    }

    public function addPickingPointsPage($menus)
    {

        $new_page = array(
            'title' => __( 'Puntos de retiro', 'text-domain' ),
            'icon'  => '', // Icono de WordPress que deseas utilizar, por ejemplo, 'dashicons-admin-page' para una página
            'url'   => dokan_get_navigation_url( 'picking-points' ), // URL de tu página personalizada
            'pos'   => 97 // Posición en el menú
        );

        // Insertar la nueva página en el menú de navegación de Dokan
        $menus['picking-points'] = $new_page;

        return $menus;

    }

    public function pageContent($query_vars)
    {
        if ( isset( $query_vars['picking-points'] ) ) {

            if(isset($_POST['save_picking_point']) && $_POST['save_picking_point'])
                $this->addPickingPoint($_POST['picking_point_id']);

            if(isset($_POST['remove_picking_point']) && $_POST['remove_picking_point'])
                $this->removePickingPoint($_POST['picking_point_id']);

            $pickingPoints = get_posts([
                'posts_per_page' => -1,
                'post_type'      => 'picking_point',
            ]);
            $activeTab = isset($_GET['tab']) && in_array($_GET['tab'], ['picking-points-list', 'my-picking-points']) ? $_GET['tab'] : 'picking-points-list';
            $this->view()->set('activeTab', $activeTab);

            $vendorPickingPoints = $this->getVendorPickingPoints();

            $this->view()->set('provincias', [
                'A' => 'Salta',
                'B' => 'Provincia de Buenos Aires',
                'C' => 'Ciudad Autonoma Buenos Aires (o Capital Federal)',
                'D' => 'San Luis',
                'E' => 'Entre Rios',
                'F' => 'La Rioja',
                'G' => 'Santiago del Estero',
                'H' => 'Chaco',
                'J' => 'San Juan',
                'K' => 'Catamarca',
                'L' => 'La Pampa',
                'M' => 'Mendoza',
                'N' => 'Misiones',
                'P' => 'Formosa',
                'Q' => 'Neuquen',
                'R' => 'Rio Negro',
                'S' => 'Santa Fe',
                'T' => 'Tucuman',
                'U' => 'Chubut',
                'V' => 'Tierra del Fuego',
                'W' => 'Corrientes',
                'X' => 'Cordoba',
                'Y' => 'Jujuy',
                'Z' => 'Santa Cruz',
            ]);
            $this->view()->set('vendorPickingPoints', $vendorPickingPoints);
            $this->view()->set('pickingPoints', $pickingPoints);
            $this->view()->render('vendor/dashboard/picking-points');
        }
    }

    private function getVendorPickingPoint($pickingPointId)
    {
        $vendorPickingPointsPosts = new \WP_Query([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'meta_key' => 'vendor_id',
                    'meta_value' => dokan_get_current_user_id(),
                    'type' => 'NUMERIC'
                ],
                [
                    'meta_key' => 'picking_point_id',
                    'meta_value' => $pickingPointId,
                    'type' => 'NUMERIC'
                ]
            ],
            'post_type' => 'vendor-picking_point',
            'posts_per_page' => 1
        ]);


        if ( $vendorPickingPointsPosts->have_posts() ) {
            while ( $vendorPickingPointsPosts->have_posts() ) {
                $vendorPickingPointsPosts->the_post();
                $value = get_post_meta( get_the_ID(), 'picking_point_id', true );
                if($value == $pickingPointId);
                    return get_post(get_the_ID());
            }
        }

        return null;

        wp_reset_postdata();
    }

    private function getVendorPickingPoints()
    {
        $vendorPickingPointsPosts = new \WP_Query([
            'meta_key' => 'vendor_id',
            'meta_value' => dokan_get_current_user_id(),
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

    private function addPickingPoint($pickingPointId)
    {

        //if($this->getVendorPickingPoint($pickingPointId) != null) return;

        $currentUserId = get_current_user_id();
        $user = wp_get_current_user();
        if (!dokan_is_user_seller($currentUserId) || array_intersect(['administrator'], $user->roles )) {
            $postId = wp_insert_post([
                'post_title' => dokan_get_current_user_id()."-".$pickingPointId,
                'post_type' => 'vendor-picking_point',
                'post_status' => 'publish',
            ]);
            update_post_meta($postId, 'vendor_id', dokan_get_current_user_id());
            update_post_meta($postId, 'picking_point_id', $pickingPointId);
            return true;
        } else {
            echo "Nada";
        }
    }

    private function removePickingPoint($pickingPointId)
    {
        $vendorPickingPointsPosts = new \WP_Query([
            'meta_query' => [
            'relation' => 'AND',
                [
                    'meta_key' => 'vendor_id',
                    'meta_value' => dokan_get_current_user_id(),
                ],
                [
                    'meta_key' => 'picking_point_id',
                    'meta_value' => $pickingPointId,
                ]
            ],
            'post_type' => 'vendor-picking_point',
            'posts_per_page' => 1
        ]);

        if ( $vendorPickingPointsPosts->have_posts() ) {
            while ( $vendorPickingPointsPosts->have_posts() ) {
                $vendorPickingPointsPosts->the_post();
               wp_delete_post(get_the_ID());
            }
        }
        wp_reset_postdata();
    }

}
