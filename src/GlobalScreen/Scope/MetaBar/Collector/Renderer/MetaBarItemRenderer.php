<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Interface MetaBarItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface MetaBarItemRenderer
{

    /**
     * @param isItem $item
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item) : Component;
}
