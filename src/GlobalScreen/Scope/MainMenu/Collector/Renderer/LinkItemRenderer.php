<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\UI\Component\Component;

/**
 * Class LinkItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LinkItemRenderer extends BaseTypeRenderer
{

    /**
     * @param Link $item
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item) : Component
    {
        $uri_string = $item->getAction();

        return $this->ui_factory->link()->bulky($this->getStandardSymbol($item), $item->getTitle(), $this->getURI($uri_string));
    }
}
