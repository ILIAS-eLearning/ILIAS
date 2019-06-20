<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\LinkItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopLegacyItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopParentItem;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\Slate\Combined;
use ILIAS\UI\Component\MainControls\Slate\Slate;
use ILIAS\UI\Implementation\Component\Legacy\Legacy as LegacyImplementation;
use ilUtil;

/**
 * Class StandardPagePartProvider
 *
 * @internal
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardPagePartProvider implements PagePartProvider
{

    /**
     * @var Legacy
     */
    protected $content;
    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    protected $gs;
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;


    /**
     * @inheritDoc
     */
    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->gs = $DIC->globalScreen();
    }


    /**
     * @inheritDoc
     */
    public function getContent() : Legacy
    {
        return $this->content ?? new LegacyImplementation("");
    }


    /**
     * @inheritDoc
     */
    public function getMetaBar() : MetaBar
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
            if (isset($slate) && $slate instanceof Slate) {
                $meta_bar = $meta_bar->withAdditionalEntry($item->getProviderIdentification()->getInternalIdentifier(), $slate);
            }
        }

        return $meta_bar;
    }


    /**
     * @inheritDoc
     */
    public function getMainBar() : MainBar
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
    public function getBreadCrumbs() : Breadcrumbs
    {
        // TODO this currently gets the items from ilLocatorGUI, should that serve be removed with
        // something like GlobalScreen\Scope\Locator\Item
        global $DIC;

        $f = $this->ui->factory();
        $crumbs = [];
        foreach ($DIC['ilLocator']->getItems() as $item) {
            $crumbs[] = $f->link()->standard($item['title'], $item["link"]);
        }

        return $f->breadcrumbs($crumbs);
    }


    /**
     * @inheritDoc
     */
    public function getLogo() : Image
    {
        return $this->ui->factory()->image()->standard(ilUtil::getImagePath("HeaderIcon.svg"), "ILIAS");
    }
}
