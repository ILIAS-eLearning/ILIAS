<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopLinkItem;
use ILIAS\UI\Component\Component;

/**
 * Class TopLinkItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopLinkItemRenderer extends AbstractMetaBarItemRenderer
{

    /**
     * @inheritDoc
     */
    protected function getSpecificComponentForItem(isItem $item) : Component
    {
        /**
         * @var $item TopLinkItem
         */
        return $this->ui->factory()->link()->bulky(
            $this->getStandardSymbol($item),
            $item->getTitle(),
            $this->getURI($item->getAction())
        );
    }
}
