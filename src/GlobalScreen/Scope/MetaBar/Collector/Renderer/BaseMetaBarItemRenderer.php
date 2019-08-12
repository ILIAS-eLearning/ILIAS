<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\LinkItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopLegacyItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopLinkItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopParentItem;
use ILIAS\UI\Component\Button\Bulky;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MainControls\Slate\Slate;

/**
 * Class BaseMetaBarItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class  BaseMetaBarItemRenderer implements MetaBarItemRenderer
{

    private $ui;


    /**
     * BaseMetaBarItemRenderer constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
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
                    if ($component_for_item instanceof Slate || $component_for_item instanceof Bulky) {
                        $component = $component->withAdditionalEntry($component_for_item);
                    }
                }
                break;
        }

        return $component;
    }
}
