<?php declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\LinkItemRenderer;
use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/**
 * Class ilMMLinkItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMLinkItemRenderer extends LinkItemRenderer
{
    use ilMMCloseOnClick;

    public function getComponentWithContent(isItem $item) : Component
    {
        return parent::getComponentWithContent($this->addDisengageDecorator($item));
    }
}
