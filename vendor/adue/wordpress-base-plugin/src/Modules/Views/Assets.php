<?php

namespace Adue\WordPressBasePlugin\Modules\Views;

class Assets
{

    public function enqueueStyles($name, $path, $dependencies = [], $version = '1.0.0', $media = 'all')
    {
        wp_enqueue_style( $name, $path, $dependencies, $version, $media );
    }

    public function enqueueScripts($name, $path, $dependencies = ['jquery'], $version = '1.0.0', $inFooter = false)
    {
        wp_enqueue_script( $name, $path, $dependencies, $version, $inFooter );
    }

    public function localizeScript($name, $varName, $values = [])
    {
        wp_localize_script( $name, $varName, $values);
    }

}