<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Complex;
use ILIAS\UI\Component\Component;

/**
 * Class ComplexItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ComplexItemRenderer extends BaseTypeRenderer
{
    use MakeSlateAsync, SlateSessionStateCode {
        MakeSlateAsync::hash insteadof SlateSessionStateCode;
        MakeSlateAsync::unhash insteadof SlateSessionStateCode;
    }


    /**
     * @inheritDoc
     */
    public function getComponentWithContent(isItem $item) : Component
    {
        /**
         * @var $item Complex
         */
        global $DIC;
        $content = $this->ui_factory->legacy($DIC->ui()->renderer()->render($item->getContent()));

        return $this->ui_factory->mainControls()->slate()->legacy($item->getTitle(), $this->getStandardSymbol($item), $content);
    }
}
