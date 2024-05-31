<?php

namespace Adue\WordPressBasePlugin\Base;

abstract class Singleton
{

    protected static $instances;
    protected $attributes;

    protected function __construct() { }

    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function instance()
    {
        static $instances = array();

        $calledClass = get_called_class();

        if (!isset(self::$instances[$calledClass])) {
            self::$instances[$calledClass] = new $calledClass();
            self::$instances[$calledClass]->setUp();
        }
        return self::$instances[$calledClass];
    }

    protected function setUp() {}

}