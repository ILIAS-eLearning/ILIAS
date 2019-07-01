<?php namespace ILIAS\GlobalScreen\Scope\Layout\Content;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\LinkItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopLegacyItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopParentItem;
use ILIAS\Tools\Maintainers\Component;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\Slate\Combined;

/**
 * Class LayoutContent
 *
 * @package ILIAS\GlobalScreen\Scope\Layout\Content
 */
class LayoutContent
{

    /**
     * @var Component
     */
    private $content;
    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    private $gs;
    /**
     * @var \ILIAS\DI\UIServices
     */
    private $ui;


    /**
     * @inheritDoc
     */
    public function __construct()
    {
        static $initialised;
        if ($initialised !== null) {
            throw new \LogicException("only one instance of a LayoutContent can exist");
        }
        global $DIC;
        $this->ui = $DIC->ui();
        $this->gs = $DIC->globalScreen();
    }


    /**
     * @inheritDoc
     */
    protected function getBreadCrumbs() : Breadcrumbs
    {
        // TODO this currently gets the items from ilLocatorGUI, should that serve be removed with
        // something like GlobalScreen\Scope\Locator\Item
        global $DIC;

        $f = $this->ui->factory();
        $crumbs = [];
        foreach ($DIC['ilLocator']->getItems() as $item) {
            $crumbs[] = $f->link()->standard($item['title'], $item["link"]);
        }

        return $this->ui->factory()->breadcrumbs($crumbs);
    }


    /**
     * @inheritDoc
     */
    protected function getMetaBar() : MetaBar
    {
        $f = $this->ui->factory();
        $meta_bar = $f->mainControls()->metaBar();

        foreach ($this->gs->collector()->metaBar()->getStackedItems() as $item) {
            switch (true) {
                case ($item instanceof TopLegacyItem):
                    $slate = $f->mainControls()->slate()->legacy($item->getTitle(), $item->getGlyph(), $item->getLegacyContent());
                    break;
                case ($item instanceof TopParentItem):
                    $slate = $f->mainControls()->slate()->combined($item->getTitle(), $item->getGlyph());
                    foreach ($item->getChildren() as $child) {
                        switch (true) {
                            case ($child instanceof LinkItem):
                                $b = $f->button()->bulky($child->getGlyph(), $child->getTitle(), $child->getAction());
                                $slate = $slate->withAdditionalEntry($b);
                                break;
                        }
                    }
                    break;
            }

            $meta_bar = $meta_bar->withAdditionalEntry($item->getProviderIdentification()->getInternalIdentifier(), $slate);
        }

        return $meta_bar;
    }


    /**
     * @inheritDoc
     */
    protected function getMainBar() : MainBar
    {
        $f = $this->ui->factory();
        $main_bar = $f->mainControls()->mainBar();

        foreach ($this->gs->collector()->mainmenu()->getStackedTopItemsForPresentation() as $item) {
            /**
             * @var $slate Combined
             */
            $slate = $item->getTypeInformation()->getRenderer()->getComponentForItem($item);
            $identifier = $item->getProviderIdentification()->getInternalIdentifier();
            $main_bar = $main_bar->withAdditionalEntry($identifier, $slate);
        }

        $main_bar = $main_bar->withMoreButton(
            $f->button()->bulky($f->symbol()->icon()->custom("./src/UI/examples/Layout/Page/Standard/grid.svg", 'more', "small"), "More", "#")
        );

        // Tools
        if ($this->gs->collector()->tool()->hasTools()) {
            $main_bar = $main_bar->withToolsButton($f->button()->bulky($f->symbol()->icon()->custom("./src/UI/examples/Layout/Page/Standard/grid.svg", 'more', "small"), "More", "#"));
            foreach ($this->gs->collector()->tool()->getTools() as $tool) {
                $slate = $tool->getTypeInformation()->getRenderer()->getComponentForItem($tool);
                $id = $tool->getProviderIdentification()->getInternalIdentifier();
                $main_bar = $main_bar->withAdditionalToolEntry(md5(rand()), $slate);
            }
        }

        return $main_bar;
    }


    /**
     * @inheritDoc
     */
    public function setContent(Legacy $content)
    {
        $this->content = $content;
    }


    /**
     * @return Page
     */
    public function getPageForLayoutDefinition() : Page
    {
        $main_bar = null;
        $meta_bar = null;
        $bread_crumbs = null;
        $header_image = $this->ui->factory()->image()->standard(\ilUtil::getImagePath("HeaderIcon.svg"), "ILIAS");

        $main_bar = $this->getMainBar();
        $meta_bar = $this->getMetaBar();
        try {
            $meta_bar->getEntries();
        } catch (\TypeError $e) { // There are no entries
            $meta_bar = null;
        }

        $bread_crumbs = $this->getBreadCrumbs();

        return $this->ui->factory()->layout()->page()->standard([$this->content], $meta_bar, $main_bar, $bread_crumbs, $header_image);
    }
}
