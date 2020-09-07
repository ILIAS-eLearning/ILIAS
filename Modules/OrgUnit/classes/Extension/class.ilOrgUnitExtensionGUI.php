<?php


/**
 * Class ilOrgUnitExtensionGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilOrgUnitExtensionGUI extends ilObjectPluginGUI
{

    /**
     * @var ilLocatorGUI
     */
    protected $ilLocator;


    /**
     * ilOrgUnitExtensionGUI constructor.
     *
     * @param int $a_ref_id
     * @param int $a_id_type
     * @param int $a_parent_node_id
     */
    public function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;
        $ilLocator = $DIC['ilLocator'];
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
        $this->ilLocator = $ilLocator;
        $this->showTree();
    }


    /**
     * Get plugin object
     *
     * @return ilOrgUnitExtensionPlugin plugin object
     * @throws ilPluginException
     */
    protected function getPlugin()
    {
        if (!$this->plugin) {
            $this->plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, "OrgUnit", "orguext", ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $this->getType()));
            if (!$this->plugin instanceof ilOrgUnitExtensionPlugin) {
                throw new ilPluginException("ilObjectPluginGUI: Could not instantiate plugin object for type " . $this->getType() . ".");
            }
        }

        return $this->plugin;
    }


    /**
     * @return bool
     */
    protected function supportsExport()
    {
        return false;
    }


    /**
     * @return string
     */
    protected function lookupParentTitleInCreationMode()
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
    protected function supportsCloning()
    {
        return false;
    }


    /**
     * Override the locator (breadcrumbs). We want the breadcrumbs with the Admin Org Unit node as a root and not the repository.
     */
    protected function setLocator()
    {
        global $DIC;
        $tpl = $DIC['tpl'];
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
        $tpl->setLocator();
    }


    /**
     * Views in the Org Unit have the Navigation Tree enabled by default. Thus we display it as well in the plugins.
     */
    public function showTree()
    {
        $this->ctrl->setParameterByClass("ilObjPluginDispatchGUI", "ref_id", $_GET["ref_id"]);
        $this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $_GET["ref_id"]);
        $tree = new ilOrgUnitExplorerGUI("orgu_explorer", array("ilAdministrationGUI", "ilObjOrgUnitGUI"), "showTree", new ilTree(1));
        $tree->setTypeWhiteList($this->getTreeWhiteList());
        if (!$tree->handleCommand()) {
            $this->tpl->setLeftNavContent($tree->getHTML());
        }
    }


    /**
     * @return array
     */
    protected function getTreeWhiteList()
    {
        $whiteList = array("orgu");
        $pls = ilOrgUnitExtension::getActivePluginIdsForTree();

        return array_merge($whiteList, $pls);
    }
}
