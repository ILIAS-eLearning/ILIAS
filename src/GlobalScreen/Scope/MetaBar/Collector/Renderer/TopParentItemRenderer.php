<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopParentItem;
use ILIAS\UI\Component\Component;

/**
 * Class TopParentItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopParentItemRenderer extends AbstractMetaBarItemRenderer
{

    /**
     * @inheritDoc
     */
    protected function getSpecificComponentForItem(isItem $item) : Component
    {
        /**
         * @var $item TopParentItem
         */
        $component = $this->ui->factory()->mainControls()->slate()->combined($item->getTitle(), $item->getSymbol());
        foreach ($item->getChildren() as $child) {
            /**
             * @var $child isItem
             */
            $component_for_item = $child->getRenderer()->getComponentForItem($child);
            if ($this->isComponentSupportedForCombinedSlate($component_for_item)) {
                $component = $component->withAdditionalEntry($component_for_item);
            }
        }

        return $component;
    }
}
