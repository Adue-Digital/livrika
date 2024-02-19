<?php

namespace Adue\WordPressPlugin;

use Adue\WordPressBasePlugin\Base\Loader;
use Adue\WordPressBasePlugin\BasePlugin;
use Adue\WordPressPlugin\Admin\CustomMenuPage;
use DI\DependencyException;
use DI\NotFoundException;
use function DI\create;

class Plugin extends BasePlugin
{

    protected string $configFilePath = __DIR__.'/../config/config.php';

    public function init()
    {
        $menuPages = $this->getContainer()->get(
            CustomMenuPage::class,
        );
        $menuPages->add();
    }

    public function run()
    {
        $loader = $this->getContainer()->get(Loader::class);
        $loader->run();
    }

}