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
    public function getComponentForItem(isItem $item) : Component
    {
        /**
         * @var $item LinkItem
         */
        return $this->ui->factory()->button()->bulky(
            $this->getStandardSymbol($item),
            $item->getTitle(),
            $item->getAction());
    }
}
