<?php namespace ILIAS\PersonalDesktop;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilPDSelectedItemsBlockViewSettings;

/**
 * Class PDMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PDMainBarProvider extends AbstractStaticMainMenuProvider
{

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

        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("pdts", "")->withIsOutlined(true);
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/heart.svg"), "");

        // Favorites
        $entries[] = $this->mainmenu->link($this->if->identifier('mm_pd_sel_items'))
            ->withTitle($this->dic->language()->txt("mm_favorites"))
            ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSelectedItems")
            ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
            ->withPosition(10)
	        ->withSymbol($icon)
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

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/home.svg"), "");

        // Dashboard
        $entries[] = $this->mainmenu->link($this->if->identifier('mm_pd_crs_grp'))
            ->withTitle($this->dic->language()->txt("mm_dashboard"))
            ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMemberships")
            ->withParent(StandardTopItemsProvider::getInstance()->getRepositoryIdentification())
            ->withPosition(10)
            ->withSymbol($icon)
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

        return $entries;
    }
}
