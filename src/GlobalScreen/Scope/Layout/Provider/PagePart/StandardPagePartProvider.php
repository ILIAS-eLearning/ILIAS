<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart;

use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\Slate\Combined;
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

    use isSupportedTrait;
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
    public function getContent() : ?Legacy
    {
        return $this->content ?? new LegacyImplementation("");
    }


    /**
     * @inheritDoc
     */
    public function getMetaBar() : ?MetaBar
    {
        $f = $this->ui->factory();
        $meta_bar = $f->mainControls()->metaBar();
        $has_items = false;
        foreach ($this->gs->collector()->metaBar()->getStackedItems() as $item) {
            $has_items = true;
            $component = $item->getRenderer()->getComponentForItem($item);
            if ($this->isComponentSupportedForCombinedSlate($component)) {
                $meta_bar = $meta_bar->withAdditionalEntry($item->getProviderIdentification()->getInternalIdentifier(), $component);
            }
        }

        return $has_items ? $meta_bar : null;
    }


    /**
     * @inheritDoc
     */
    public function getMainBar() : ?MainBar
    {
        $f = $this->ui->factory();
        $main_bar = $f->mainControls()->mainBar();

        foreach ($this->gs->collector()->mainmenu()->getStackedTopItemsForPresentation() as $item) {
            /**
             * @var $component Combined
             */
            $component = $item->getTypeInformation()->getRenderer()->getComponentForItem($item);
            $identifier = $item->getProviderIdentification()->getInternalIdentifier();
            if ($this->isComponentSupportedForCombinedSlate($component)) {
                $main_bar = $main_bar->withAdditionalEntry($identifier, $component);
            }
        }

        $main_bar = $main_bar->withMoreButton(
            $f->button()->bulky($f->symbol()->icon()->custom("./src/UI/examples/Layout/Page/Standard/grid.svg", 'more', "small"), "More", "#")
        );

        // Tools
        if ($this->gs->collector()->tool()->hasTools()) {
            $main_bar = $main_bar->withToolsButton($f->button()->bulky($f->symbol()->icon()->custom("./src/UI/examples/Layout/Page/Standard/grid.svg", 'more', "small"), "More", "#"));
            foreach ($this->gs->collector()->tool()->getTools() as $tool) {
                $component = $tool->getTypeInformation()->getRenderer()->getComponentForItem($tool);
                $main_bar = $main_bar->withAdditionalToolEntry(md5(rand()), $component);
            }
        }

        return $main_bar;
    }


    /**
     * @inheritDoc
     */
    public function getBreadCrumbs() : ?Breadcrumbs
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
    public function getLogo() : ?Image
    {
        return $this->ui->factory()->image()->standard(ilUtil::getImagePath("HeaderIcon.svg"), "ILIAS");
    }
}
