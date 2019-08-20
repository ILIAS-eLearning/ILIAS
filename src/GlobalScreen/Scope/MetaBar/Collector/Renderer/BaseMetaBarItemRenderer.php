<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\LinkItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopLegacyItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopLinkItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopParentItem;
use ILIAS\UI\Component\Component;

/**
 * Class BaseMetaBarItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BaseMetaBarItemRenderer implements MetaBarItemRenderer
{

    use isSupportedTrait;
    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    private $ui;
    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    private $gs;


    /**
     * BaseMetaBarItemRenderer constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->gs = $DIC->globalScreen();
    }


    /**
     * @param isItem $item
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item) : Component
    {
        $f = $this->ui->factory();

        $component = $f->legacy("");

        switch (true) {
            case ($item instanceof LinkItem):
            case ($item instanceof TopLinkItem):
                $component = $f->button()->bulky($item->getSymbol(), $item->getTitle(), $item->getAction());
                break;
            case ($item instanceof TopLegacyItem):
                $component = $f->mainControls()->slate()->legacy($item->getTitle(), $item->getSymbol(), $item->getLegacyContent());
                break;
            case ($item instanceof TopParentItem):
                $component = $f->mainControls()->slate()->combined($item->getTitle(), $item->getSymbol());
                foreach ($item->getChildren() as $child) {
                    /**
                     * @var $child isItem
                     */
                    $component_for_item = $child->getRenderer()->getComponentForItem($child);
                    if ($this->isComponentSupportedForCombinedSlate($component_for_item)) {
                        $component = $component->withAdditionalEntry($component_for_item);
                    }
                }
                break;
        }

        return $component;
    }
}
