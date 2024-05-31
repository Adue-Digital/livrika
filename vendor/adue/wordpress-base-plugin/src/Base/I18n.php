<?php

namespace Adue\WordPressBasePlugin\Base;

class I18n
{
    private string $domain;

    public function __construct(string $domain = '')
    {
        $this->domain = $domain;
        $this->loadPluginTextdomain();
    }

    public function loadPluginTextdomain()
    {
        load_plugin_textdomain(
            $this->domain,
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
    }

}