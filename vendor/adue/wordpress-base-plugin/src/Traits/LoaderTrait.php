<?php

namespace Adue\WordPressBasePlugin\Traits;

use Adue\WordPressBasePlugin\Base\Loader;
use DI\Attribute\Inject;

trait LoaderTrait
{

    #[Inject]
    private Loader $loader;

    public function loader()
    {
        return $this->loader;
    }

}