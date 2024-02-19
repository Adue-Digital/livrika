<?php

namespace Adue\WordPressPlugin;

use Adue\WordPressBasePlugin\Base\Loader;
use Adue\WordPressBasePlugin\BasePlugin;

class Plugin extends BasePlugin
{

    public function init()
    {}

    public function run()
    {
        $loader = $this->getContainer()->get(Loader::class);
        $loader->run();
    }

}