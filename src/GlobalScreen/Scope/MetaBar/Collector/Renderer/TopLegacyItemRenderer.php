<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopLegacyItem;
use ILIAS\UI\Component\Component;

/**
 * Class TopLegacyItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopLegacyItemRenderer extends AbstractMetaBarItemRenderer
{

    /**
     * @inheritDoc
     */
    public function getComponentForItem(isItem $item) : Component
    {
        /**
         * @var $item TopLegacyItem
         */

        return $this->ui->factory()->mainControls()->slate()->legacy(
            $item->getTitle(),
            $item->getSymbol(),
            $item->getLegacyContent()
        );
    }
}
