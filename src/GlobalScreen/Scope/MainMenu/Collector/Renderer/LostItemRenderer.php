<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class LostItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LostItemRenderer extends BaseTypeRenderer
{

    /**
     * @inheritDoc
     */
    public function getComponentWithContent(isItem $item) : Component
    {
        /**
         * @var $item \ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost
         */
        if ($item->hasChildren()) {
            $r = new TopParentItemRenderer();

            return $r->getComponentForItem($item, true);
        }

        return $this->ui_factory->button()->bulky($this->getStandardSymbol($item), "{$item->getTypeInformation()->getTypeNameForPresentation()}", "");
    }
}
