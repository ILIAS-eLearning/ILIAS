<?php

namespace ILIAS\Changelog\Query;

trait SingletonTrait
{

    /**
     * @var self
     */
    protected static $instance;


    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}