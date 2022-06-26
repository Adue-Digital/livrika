<?php

namespace Adue\WordPressPlugin;

use Adue\WordPressBasePlugin\BasePlugin;
use Adue\WordPressPlugin\Admin\CustomOption;
use Adue\WordPressPlugin\Admin\CustomSubmenuPage;
use Adue\WordPressPlugin\PostTypes\BookPostType;
use Adue\WordPressPlugin\Admin\CustomMenuPage;

class Plugin extends BasePlugin
{

    protected string $configFilePath = __DIR__.'/../config/config.php';

    public function init()
    {
        //Make some awesome
    }

}