<?php

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;

/**
 * Class ilStaffGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilStaffGlobalScreenProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @var IdentificationInterface
     */
    protected $top_item;


    /**
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->top_item = (new ilPDGlobalScreenProvider($dic))->getTopItem();
    }


    /**
     * Some other components want to provide Items for the main menu which are
     * located at the PD TopTitem by default. Therefore we have to provide our
     * TopTitem Identification for others
     *
     * @return IdentificationInterface
     */
    public function getTopItem() : IdentificationInterface
    {
        return $this->top_item;
    }


    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        $dic = $this->dic;

        return [$this->mainmenu->link($this->if->identifier('mm_pd_mst'))
                    ->withTitle($this->dic->language()->txt("my_staff"))
                    ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMyStaff")
                    ->withParent($this->getTopItem())
                    ->withPosition(12)
                    ->withAvailableCallable(
                        function () use ($dic) {
                            return (bool) ($dic->settings()->get("enable_my_staff"));
                        }
                    )
                    ->withVisibilityCallable(
                        function () {
                            return (bool) ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff();
                        }
                    )->withNonAvailableReason($dic->ui()->factory()->legacy("{$dic->language()->txt('component_not_active')}"))];
    }
}
