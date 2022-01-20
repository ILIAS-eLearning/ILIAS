<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\ComponentDecoratorApplierTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
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
 * Class RepositoryLinkItemRenderer
 */
class RepositoryLinkItemRenderer extends BaseTypeRenderer
{
    use ComponentDecoratorApplierTrait;
    
    /**
     * @inheritDoc
     */
    public function getComponentWithContent(isItem $item) : Component
    {
        if ($item instanceof isInterchangeableItem && !$item->getParent()) {
            return $this->ui_factory->link()->bulky($this->getStandardSymbol($item), $item->getTitle(), $this->getURI($item->getAction()));
        } else {
            return $this->ui_factory->link()->bulky($this->getStandardSymbol($item), $item->getTitle(), $this->getURI($item->getAction()));
        }
    }
}
