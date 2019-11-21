<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\LinkList;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;
use ILIAS\UI\Component\Component;

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
     * @param isItem $item
     *
     * @param bool   $with_async_content
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item, bool $with_async_content = false) : Component
    {
        /**
         * @var $item LinkList
         */
        $slate = $this->ui_factory->mainControls()->slate()->combined($item->getTitle(), $this->getStandardSymbol($item));
        if ($with_async_content === false && $item instanceof supportsAsynchronousLoading && $item->supportsAsynchronousLoading()) {
            $slate = $this->addAsyncLoadingCode($slate, $item);
            $slate = $this->addOnloadCode($slate, $item);
        } else {
            foreach ($item->getLinks() as $link) {
                $link = $this->ui_factory->link()->bulky($this->getStandardSymbol($link), $link->getTitle(), $this->getURI($link->getAction()));
                $slate = $slate->withAdditionalEntry($link);
            }
        }

        return $slate;
    }
}
