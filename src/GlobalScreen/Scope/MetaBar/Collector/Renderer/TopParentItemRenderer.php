<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopParentItem;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MainControls\Slate\Slate;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class TopParentItemRenderer
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
             * @var $component_for_item Slate
             */
            $component_for_item = $child->getRenderer()->getComponentForItem($child);
            if ($this->isComponentSupportedForCombinedSlate($component_for_item)) {
                $component = $component->withAdditionalEntry($component_for_item);
            }
        }
        
        return $component;
    }
}
