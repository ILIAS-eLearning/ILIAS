<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart;

use ILIAS\GlobalScreen\Client\ItemState;
use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\SlateSessionStateCode;
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
    use SlateSessionStateCode;
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
        $this->gs->collector()->metaBar()->collect();
        if (!$this->gs->collector()->metaBar()->hasItems()) {
            return null;
        }
        $f = $this->ui->factory();
        $meta_bar = $f->mainControls()->metaBar();

        foreach ($this->gs->collector()->metaBar()->getItems() as $item) {

            $component = $item->getRenderer()->getComponentForItem($item);
            if ($this->isComponentSupportedForCombinedSlate($component)) {
                $meta_bar = $meta_bar->withAdditionalEntry($item->getProviderIdentification()->getInternalIdentifier(), $component);
            }
        }

        return $meta_bar;
    }


    /**
     * @inheritDoc
     */
    public function getMainBar() : ?MainBar
    {
        $this->gs->collector()->mainmenu()->collect();
        if (!$this->gs->collector()->mainmenu()->hasItems()) {
            return null;
        }

        $f = $this->ui->factory();
        $main_bar = $f->mainControls()->mainBar();

        foreach ($this->gs->collector()->mainmenu()->getItems() as $item) {
            /**
             * @var $component Combined
             */
            $component = $item->getTypeInformation()->getRenderer()->getComponentForItem($item);
            $identifier = $item->getProviderIdentification()->getInternalIdentifier();

            if ($this->isComponentSupportedForCombinedSlate($component)) {
                $main_bar = $main_bar->withAdditionalEntry($identifier, $component);
            }

            $item_state = new ItemState($item->getProviderIdentification());
            if ($item_state->isItemActive()) {
                $main_bar = $main_bar->withActive($identifier);
            }
        }

        $grid_icon = $f->symbol()->icon()->custom("./src/UI/examples/Layout/Page/Standard/grid.svg", 'more', "small");
        $main_bar = $main_bar->withMoreButton(
            $f->button()->bulky($grid_icon, "More", "#")
        );

        // Tools
        $this->gs->collector()->tool()->collect();
        if ($this->gs->collector()->tool()->hasItems()) {
            $tools_button = $f->button()->bulky($grid_icon, "Tools", "#")->withEngagedState(true);
            $main_bar = $main_bar->withToolsButton($tools_button);
            foreach ($this->gs->collector()->tool()->getItems() as $tool) {
                $component = $tool->getTypeInformation()->getRenderer()->getComponentForItem($tool);
                $identifier = $this->hash($tool->getProviderIdentification()->serialize());
                $main_bar = $main_bar->withAdditionalToolEntry($identifier, $component);
                $item_state = new ItemState($tool->getProviderIdentification());
                if ($item_state->isItemActive()) {
                    $main_bar = $main_bar->withActive($identifier);
                }
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
