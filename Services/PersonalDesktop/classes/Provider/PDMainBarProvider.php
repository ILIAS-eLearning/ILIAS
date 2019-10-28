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

        $f = $this->dic->ui()->factory();

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/heart.svg"), "");

        $fav_list = new \ilFavouritesListGUI();
        $contents = $f->legacy($fav_list->render());

        return [
            $this->mainmenu->complex($this->if->identifier('mm_pd_sel_items'))
                ->withTitle($this->dic->language()->txt("mm_favorites"))
                ->withSymbol($icon)
                ->withContent($contents)
                ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                ->withAlwaysAvailable(true)
                ->withPosition(10)
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
                )

        ];
    }
}
