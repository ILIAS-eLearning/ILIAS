<?php namespace ILIAS\OrgUnit\Provider;

use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilObjOrgUnit;

/**
 * Class OrgUnitMainBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class OrgUnitMainBarProvider extends AbstractStaticMainMenuProvider
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
        $this->dic->language()->loadLanguageModule('mst');
        $items         = [];
        $access_helper = BasicAccessCheckClosures::getInstance();
        $top           = StandardTopItemsProvider::getInstance()->getAdministrationIdentification();

        $title  = $this->dic->language()->txt("objs_orgu");
        $action = "ilias.php?baseClass=ilAdministrationGUI&ref_id=" . ilObjOrgUnit::getRootOrgRefId() . "&cmd=jump";
        $icon   = $this->dic->ui()->factory()->symbol()->icon()->standard('orgu', $title)
                            ->withIsOutlined(true);

        $items[] = $this->mainmenu->link($this->if->identifier('mm_adm_orgu'))
                                  ->withAlwaysAvailable(true)
                                  ->withAction($action)
                                  ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('item_must_be_always_active')}"))
                                  ->withParent($top)
                                  ->withTitle($title)
                                  ->withSymbol($icon)
                                  ->withPosition(65)
                                  ->withVisibilityCallable(
                                      $access_helper->hasAdministrationAccess(function () : bool {
                                          return (bool) $this->dic->access()->checkAccess('read', '', ilObjOrgUnit::getRootOrgRefId());
                                      }));

        return $items;
    }
}
