<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

/**
 * Class ilOrgUnitExtensionGUI
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilOrgUnitExtensionGUI extends ilObjectPluginGUI
{
    protected ilLocatorGUI $ilLocator;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct(int $a_ref_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
        $this->ilLocator = $DIC['ilLocator'];
        $this->tpl = $DIC->ui()->mainTemplate();

        $this->showTree();
    }

    protected function supportsExport() : bool
    {
        return false;
    }

    protected function lookupParentTitleInCreationMode() : string
    {
        $parent = parent::lookupParentTitleInCreationMode();
        if ($parent == '__OrgUnitAdministration') {
            return $this->lng->txt("objs_orgu");
        }

        return $parent;
    }

    /**
     * @return bool returns true iff this object supports cloning.
     */
    protected function supportsCloning() : bool
    {
        return false;
    }

    /**
     * Override the locator (breadcrumbs). We want the breadcrumbs with the Admin Org Unit node as a root and not the repository.
     */
    protected function setLocator() : void
    {
        if ($this->getCreationMode()) {
            $endnode_id = $this->parent_id;
        } else {
            $endnode_id = $this->ref_id;
        }

        $path = $this->tree->getPathFull($endnode_id, ilObjOrgUnit::getRootOrgRefId());
        // add item for each node on path
        foreach ((array) $path as $key => $row) {
            if ($row["title"] == "__OrgUnitAdministration") {
                $row["title"] = $this->lng->txt("objs_orgu");
            }
            $this->ctrl->setParameterByClass("ilobjorgunitgui", "ref_id", $row["child"]);
            $this->ilLocator->addItem($row["title"], $this->ctrl->getLinkTargetByClass(array(
                "iladministrationgui",
                "ilobjorgunitgui",
            ), "view"), ilFrameTargetInfo::_getFrame("MainContent"), $row["child"]);
            $this->ctrl->setParameterByClass("ilobjplugindispatchgui", "ref_id", $_GET["ref_id"]);
        }
        $this->tpl->setLocator();
    }

    /**
     * Views in the Org Unit have the Navigation Tree enabled by default. Thus we display it as well in the plugins.
     */
    public function showTree(): void
    {
        $this->ctrl->setParameterByClass("ilObjPluginDispatchGUI", "ref_id", $_GET["ref_id"]);
        $this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $_GET["ref_id"]);
        $tree = new ilOrgUnitExplorerGUI("orgu_explorer", array("ilAdministrationGUI", "ilObjOrgUnitGUI"), "showTree",
            new ilTree(1));
        $tree->setTypeWhiteList($this->getTreeWhiteList());
        if (!$tree->handleCommand()) {
            $this->tpl->setLeftNavContent($tree->getHTML());
        }
    }

    protected function getTreeWhiteList(): array
    {
        $whiteList = array("orgu");
        $pls = ilOrgUnitExtension::getActivePluginIdsForTree();

        return array_merge($whiteList, $pls);
    }
}
