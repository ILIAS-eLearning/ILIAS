<?php namespace ILIAS\GlobalScreen\Scope\Layout;

use ILIAS\GlobalScreen\Scope\Layout\Content\LayoutContent;
use ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\MetaContent;

/**
 * Class LayoutServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LayoutServices
{

    /**
     * @var array
     */
    private static $services = [];


    /**
     * @return LayoutContent
     */
    public function content() : LayoutContent
    {
        return $this->get(LayoutContent::class);
    }


    /**
     * @return MetaContent
     */
    public function meta() : MetaContent
    {
        return $this->get(MetaContent::class);
    }


    /**
     * @param string $class_name
     *
     * @return mixed
     */
    private function get(string $class_name)
    {
        if (!isset(self::$services[$class_name])) {
            self::$services[$class_name] = new $class_name();
        }

        return self::$services[$class_name];
    }
}
