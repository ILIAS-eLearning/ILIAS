<?php namespace ILIAS\GlobalScreen\Scope\Layout;

use ILIAS\GlobalScreen\Scope\Layout\Factory\ModificationFactory;
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
    public function __construct(string $resource_version)
    {
        $this->meta_content = new MetaContent($resource_version);
    }


    /**
     * @return ModificationFactory
     */
    public function factory() : ModificationFactory
    {
        return $this->get(ModificationFactory::class);
    }


    /**
     * @return MetaContent
     */
    public function meta() : MetaContent
    {
        return $this->meta_content;
    }
}
