<?php namespace ILIAS\PersonalDesktop;

use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;
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
        $access_helper = BasicAccessCheckClosures::getInstance();

        $f = $this->dic->ui()->factory();

        $title = $this->dic->language()->txt("mm_favorites");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_fav.svg"), $title);

        $items = [
            $this->mainmenu->complex($this->if->identifier('mm_pd_sel_items'))
                ->withSupportsAsynchronousLoading(true)
                ->withTitle($title)
                ->withSymbol($icon)
                ->withContentWrapper(function () use ($f) {
                    $fav_list = new \ilFavouritesListGUI();

                    return $f->legacy($fav_list->render());
                })
                ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                ->withPosition(10)
                ->withAvailableCallable(
                    function () use ($dic) {
                        // note: the setting disable_my_offers is used for
                        // presenting the favourites in the main section of the dashboard
                        // return $dic->settings()->get('disable_my_offers', 0) == 0;
                        return true;
                    }
                )
                ->withVisibilityCallable(
                    $access_helper->isUserLoggedIn($access_helper->isRepositoryReadable(
                        static function () use ($dic) : bool {
                            return true;
                            $pdItemsViewSettings = new ilPDSelectedItemsBlockViewSettings($dic->user());

                            return (bool) $pdItemsViewSettings->allViewsEnabled() || $pdItemsViewSettings->enabledSelectedItems();
                        }
                    ))
                ),
        ];

        $top = StandardTopItemsProvider::getInstance()->getAdministrationIdentification();

        $title  = $this->dic->language()->txt("obj_dshs");
        $objects_by_type = \ilObject2::_getObjectsByType('dshs');
        $id = (int) reset($objects_by_type)['obj_id'];
        $references = \ilObject2::_getAllReferences($id);
        $admin_ref_id = (int) reset($references);

        if ($admin_ref_id > 0) {
            $action = "ilias.php?baseClass=ilAdministrationGUI&ref_id=" . $admin_ref_id . "&cmd=jump";
            $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("dshs", $title)
                              ->withIsOutlined(true);

            $items[] = $this->mainmenu->link($this->if->identifier('mm_adm_dshs'))
                                      ->withAction($action)
                                      ->withParent($top)
                                      ->withTitle($title)
                                      ->withSymbol($icon)
                                      ->withPosition(25)
                                        ->withVisibilityCallable(function() use($admin_ref_id){
                                            return $this->dic->rbac()->system()->checkAccess('visible,read', $admin_ref_id);
                                        });
        }


        return $items;
    }
}
