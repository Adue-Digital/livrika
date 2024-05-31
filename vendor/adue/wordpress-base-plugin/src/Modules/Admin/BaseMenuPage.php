<?php

namespace Adue\WordPressBasePlugin\Modules\Admin;

use Adue\WordPressBasePlugin\Base\Loader;
use Adue\WordPressBasePlugin\Helpers\Traits\UseLoader;
use Adue\WordPressBasePlugin\Helpers\Traits\UseView;
use Adue\WordPressBasePlugin\Helpers\View;
use Adue\WordPressBasePlugin\Traits\LoaderTrait;
use Adue\WordPressBasePlugin\Traits\ViewTrait;

class BaseMenuPage
{

    use LoaderTrait, ViewTrait;

    protected string $pageTitle = '';
    protected string $menuTitle = '';
    protected string $capability = '';
    protected string $menuSlug = '';
    protected string $iconUrl = '';
    protected int $position = 0;

    protected array $submenuItems = [];

    public function add()
    {
        $this->loader()->addAction('init', $this, 'addMenuPage');
    }

    public function addSubmenus()
    {
        $this->loader()->addAction('init', $this, 'addSubmenusPages');
    }

    public function addMenuPage()
    {
        add_menu_page(
            $this->pageTitle,
            $this->menuTitle,
            $this->capability,
            $this->menuSlug,
            [$this, 'render'],
            $this->iconUrl,
            $this->position,
        );
    }

    public function setSubpage(BaseMenuPage $subpage)
    {
        $this->submenuItems[] = $subpage;
    }

    public function addSubmenusPages()
    {
        foreach ($this->submenuItems as $submenuItem)
            add_submenu_page(
                $this->menuSlug,
                $submenuItem->pageTitle,
                $submenuItem->menuTitle,
                $submenuItem->capability,
                $submenuItem->menuSlug,
                [$submenuItem, 'render'],
                $submenuItem->position,
            );
    }

    public function render()
    {
        //Render page content
    }

}