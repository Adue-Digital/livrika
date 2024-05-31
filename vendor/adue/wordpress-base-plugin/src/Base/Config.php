<?php

namespace Adue\WordPressBasePlugin\Base;

class Config extends Singleton
{

    protected $config;

    public static function setConfig($configFilePath)
    {
        if(!file_exists($configFilePath))
            return false;
        $instance = self::instance();
        $instance->config = new \Noodlehaus\Config($configFilePath);
    }

    public function __call(string $name, array $arguments)
    {
        $instance = self::instance();
        return $instance->config->$name(...$arguments);
    }

}