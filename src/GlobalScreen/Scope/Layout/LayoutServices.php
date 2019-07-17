<?php namespace ILIAS\GlobalScreen\Scope\Layout;

use ILIAS\GlobalScreen\Scope\Layout\Content\LayoutContent;
use ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\MetaContent;
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
}
