<?php namespace ILIAS\MyStaff\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilObjOrgUnit;

/**
 * Class OrgUnitMainBarProvider
 *
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
        $items = [];
        $top = StandardTopItemsProvider::getInstance()->getAdministrationIdentification();

        $title = $this->dic->language()->txt("objs_orgu");
        $action = "ilias.php?baseClass=ilAdministrationGUI&ref_id=" . ilObjOrgUnit::getRootOrgRefId() . "&cmd=jump";
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard('orgu', $title)
            ->withIsOutlined(true);

        $items[] = $this->mainmenu->link($this->if->identifier('mm_adm_orgu'))
            ->withAction($action)
            ->withParent($top)
            ->withTitle($title)
            ->withSymbol($icon)
            ->withPosition(5)
            ->withVisibilityCallable(
                function () {
                    return (bool) ($this->dic->rbac()->system()->checkAccess("visible", SYSTEM_FOLDER_ID));
                }
            )->withAvailableCallable(
                function () {
                    return ($this->dic->user()->getId() != ANONYMOUS_USER_ID);
                }
            );;

        return $items;
    }
}
