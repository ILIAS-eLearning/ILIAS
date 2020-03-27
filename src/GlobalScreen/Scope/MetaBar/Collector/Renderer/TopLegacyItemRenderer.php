<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopLegacyItem;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MainControls\Slate\Slate;

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
    protected function getSpecificComponentForItem(isItem $item) : Component
    {
        /**
         * @var $item TopLegacyItem
         */

        return $this->ui->factory()->mainControls()->slate()->legacy(
            $item->getTitle(),
            $item->getSymbol(),
            $item->getLegacyContent()
        )->withAriaRole(Slate::MENU);
    }
}
