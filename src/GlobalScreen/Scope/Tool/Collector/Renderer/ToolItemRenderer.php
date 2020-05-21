<?php

namespace ILIAS\GlobalScreen\Scope\Tool\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\ComponentDecoratorApplierTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\SlateSessionStateCode;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\SymbolDecoratorTrait;
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
    use ComponentDecoratorApplierTrait;
    use SymbolDecoratorTrait;

    /**
     * @param isItem $item
     *
     * @param bool   $with_content
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item, bool $with_content = false) : Component
    {
        /**
         * @var $item Tool
         */

        $symbol = $this->getStandardSymbol($item);

        $slate = $this->ui_factory->mainControls()->slate()->legacy($item->getTitle(), $symbol, $item->getContent());

        $slate = $this->addOnloadCode($slate, $item);
        $slate = $this->applyDecorator($slate, $item);

        return $slate;
    }
}
