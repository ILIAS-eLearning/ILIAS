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
 * Class ilOrgUnitExtension
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilOrgUnitExtension extends ilObjectPlugin
{
    protected ilObjOrgUnitTree $ilObjOrgUnitTree;
    protected int $parent_ref_id;
    protected ilTree $tree;

    /**
     * ilOrgUnitExtension constructor.
     */
    public function __construct(int $a_ref_id = 0)
    {
        global $DIC;
        $tree = $DIC->repositoryTree();

        $http = $DIC->http();
        $refinery = $DIC->refinery();
        $ref_id = $http->wrapper()->query()->retrieve('ref_id', $refinery->to()->int());

        parent::__construct($a_ref_id);
        $this->ilObjOrgUnitTree = ilObjOrgUnitTree::_getInstance();
        $this->parent_ref_id = $tree->getParentId($a_ref_id ? $a_ref_id : $ref_id);
        $this->tree = $tree;
    }

    /**
     * Returns all Orgu Plugin Ids of active plugins where the Plugin wants to be shown in the tree. ($plugin->showInTree() == true)
     * @return string[]
     */
    public static function getActivePluginIdsForTree(): array
    {
        global $DIC;
        $component_factory = $DIC["component.factory"];

        /**
         * @var $plugin ilOrgUnitExtensionPlugin
         */
        $list = array();

        foreach ($component_factory->getActivePluginsInSlot("orguext") as $plugin) {
            if ($plugin->showInTree()) {
                $list[] = $plugin->getId();
            }
        }

        return $list;
    }

    /**
     * Get all user ids of employees of the underlying OrgUnit.
     * @param bool $recursively include all employees in the suborgunits
     * @return int[]
     */
    public function getEmployees(bool $recursively = false): array
    {
        return $this->ilObjOrgUnitTree->getEmployees($this->parent_ref_id, $recursively);
    }

    /**
     * Get all user ids of superiors of the underlying OrgUnit
     * @param bool $recursively
     * @return int[]
     */
    public function getSuperiors(bool $recursively = false): array
    {
        return $this->ilObjOrgUnitTree->getSuperiors($this->parent_ref_id, $recursively);
    }

    public function getOrgUnit(): ?ilObject
    {
        return ilObjectFactory::getInstanceByRefId($this->parent_ref_id);
    }

    /**
     * @return int[] RefIds from the root OrgUnit to the underlying OrgUnit
     */
    public function getOrgUnitPathRefIds(): array
    {
        $path = array();
        foreach ($this->getOrgUnitPath() as $node) {
            $path[] = $node['child'];
        }

        return $path;
    }

    /**
     * @return array Returns the path to the underlying OrgUnit starting with the root OrgUnit. The array are nodes of the global $tree.
     */
    public function getOrgUnitPath(): array
    {
        return $this->tree->getPathFull($this->parent_ref_id, ilObjOrgUnit::getRootOrgRefId());
    }

    /**
     * @return string[] Returns the titles to the underlying OrgUnit starting with the root OrgUnit.
     */
    public function getOrgUnitPathTitles(): array
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
     * @param bool   $with_data if this is set to true, only the ids are delivered
     * @param string $type      what type are you looking for?
     * @return array
     */
    public function getOrgUnitSubtree(bool $with_data = true, string $type = ""): array
    {
        $node = $this->tree->getNodeData($this->parent_ref_id);

        return $this->tree->getSubTree($node, $with_data, [$type]);
    }
}
