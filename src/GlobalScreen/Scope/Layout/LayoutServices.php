<?php namespace ILIAS\GlobalScreen\Scope\Layout;

use ILIAS\GlobalScreen\Scope\Layout\Factory\ModifierFactory;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\SingletonTrait;

/**
 * Class LayoutServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LayoutServices
{

    use SingletonTrait;
    /**
     * @var MetaContent
     */
    private $meta_content;


    /**
     * LayoutServices constructor.
     */
    public function __construct()
    {
        $this->meta_content = new MetaContent();
    }


    /**
     * @return ModifierFactory
     */
    public function factory() : ModifierFactory
    {
        return $this->get(ModifierFactory::class);
    }


    /**
     * @return MetaContent
     */
    public function meta() : MetaContent
    {
        return $this->meta_content;
    }
}
