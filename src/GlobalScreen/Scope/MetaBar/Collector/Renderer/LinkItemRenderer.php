<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\LinkItem;
use ILIAS\UI\Component\Component;

/**
 * Class LinkItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LinkItemRenderer extends AbstractMetaBarItemRenderer
{

    /**
     * @inheritDoc
     */
    protected function getSpecificComponentForItem(isItem $item) : Component
    {
        /**
         * @var $item LinkItem
         */
        return $this->ui->factory()->link()->bulky(
            $this->getStandardSymbol($item),
            $item->getTitle(),
            $this->getURI($item->getAction()));
    }
}
