<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;

/**
 * Class LinkListItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LinkListItemRenderer extends BaseTypeRenderer
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
        $slate = $this->ui_factory->mainControls()->slate()->combined($item->getTitle(), $this->getStandardSymbol($item));
        /**
         * @var $link Link
         */
        foreach ($item->getLinks() as $link) {
            if (!$link->isVisible()) {
                continue;
            }
            $link = $this->ui_factory->link()->bulky($this->getStandardSymbol($link), $link->getTitle(), $this->getURI($link->getAction()));
            $slate = $slate->withAdditionalEntry($link);
        }

        return $slate;
    }
}
