<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;

/**
 * Class ilPDGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilPDGlobalScreenProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @var IdentificationInterface
     */
    protected $top_item;


    public function __construct(\ILIAS\DI\Container $dic)
    {
        parent::__construct($dic);
        $this->top_item = $this->if->identifier('desktop');
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
        $dic = $this->dic;

        // Personal Desktop TopParentItem
        return [$this->mainmenu->topParentItem($this->getTopItem())
                    ->withTitle($this->dic->language()->txt("personal_desktop"))
                    ->withPosition(1)
                    ->withVisibilityCallable(
                        function () use ($dic) {
                            return (bool) ($dic->user()->getId() != ANONYMOUS_USER_ID);
                        }
                    )];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        $dic = $this->dic;

        $dic->language()->loadLanguageModule("pd");

        // overview
        $entries[] = $this->mainmenu->link($this->if->identifier('mm_pd_sel_items'))
            ->withTitle($this->dic->language()->txt("overview"))
            ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSelectedItems")
            ->withParent($this->getTopItem())
            ->withPosition(1)
            ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
            ->withAvailableCallable(
                function () use ($dic) {
                    return $dic->settings()->get('disable_my_offers', 0) == 0;
                }
            )
            ->withVisibilityCallable(
                function () use ($dic) {
                    $pdItemsViewSettings = new ilPDSelectedItemsBlockViewSettings($dic->user());

                    return (bool) $pdItemsViewSettings->allViewsEnabled() || $pdItemsViewSettings->enabledSelectedItems();
                }
            );

        // my groups and courses, if both is available
        $entries[] = $this->mainmenu->link($this->if->identifier('mm_pd_crs_grp'))
            ->withTitle($this->dic->language()->txt("my_courses_groups"))
            ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMemberships")
            ->withParent($this->getTopItem())
            ->withPosition(2)
            ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
            ->withAvailableCallable(
                function () use ($dic) {
                    return $dic->settings()->get('disable_my_memberships', 0) == 0;
                }
            )
            ->withVisibilityCallable(
                function () use ($dic) {
                    $pdItemsViewSettings = new ilPDSelectedItemsBlockViewSettings($dic->user());

                    return (bool) $pdItemsViewSettings->allViewsEnabled() || $pdItemsViewSettings->enabledMemberships();
                }
            );

        // achievements
        $entries[] = $this->mainmenu->link($this->if->identifier('mm_pd_achiev'))
            ->withTitle($this->dic->language()->txt("pd_achievements"))
            ->withAction($dic->ctrl()->getLinkTargetByClass(["ilPersonalDesktopGUI", "ilAchievementsGUI"], ""))
            ->withParent($this->getTopItem())
            ->withPosition(7)
            ->withAvailableCallable(
                function () use ($dic) {
                    $achievements = new ilAchievements();

                    return (bool) $achievements->isAnyActive();
                }
            );

        return $entries;
    }
}
