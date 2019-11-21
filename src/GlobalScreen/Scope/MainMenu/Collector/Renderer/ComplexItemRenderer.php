<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Complex;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;
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
    public function getComponentForItem(isItem $item, bool $with_async_content = false) : Component
    {
        /**
         * @var $item Complex
         */
        global $DIC;

        if ($with_async_content === false && $item instanceof supportsAsynchronousLoading && $item->supportsAsynchronousLoading()) {
            $content = $this->ui_factory->legacy("...");
            $slate = $this->ui_factory->mainControls()->slate()->legacy($item->getTitle(), $this->getStandardSymbol($item), $content);
            $slate = $this->addAsyncLoadingCode($slate, $item);
            $slate = $this->addOnloadCode($slate, $item);
        } else {
            $content = $this->ui_factory->legacy($DIC->ui()->renderer()->render($item->getContent()));
            $slate = $this->ui_factory->mainControls()->slate()->legacy($item->getTitle(), $this->getStandardSymbol($item), $content);
        }

        return $slate;
    }
}
