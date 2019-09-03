<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\Data\URI;
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
                $component = $f->link()->bulky($item->getSymbol(), $item->getTitle(), $this->getURI($item->getAction()));
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


    /**
     * @param string $uri_string
     *
     * @return URI
     */
    protected function getURI(string $uri_string) : URI
    {
        if (strpos($uri_string, 'http') === 0) {
            return new URI($uri_string);
        }

        return new URI(rtrim(ILIAS_HTTP_PATH, "/") . "/" . ltrim($uri_string, "./"));
    }
}
