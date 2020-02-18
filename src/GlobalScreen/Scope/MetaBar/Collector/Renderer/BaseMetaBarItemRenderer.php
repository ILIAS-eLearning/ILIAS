<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class BaseMetaBarItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BaseMetaBarItemRenderer extends AbstractMetaBarItemRenderer implements MetaBarItemRenderer
{

    /**
     * @param isItem $item
     *
     * @return Component
     */
    protected function getSpecificComponentForItem(isItem $item) : Component
    {
        return $this->ui->factory()->legacy("no renderer found");
    }
}
