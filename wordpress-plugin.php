<?php
/*
Plugin Name:  WordPress Plugin
Description:  A small plugin that use WordPress Base Plugin package.
Version:      0.0.1
Author: Marcio Fuentes
Author URI: https://adue.digital
 */

use Adue\WordPressPlugin\Plugin;
use DI\ContainerBuilder;
use Noodlehaus\Config;

require 'vendor/autoload.php';

class WordPressPlugin
{

    public $plugin;

    public static function instance(): self
    {
        static $instance;
        if (! $instance) {
            $instance = new self();

            $containerBuilder = new ContainerBuilder();
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

class_exists(WordPressPlugin::class) && WordPressPlugin::instance();