<?php
/*
Plugin Name:  Livrika Puntos de Retiro
Description:  Provee la funcionalidad de puntos de retiro para Dokan
Version:      0.0.1
Author: Marcio Fuentes
Author URI: https://adue.digital
 */

use Adue\LivrikaPickingPoints\Plugin;
use DI\ContainerBuilder;
use Noodlehaus\Config;

require 'vendor/autoload.php';

class PickingPoints
{

    public $plugin;

    public static function instance(): self
    {
        static $instance;
        if (! $instance) {
            $instance = new self();

            $containerBuilder = new ContainerBuilder();
            $containerBuilder->useAttributes(true);
            $containerBuilder->addDefinitions(__DIR__.'/config/di_definitions.php');

            $container = $containerBuilder->build();

            $instance->plugin = new Plugin($container);
            $instance->run();
        }
        return $instance;
    }

    private function run()
    {
        $this->plugin->init();
        $this->plugin->run();
    }

}

class_exists(PickingPoints::class) && PickingPoints::instance();