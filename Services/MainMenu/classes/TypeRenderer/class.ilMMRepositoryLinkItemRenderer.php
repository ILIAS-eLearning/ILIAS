<?php

declare(strict_types=1);

use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\TopLinkItemRenderer;

/**
 * Class ilMMRepositoryLinkItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMRepositoryLinkItemRenderer extends TopLinkItemRenderer
{
    use ilMMCloseOnClick;

    public function getComponentWithContent(isItem $item): Component
    {
        return parent::getComponentWithContent($this->addDisengageDecorator($item));
    }
}
