<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart;

use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\SlateSessionStateCode;
use ILIAS\GlobalScreen\Scope\Tool\Factory\isToolItem;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\Slate\Combined;
use ilUserUtil;
use ilUtil;

/**
 * Class StandardPagePartProvider
 * @internal
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
     * @var \ilLanguage
     */
    protected $lang;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->gs = $DIC->globalScreen();
        $this->lang = $DIC->language();
    }

    /**
     * @inheritDoc
     */
    public function getContent() : ?Legacy
    {
        return $this->content ?? $this->ui->factory()->legacy("");
    }

    /**
     * @inheritDoc
     */
    public function getMetaBar() : ?MetaBar
    {
        $this->gs->collector()->metaBar()->collectOnce();
        if (!$this->gs->collector()->metaBar()->hasItems()) {
            return null;
        }
        $f        = $this->ui->factory();
        $meta_bar = $f->mainControls()->metaBar();

        foreach ($this->gs->collector()->metaBar()->getItemsForUIRepresentation() as $item) {
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
        $this->gs->collector()->mainmenu()->collectOnce();
        if (!$this->gs->collector()->mainmenu()->hasItems()) {
            return null;
        }

        $f        = $this->ui->factory();
        $main_bar = $f->mainControls()->mainBar();

        foreach ($this->gs->collector()->mainmenu()->getItemsForUIRepresentation() as $item) {
            /**
             * @var $component Combined
             */
            $component  = $item->getTypeInformation()->getRenderer()->getComponentForItem($item, false);
            $identifier = $this->hash($item->getProviderIdentification()->serialize());

            if ($this->isComponentSupportedForCombinedSlate($component)) {
                $main_bar = $main_bar->withAdditionalEntry($identifier, $component);
            }
        }

        // Tools
        $grid_icon = $f->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_tool.svg"), $this->lang->txt('more'));
        $this->gs->collector()->tool()->collectOnce();
        if ($this->gs->collector()->tool()->hasItems()) {
            $tools_button = $f->button()->bulky($grid_icon, $this->lang->txt('tools'), "#")->withEngagedState(true);
            $main_bar = $main_bar->withToolsButton($tools_button);
            /**
             * @var $main_bar MainBar
             */
            foreach ($this->gs->collector()->tool()->getItemsForUIRepresentation() as $tool) {
                if (!$tool instanceof isToolItem) {
                    continue;
                }
                $component = $tool->getTypeInformation()->getRenderer()->getComponentForItem($tool, false);

                $identifier   = $this->hash($tool->getProviderIdentification()->serialize());
                $close_button = null;
                if ($tool->hasCloseCallback()) {
                    $close_button = $this->ui->factory()->button()->close()->withOnLoadCode(static function (string $id) use ($identifier) {
                        return "$('#$id').on('click', function(){
                            $.ajax({
                                url: 'src/GlobalScreen/Client/callback_handler.php?item=$identifier'
                            }).done(function() {
                                console.log('done closing');
                            });
                        });";
                    });
                }
                $main_bar = $main_bar->withAdditionalToolEntry($identifier, $component, $tool->isInitiallyHidden(), $close_button);
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

        $f      = $this->ui->factory();
        $crumbs = [];
        foreach ($DIC['ilLocator']->getItems() as $item) {
            if (empty($item['title']) || empty($item['link'])) {
                continue;
            }
            $crumbs[] = $f->link()->standard($item['title'], $item["link"]);
        }

        return $f->breadcrumbs($crumbs);
    }

    /**
     * @inheritDoc
     */
    public function getLogo() : ?Image
    {
        $std_logo      = ilUtil::getImagePath("HeaderIcon.svg");
        $std_logo_link = ilUserUtil::getStartingPointAsUrl();
        if (!$std_logo_link) {
            $std_logo_link = "./goto.php?target=root_1";
        }

        return $this->ui->factory()->image()
                        ->standard($std_logo, "ILIAS")
                        ->withAction($std_logo_link);
    }

    /**
     * @inheritDoc
     */
    public function getSystemInfos() : array
    {
        $system_infos = [];

        foreach ($this->gs->collector()->notifications()->getAdministrativeNotifications() as $adn) {
            $system_infos[] = $adn->getRenderer($this->ui->factory())->getNotificationComponentForItem($adn);
        }

        return $system_infos;

    }

    /**
     * @inheritDoc
     */
    public function getFooter() : ?Footer
    {
        return $this->ui->factory()->mainControls()->footer([]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return 'title';
    }

    /**
     * @inheritDoc
     */
    public function getShortTitle() : string
    {
        return 'short';
    }

    /**
     * @inheritDoc
     */
    public function getViewTitle() : string
    {
        return 'view';
    }
}
