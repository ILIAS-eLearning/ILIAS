<?php

namespace ILIAS\GlobalScreen\Scope\Tool\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Tool\Factory\TreeTool;
use ILIAS\UI\Component\Component;

/**
 * Class TreeToolItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TreeToolItemRenderer extends BaseTypeRenderer
{

    /**
     * @param isItem $item
     *
     * @param bool   $with_async_content
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item, bool $with_async_content = false) : Component
    {
        global $DIC;
        /**
         * @var $item TreeTool
         */

        $symbol = $this->getStandardSymbol($item);

        return $this->ui_factory->mainControls()->slate()->legacy($item->getTitle(), $symbol, $this->ui_factory->legacy($DIC->ui()->renderer()->render([$item->getTree()])));
    }
}
