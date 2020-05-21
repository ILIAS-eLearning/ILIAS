<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class LinkItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LinkItemRenderer extends BaseTypeRenderer
{

    /**
     * @inheritDoc
     */
    public function getComponentWithContent(isItem $item) : Component
    {
        return $this->ui_factory->link()->bulky($this->getStandardSymbol($item), $item->getTitle(), $this->getURI($item->getAction()))->withOpenInNewViewport($item->isLinkWithExternalAction());
    }
}
