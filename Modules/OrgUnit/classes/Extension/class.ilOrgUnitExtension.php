<?php

/**
 * Class ilOrgUnitExtension
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilOrgUnitExtension extends ilObjectPlugin
{

    /**
     * @var ilObjOrgUnitTree
     */
    protected $ilObjOrgUnitTree;

    /**
     * @var int
     */
    protected $parent_ref_id;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * ilOrgUnitExtension constructor.
     *
     * @param int $a_ref_id
     */
    public function __construct($a_ref_id = 0)
    {
        global $DIC;
        $tree = $DIC['tree'];

        parent::__construct($a_ref_id);
        $this->ilObjOrgUnitTree = ilObjOrgUnitTree::_getInstance();
        $this->parent_ref_id = $tree->getParentId($a_ref_id ? $a_ref_id : $_GET['ref_id']);
        $this->tree = $tree;
    }

    /**
     * Returns all Orgu Plugin Ids of active plugins where the Plugin wants to be shown in the tree. ($plugin->showInTree() == true)
     *
     * @return string[]
     */
    public static function getActivePluginIdsForTree()
    {
        /**
         * @var $plugin ilOrgUnitExtensionPlugin
         */
        $list = array();

        $plugin_ids = ilPlugin::getActivePluginIdsForSlot(IL_COMP_MODULE, "OrgUnit", "orguext");
        foreach ($plugin_ids as $plugin_id) {
            $plugin = ilObjectPlugin::getPluginObjectByType($plugin_id);
            if ($plugin->showInTree()) {
                $list[] = $plugin_id;
            }
        }

        return $list;
    }


    /**
     * @return ilOrgUnitExtensionPlugin
     * @throws ilPluginException
     */
    protected function getPlugin()
    {
        if (!$this->plugin) {
            $this->plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, "OrgUnit", "orguext", ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $this->getType()));
            if (!$this->plugin instanceof ilOrgUnitExtensionPlugin) {
                throw new ilPluginException("ilOrgUnitExtension: Could not instantiate plugin object for type " . $this->getType() . ".");
            }
        }

        return $this->plugin;
    }


    /**
     * Get all user ids of employees of the underlying OrgUnit.
     *
     * @param bool $recursively include all employees in the suborgunits
     * @return int[]
     */
    public function getEmployees($recursively = false)
    {
        return $this->ilObjOrgUnitTree->getEmployees($this->parent_ref_id, $recursively);
    }


    /**
     * Get all user ids of superiors of the underlying OrgUnit
     *
     * @param bool $recursively
     * @return int[]
     */
    public function getSuperiors($recursively = false)
    {
        return $this->ilObjOrgUnitTree->getSuperiors($this->parent_ref_id, $recursively);
    }


    /**
     * @return ilObjOrgUnit
     */
    public function getOrgUnit()
    {
        return ilObjectFactory::getInstanceByRefId($this->parent_ref_id);
    }

    /**
     * @return int[] RefIds from the root OrgUnit to the underlying OrgUnit
     */
    public function getOrgUnitPathRefIds()
    {
        $path = array();
        foreach ($this->getOrgUnitPath() as $node) {
            $path[] = $node['child'];
        }
        return $path;
    }

    /**
     *
     * @return array Returns the path to the underlying OrgUnit starting with the root OrgUnit. The array are nodes of the global $tree.
     */
    public function getOrgUnitPath()
    {
        return $this->tree->getPathFull($this->parent_ref_id, ilObjOrgUnit::getRootOrgRefId());
    }

    /**
     * @return string[] Returns the titles to the underlying OrgUnit starting with the root OrgUnit.
     */
    public function getOrgUnitPathTitles()
    {
        $titles = array();
        foreach ($this->getOrgUnitPath() as $node) {
            if ($node["title"] == "__OrgUnitAdministration") {
                $node["title"] = $this->lng->txt("objs_orgu");
            }
            $titles[] = $node['title'];
        }
        return $titles;
    }

    /**
     * @param bool $with_data if this is set to true, only the ids are delivered
     * @param string $type what type are you looking for?
     * @return array
     */
    public function getOrgUnitSubtree($with_data = true, $type = "")
    {
        $node = $this->tree->getNodeData($this->parent_ref_id);
        return $this->tree->getSubTree($node, $with_data, $type);
    }
}
