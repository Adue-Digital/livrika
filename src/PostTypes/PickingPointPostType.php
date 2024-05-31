<?php

namespace Adue\LivrikaPickingPoints\PostTypes;

use Adue\WordPressBasePlugin\Modules\Registers\PostTypes\BasePostType;

class PickingPointPostType extends BasePostType
{
    protected string $name = 'Picking points';
    protected string $singularName = 'Picking point';
    protected string $postType = 'picking_point';

    public function runHooks()
    {
        $this->register();
        $this->registerMetaBoxes();
        $this->registerPrePostUpdate();
        $this->registerAfterInsert();
        $this->registerAfterDelete();
    }

    public function registerMetaBoxes()
    {
        $this->args['supports'] = ['title', 'editor', 'author', 'thumbnail'];
        $this->loader()->addFilter('rwmb_meta_boxes', $this, 'metaBoxes', 10, 1);
    }
    public function metaBoxes(array $meta_boxes)
    {
        $prefix = '';

        $meta_boxes[] = [
            'title'   => esc_html__( 'Picking Points group', 'online-generator' ),
            'id'      => 'picking-points-group',
            'post_types' => [$this->postType],
            'context' => 'normal',
            'fields'  => [
                [
                    'type' => 'select',
                    'name' => esc_html__( 'Provincia', 'online-generator' ),
                    'id'   => $prefix . 'provincia',
                    'options' => [
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
                    ],
                    'attributes' => [
                        'required' => 'required',
                    ],
                ],
                [
                    'type' => 'text',
                    'name' => esc_html__( 'Ciudad', 'online-generator' ),
                    'id'   => $prefix . 'ciudad',
                    'attributes' => [
                        'required' => 'required',
                    ],
                ],
                [
                    'type' => 'text',
                    'name' => esc_html__( 'Código postal', 'online-generator' ),
                    'id'   => $prefix . 'codigo_postal',
                    'attributes' => [
                        'required' => 'required',
                    ],
                ],
                [
                    'type' => 'text',
                    'name' => esc_html__( 'Dirección', 'online-generator' ),
                    'id'   => $prefix . 'direccion',
                    'attributes' => [
                        'required' => 'required',
                    ],
                ],
                [
                    'type' => 'text',
                    'name' => esc_html__( 'Teléfono', 'online-generator' ),
                    'id'   => $prefix . 'telefono',
                    'attributes' => [
                        'required' => 'required',
                    ],
                ],
                [
                    'type' => 'text',
                    'name' => esc_html__( 'Horarios de atención', 'online-generator' ),
                    'id'   => $prefix . 'horarios_de_atencion',
                ],
                [
                    'type' => 'email',
                    'name' => esc_html__( 'Email', 'online-generator' ),
                    'id'   => $prefix . 'email',
                    'attributes' => [
                        'required' => 'required',
                    ],
                ],
                [
                    'type' => 'number',
                    'name' => esc_html__( 'Latitud', 'online-generator' ),
                    'id'   => $prefix . 'latitud',
                ],
                [
                    'type' => 'number',
                    'name' => esc_html__( 'Longitud', 'online-generator' ),
                    'id'   => $prefix . 'longitud',
                ],
            ],
        ];

        return $meta_boxes;
    }

    public function registerAfterInsert()
    {
        $this->loader()->addAction('wp_after_insert_post', $this, 'afterInsertPost', 1, 2);
    }
    public function afterInsertPost($postId, $post)
    {
        add_filter( 'wp_mail_content_type','mail_set_content_type' );

        if($post->post_type !== $this->postType)
            return;

        $email = rwmb_meta(
            'email', [], $postId
        );

        $password = uniqid();
        $user_data = array(
            'user_pass' => $password,
            'user_login' => $email,
            'display_name' => $post->post_title,
            'role' => 'picking_point',
        );
        $userId = wp_insert_user( $user_data );
        update_user_meta($userId, 'picking_point_id', $postId);

        $subject = 'Se creó tu usuario en Livrika<br>';
        $message = 'Se ha creado tu usuario de tipo Punto de Retiro<br>';
        $message .= 'Podés ingresar a <a href="'.site_url().'" target="_blank">'.site_url().'</a> con los siguientes datos<br>';
        $message .= 'Usuario: '.$email.'<br>';
        $message .= 'Contraseña: '.$password.'<br>';
        $message .= 'Te recomendamos modificar tu contraseña la primera vez que ingreses al sistema por motivos de seguridad';

        wp_mail($email, $subject, $message,
            [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]
        );
    }

    public function registerPrePostUpdate()
    {
        $this->loader()->addAction('pre_post_update', $this, 'beforeInsertOrUpdatePost', 0, 2);
        $this->loader()->addAction('admin_notices', $this, 'myAdminNotices', 0);
    }
    public function beforeInsertOrUpdatePost($postId, $data)
    {
        $post = get_post($postId);

        if($post->post_type !== $this->postType)
            return;

        if(!$this->validatePostUpsert($postId, $data)) {

            if (!session_id())
                session_start();

            $_SESSION['my_admin_notices'] .= '<div class="error"><p>Los datos no son válidos</p></div>';
            header('Location: '.get_edit_post_link($postId, 'redirect'));
            exit;
        }

        $email = rwmb_meta(
            'email', [], $postId
        );
    }

    public function registerAfterDelete()
    {
        $this->loader()->addAction('after_delete_post', $this, 'afterDeletePost', 1, 2);
    }
    public function afterDeletePost($postId, $post)
    {
        if($post->post_type !== $this->postType)
            return;

        $email = rwmb_meta(
            'email', [], $postId
        );

        $user = get_user_by('login', $email);

        wp_delete_user($user->ID);
    }

    private function validatePostUpsert($postId, $post)
    {
        $email = rwmb_meta(
            'email', [], $postId
        );

        if(empty($email)) {
            $email = isset($_POST['email']) ? $_POST['email'] : '';
        }

        if(empty($email)) return false;

        $posts = new \WP_Query([
            'post_type' => $this->postType,
            'meta_key' => 'email',
            'meta_value' => $email,
            'meta_compare' => 'LIKE',
        ]);
        while ( $posts->have_posts() ) {
            $posts->the_post();
            if($postId != get_the_ID()) {
                return false;
            }
        }
        return true;
    }

    public function myAdminNotices()
    {
        if (!session_id())
            session_start();

        if(!empty($_SESSION['my_admin_notices'])) print  $_SESSION['my_admin_notices'];
        unset ($_SESSION['my_admin_notices']);
    }
}