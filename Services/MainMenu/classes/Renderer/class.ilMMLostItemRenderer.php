<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class ilMMLostItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMLostItemRenderer extends BaseTypeRenderer
{

    /**
     * @param isItem $item
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item) : Component
    {
        /**
         * @var $item \ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost
         */
        if ($item->hasChildren()) {
            $r = new ilMMTopParentItemRenderer();

            return $r->getComponentForItem($item);
        }

        return $this->ui_factory->legacy("{$item->getTypeInformation()->getTypeNameForPresentation()}");
    }
}
