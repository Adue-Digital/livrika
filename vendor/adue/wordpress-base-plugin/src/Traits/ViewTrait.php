<?php

namespace Adue\WordPressBasePlugin\Traits;

use Adue\WordPressBasePlugin\Helpers\View;
use DI\Attribute\Inject;

trait ViewTrait
{

    #[Inject]
    private View $view;

    public function view()
    {
        return $this->view;
    }

}