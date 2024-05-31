<?php

namespace Adue\WordPressBasePlugin;

use Adue\WordPressBasePlugin\Base\Activator;
use Adue\WordPressBasePlugin\Base\Config;
use Adue\WordPressBasePlugin\Base\Deactivator;
use Adue\WordPressBasePlugin\Helpers\Traits\UseLoader;
use DI\Container;


class BasePlugin
{

    protected array $dependencies = [];
    protected Activator $activator;
    protected Deactivator $deactivator;

    public function __construct(public Container $container)
    {
        $this->loadDependences();
    }

    private function loadDependences()
    {

        $this->activator = new Activator();
        $this->deactivator = new Deactivator();
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function register()
    {

    }

    public function init() {}

    public function run()
    {
        $this->loader()->run();
    }

    public function registerMainHooks($path)
    {
        register_activation_hook(
            $path,
            [$this, 'activate']
        );

        register_deactivation_hook(
            $path,
            [$this, 'deactivate']
        );

        register_uninstall_hook(
            $path,
            [$this, 'uninstall']
        );
    }

    public function activate()
    {}

    public function deactivate()
    {}

    public function uninstall()
    {}

}