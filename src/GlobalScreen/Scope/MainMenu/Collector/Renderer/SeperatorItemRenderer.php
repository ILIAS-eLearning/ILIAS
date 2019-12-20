<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class LinkItemRenderer
 */
class SeperatorItemRenderer extends BaseTypeRenderer
{

    /**
     * @inheritDoc
     */
    public function getComponentWithContent(isItem $item) : Component
    {
        $title = $item->isTitleVisible() ? $item->getTitle():"";
        return $this->ui_factory->button()
                                ->bulky($this->getStandardSymbol($item), $title, "#")->withUnavailableAction(true);
    }
}
