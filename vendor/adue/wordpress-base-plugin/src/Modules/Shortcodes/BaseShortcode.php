<?php

namespace Adue\WordPressBasePlugin\Modules\Shortcodes;

class BaseShortcode
{

    protected $signature = 'base-shortcode';
    protected $args = [];

    public function add()
    {
        add_shortcode($this->signature, [$this, 'run']);
    }

    public function run($args)
    {
        // ...
    }

}