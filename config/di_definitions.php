<?php

use Adue\WordPressPlugin\Admin\Test1Page;
use Psr\Container\ContainerInterface;

require __DIR__.'/../vendor/autoload.php';

return [
    'plugin_name' => 'Livrika Picking Points',
    'plugin_version' => '0.0.1',
    'base_view_path' => __DIR__.'/../resources/views',

    \Adue\WordPressBasePlugin\Base\Loader::class => fn() => new \Adue\WordPressBasePlugin\Base\Loader,
    \Adue\WordPressBasePlugin\Helpers\View::class =>  function (ContainerInterface $c) {
        return new \Adue\WordPressBasePlugin\Helpers\View($c->get('base_view_path'));
    },

];