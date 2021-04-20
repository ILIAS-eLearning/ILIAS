<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class TopParentItemRenderer
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
    public function getComponentWithContent(isItem $item) : Component
    {
        $f = $this->ui_factory;
        $slate = $f->mainControls()->slate()->combined($item->getTitle(), $this->getStandardSymbol($item));
        /**
         * @var $child isItem
         */
        foreach ($item->getChildren() as $child) {
            $component = $child->getTypeInformation()->getRenderer()->getComponentForItem($child, false);
            if ($this->isComponentSupportedForCombinedSlate($component)) {
                $slate = $slate->withAdditionalEntry($component, $this->hash($child->getProviderIdentification()->serialize()));
            }
        }

        return $slate;
    }
}
