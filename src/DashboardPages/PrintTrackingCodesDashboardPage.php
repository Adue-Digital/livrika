<?php

namespace Adue\LivrikaPickingPoints\DashboardPages;

use Adue\WordPressBasePlugin\Traits\LoaderTrait;
use Adue\WordPressBasePlugin\Traits\ViewTrait;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;
use WC_Order;

class PrintTrackingCodesDashboardPage
{

    use LoaderTrait, ViewTrait;

    public function register()
    {
        $this->loader()->addFilter( 'dokan_query_var_filter', $this, 'addQueryVar' );
        $this->loader()->addFilter( 'dokan_get_dashboard_nav', $this, 'addPrintTrackingQrsPage', 20 );
        //$this->loader()->addAction('dokan_dashboard_wrap_before', $this, 'pageContent');
        $this->loader()->addAction( 'dokan_load_custom_template', $this, 'pageContent', 25 );

        if(isset($_POST['generate_pdf']) && $_POST['generate_pdf']) {
            try {
                ob_start();

                ?>
                <page backtop="10mm" >
                    <page_header>
                        <table style="width: 100%; border: solid 1px black;">
                            <tr>
                                <td style="text-align: left;    width: 50%">Livrika</td>
                                <td style="text-align: right;    width: 50%">Códigos para seguimiento de envío</td>
                            </tr>
                        </table>
                    </page_header>
                    <h1>Códigos para seguimiento</h1>


                    <table class="page_header">
                        <?php $x = 0; ?>
                        <?php foreach ($_POST['orders'] as $orderId) : ?>

                            <?php if ($x % 4 == 0) {echo "<tr>";} ?>

                            <td style="width: 25%; display:inline-block;text-align:center">
                                <qrcode value="<?php echo site_url(); ?>/estado-de-orden?order_id=<?php echo $orderId; ?>" ec="H" style="width: 50mm;" label=""></qrcode><br>
                                #<?php echo $orderId; ?>
                            </td>

                            <?php if ($x && ($x + 1) % 4 == 0) {echo "</tr>";} ?>

                        <?php $x++; ?>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < ($x % 4); $i++) : ?>
                            <td></td>
                        <?php endfor; ?>
                        <?php if (($x + 1) % 4) {echo "</tr>";} ?>
                    </table>
                </page>
                <?php
                $content = ob_get_clean();

                /*echo "<pre>";
                var_dump(htmlspecialchars($content));die;*/

                $html2pdf = new Html2Pdf('P', 'A4', 'fr');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($content);
                $html2pdf->output('qrcode.pdf');
            } catch (Html2PdfException $e) {
                $html2pdf->clean();

                $formatter = new ExceptionFormatter($e);
                echo $formatter->getHtmlMessage();
            }
            //exit;
        }
    }

    public function addQueryVar($query_vars)
    {
        $query_vars['tracking-qr-codes'] = 'tracking-qr-codes';
        return $query_vars;
    }

    public function registerPage()
    {
        $this->loader()->addAction('dokan_dashboard_left_widgets', $this, 'addPrintTrackingQrsPage', 11);
    }

    public function addPrintTrackingQrsPage($menus)
    {

        $new_page = array(
            'title' => __( 'Imprimir QRs de seguimiento', 'text-domain' ),
            'icon'  => '', // Icono de WordPress que deseas utilizar, por ejemplo, 'dashicons-admin-page' para una página
            'url'   => dokan_get_navigation_url( 'tracking-qr-codes' ), // URL de tu página personalizada
            'pos'   => 97 // Posición en el menú
        );

        // Insertar la nueva página en el menú de navegación de Dokan
        $menus['tracking-qr-codes'] = $new_page;

        return $menus;

    }

    public function pageContent($query_vars)
    {
        if ( isset( $query_vars['tracking-qr-codes'] ) ) {

            $this->view()->set('orderStates', $this->getOrderStates());
            $this->view()->set('orders', $this->getPendingOrders());
            $this->view()->render('vendor/dashboard/tracking-qr-codes');
        }
    }

    private function getPendingOrders()
    {
        //return dokan_get_seller_orders(dokan_get_current_user_id(), []);

        if (!isset($_GET['fecha_desde'])) {
            $dateFrom = date('Y-m-d', strtotime('1970-01-01'));
        } else {
            $dateFrom = $_GET['fecha_desde'];
        }

        if (!isset($_GET['fecha_hasta'])) {
            $dateTo = date('Y-m-d');
        } else {
            $dateTo = $_GET['fecha_hasta'];
        }

        $metaQuery = [
            [
                'key' => '_dokan_vendor_id',
                'value' => get_current_user_id(),
                'compare' => 'LIKE',
            ]
        ];

        $dateQuery = [
            'after'     => $dateFrom." 00:00:00",
            'before'    => $dateTo." 23:59:59",
            'inclusive' => true,
        ];

        $args = [
            'post_type'      => 'shop_order',
            'posts_per_page' => -1,
            'post_status'   => $_GET['estado'] ?? 'any',
            'date_query'    => $dateQuery,
            'meta_query'     => $metaQuery
        ];

        /*echo "<pre>";
        var_dump($args);die;*/

        return get_posts($args);
    }

    private function getOrderStates()
    {

        return [
            "wc-pending" => 'Pendiente de pago',
            "wc-processing" => 'Procesando',
            "wc-on-hold" => 'En espera',
            "wc-completed" => 'Completado',
            "wc-ongoing" => 'En camino a punto de retiro',
            "wc-delivered" => 'Llegado a punto de retiro',
            "wc-withdrawn" => 'Retirado por el cliente',
            "wc-cancelled" => 'Cancelled',
            "wc-refunded" => 'Devuelto',
            "wc-failed" => 'Falló',
            "wc-checkout-draft" => 'Borrador',
        ];
    }

}