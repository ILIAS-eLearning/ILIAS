<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopLinkItem;
use ILIAS\UI\Component\Component;

/**
 * Class TopLinkItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopLinkItemRenderer extends BaseTypeRenderer
{

    /**
     * @inheritDoc
     */
    const BLANK = "_blank";
    const TOP = "_top";


    /**
     * @param TopLinkItem $item
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item) : Component
    {
        return $this->ui_factory->button()->bulky($this->getStandardSymbol($item), $item->getTitle(), $item->getAction());
    }
}
