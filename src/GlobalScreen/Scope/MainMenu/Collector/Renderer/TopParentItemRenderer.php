<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\UI\Component\Component;

/**
 * Class TopParentItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopParentItemRenderer extends BaseTypeRenderer
{

    use MakeSlateAsync, SlateSessionStateCode {
        MakeSlateAsync::hash insteadof SlateSessionStateCode;
        MakeSlateAsync::unhash insteadof SlateSessionStateCode;
    }
    use isSupportedTrait;


    /**
     * @inheritDoc
     */
    public function getComponentForItem(isItem $item, bool $with_async_content = false) : Component
    {
        $f = $this->ui_factory;

        /**
         * @var $item TopParentItem
         */

        if ($with_async_content === false && $item instanceof supportsAsynchronousLoading && $item->supportsAsynchronousLoading()) {
            $content = $this->ui_factory->legacy("...");
            $slate = $this->ui_factory->mainControls()->slate()->legacy($item->getTitle(), $this->getStandardSymbol($item), $content);
            $slate = $this->addAsyncLoadingCode($slate, $item);
            $slate = $this->addOnloadCode($slate, $item);
        } else {
            $slate = $f->mainControls()->slate()->combined($item->getTitle(), $this->getStandardSymbol($item));

            foreach ($item->getChildren() as $child) {
                $component = $child->getTypeInformation()->getRenderer()->getComponentForItem($child, $with_async_content);
                if ($this->isComponentSupportedForCombinedSlate($component)) {
                    $slate = $slate->withAdditionalEntry($component);
                }
            }
        }

        return $slate;
    }
}
