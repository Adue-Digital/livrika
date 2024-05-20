<?php

namespace Adue\LivrikaPickingPoints\DashboardPages;

use Adue\WordPressBasePlugin\Traits\LoaderTrait;
use Adue\WordPressBasePlugin\Traits\ViewTrait;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;

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


                    <?php foreach ($_POST['orders'] as $orderId) : ?>

                        <div style="text-align: center;width: 25%;display: inline-block">
                            <qrcode value="<?php echo site_url(); ?>/livrika-track-order?order_id=<?php echo $orderId; ?>" ec="H" style="width: 50mm;"></qrcode>
                            #<?php echo $orderId; ?>
                        </div>

                    <?php endforeach; ?>

                </page>
                <?php
                $content = ob_get_clean();

                /*echo "<pre>";
                var_dump($content);die;*/

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

            if(isset($_POST['generate_pdf']) && $_POST['generate_pdf']) {
                var_dump($_POST);die;
                $html2pdf = new Html2Pdf();

                $html='
                    <h1>HTML2PDF is easy</h1>
                    <p>The HTML2PDF API makes it simple to convert web pages to PDF.</p>
                    <p>It should be noted that specific tags must be implemented to use the html2pdf</p>
                ';
                $html2pdf->writeHTML($html);
                $html2pdf->output();
                exit;
            }

            $this->view()->set('orders', $this->getPendingOrders());
            $this->view()->render('vendor/dashboard/tracking-qr-codes');
        }
    }

    private function getPendingOrders()
    {
        return dokan_get_seller_orders(dokan_get_current_user_id(), []);
    }

}