<?php

namespace ILIAS\GlobalScreen\Scope\Tool\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\SlateSessionStateCode;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
use ILIAS\UI\Component\Component;

/**
 * Class LinkItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ToolItemRenderer extends BaseTypeRenderer
{

    use SlateSessionStateCode;


    /**
     * @param Link $item
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item) : Component
    {
        /**
         * @var $item Tool
         */

        $symbol = $this->getStandardSymbol($item);

        $slate = $this->ui_factory->mainControls()->slate()->legacy($item->getTitle(), $symbol, $item->getContent());

        $slate = $this->addOnloadCode($slate, $item);

        return $slate;
    }
}
