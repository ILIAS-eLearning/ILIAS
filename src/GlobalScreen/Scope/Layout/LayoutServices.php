<?php namespace ILIAS\GlobalScreen\Scope\Layout;

use ILIAS\GlobalScreen\Scope\Layout\Content\LayoutContent;
use ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\MetaContent;
use ILIAS\GlobalScreen\Scope\Layout\Definition\LayoutDefinitionFactory;
use ILIAS\UI\Component\Layout\Builder\PageBuilder;
use ILIAS\UI\Component\Layout\Builder\StandardPageBuilder;

/**
 * Class LayoutServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LayoutServices
{

    /**
     * @var PageBuilder
     */
    private $current_page_builder;
    /**
     * @var MetaContent
     */
    private $meta_content;


    /**
     * LayoutServices constructor.
     */
    public function __construct()
    {
        $this->current_page_builder = new StandardPageBuilder();
        $this->meta_content = new MetaContent();
    }


    /**
     * @param PageBuilder $builder
     */
    public function setPageBuilder(PageBuilder $builder)
    {
        $this->current_page_builder = $builder;
    }


    /**
     * @return PageBuilder
     */
    public function builder() : PageBuilder
    {
        return $this->current_page_builder;
    }


    /**
     * @return MetaContent
     */
    public function metaContent() : MetaContent
    {
        return $this->meta_content;
    }






    //
    // Deprecated
    //

    /**
     * @var array
     */
    private static $services = [];


    /**
     * @return LayoutDefinitionFactory
     */
    public function definition() : LayoutDefinitionFactory
    {
        return $this->get(LayoutDefinitionFactory::class);
    }


    /**
     * @return LayoutContent
     */
    public function content() : LayoutContent
    {
        return $this->get(LayoutContent::class);
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
