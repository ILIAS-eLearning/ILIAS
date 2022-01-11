<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\UI\Component\Component;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class BaseMetaBarItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BaseMetaBarItemRenderer extends AbstractMetaBarItemRenderer implements MetaBarItemRenderer
{
    
    /**
     * @param isItem $item
     * @return Component
     */
    protected function getSpecificComponentForItem(isItem $item) : Component
    {
        return $this->ui->factory()->legacy("no renderer found");
    }
}
