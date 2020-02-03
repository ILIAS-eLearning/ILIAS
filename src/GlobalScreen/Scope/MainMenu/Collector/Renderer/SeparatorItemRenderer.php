<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class SeparatorItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SeparatorItemRenderer extends BaseTypeRenderer
{

    /**
     * @inheritDoc
     */
    public function getComponentWithContent(isItem $item) : Component
    {
        $horizontal = $this->ui_factory->divider()->horizontal();
        if ($item->getTitle()) {
            $horizontal = $horizontal->withLabel($item->getTitle());
        }
        return $horizontal;
    }
}
