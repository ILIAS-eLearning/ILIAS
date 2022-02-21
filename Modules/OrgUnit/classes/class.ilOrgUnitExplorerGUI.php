<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Tree\TreeRecursion;

/**
 * Class ilOrgUnitExplorerGUI
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilOrgUnitExplorerGUI extends ilTreeExplorerGUI implements TreeRecursion
{
    protected const ORGU = "orgu";
    /**
     * @var array
     */
    protected $stay_with_command = array('', 'render', 'view', 'infoScreen', 'showStaff', 'performPaste', 'cut');
    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilAccessHandler
     */
    protected $access;


    /**
     * @param $a_expl_id
     * @param $a_parent_obj
     * @param $a_parent_cmd
     * @param $a_tree
     * @param $access
     */
    public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree, \ilAccessHandler $access = null)
    {
        parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree);
        $this->setAjax(true);
        $this->setTypeWhiteList(array(self::ORGU));
        $this->tree->initLangCode();
        $this->access = $access;
    }


    /**
     * @param mixed $node
     *
     * @return string
     */
    public function getNodeContent($node) : ?string
    {
        global $DIC;
        if ($node['title'] === '__OrgUnitAdministration') {
            $node['title'] = $DIC->language()->txt('objs_orgu');
        }
        if ((int) $node['child'] === (int) $_GET['ref_id']) {
            return "<span class='ilExp2NodeContent ilHighlighted'>" . $node['title'] . '</span>';
        }

        return $node['title'];
    }


    /**
     * @return array
     */
    public function getRootNode() : array
    {
        return $this->getTree()->getNodeData(ilObjOrgUnit::getRootOrgRefId());
    }


    /**
     * Get node icon
     * Return custom icon of OrgUnit type if existing
     *
     * @param array $a_node
     *
     * @return string
     */
    public function getNodeIcon($a_node) : string
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        if ($ilias->getSetting('custom_icons')) {
            $icons_cache = ilObjOrgUnit::getIconsCache();
            $obj_id = ilObject::_lookupObjId($a_node["child"]);
            if (isset($icons_cache[$obj_id])) {
                return $icons_cache[$obj_id];
            }
        }

        return ilObject::_getIcon($obj_id, "tiny", $a_node["type"]);
    }


    /**
     * @param array $node
     *
     * @return string
     */
    public function getNodeHref($node) : string
    {
        global $DIC;

        if ($this->select_postvar) {
            return '#';
        }

        if ($DIC->ctrl()->getCmd() === 'performPaste') {
            $DIC->ctrl()->setParameterByClass(ilObjOrgUnitGUI::class, 'target_node', $node['child']);
        }
        $array = $DIC->ctrl()->getParameterArrayByClass(ilObjOrgUnitGUI::class);
        $temp = $array['ref_id'];

        $DIC->ctrl()->setParameterByClass(ilObjOrgUnitGUI::class, 'ref_id', $node['child']);
        $DIC->ctrl()->setParameterByClass(ilObjPluginDispatchGUI::class, 'ref_id', $node['child']);

        $link_target = ($node['type'] === self::ORGU) ? $this->getLinkTarget() : $this->getPluginLinkTarget();
        $DIC->ctrl()->setParameterByClass(ilObjOrgUnitGUI::class, 'ref_id', $temp);

        return $link_target;
    }


    /**
     * @return string
     */
    protected function getLinkTarget() : string
    {
        global $DIC;
        if ($DIC->ctrl()->getCmdClass() === strtolower(ilObjOrgUnitGUI::class) && in_array($DIC->ctrl()->getCmd(), $this->stay_with_command, true)) {
            return $DIC->ctrl()->getLinkTargetByClass(array(ilAdministrationGUI::class, $DIC->ctrl()->getCmdClass()), $DIC->ctrl()->getCmd());
        }

        return $DIC->ctrl()->getLinkTargetByClass(array(ilAdministrationGUI::class, ilObjOrgUnitGUI::class), 'view');
    }


    /**
     * @return string
     */
    protected function getPluginLinkTarget() : string
    {
        global $DIC;

        return $DIC->ctrl()->getLinkTargetByClass(ilObjPluginDispatchGUI::class, 'forward');
    }


    /**
     * @param array $a_node
     *
     * @return bool
     */
    public function isNodeClickable($a_node) : bool
    {
        global $DIC;
        $ilAccess = $DIC->access();

        if ($ilAccess->checkAccess('read', '', $a_node['ref_id'])) {
            return true;
        }

        return false;
    }


    /**
     * @param array $a_node
     *
     * @return bool
     */
    public function isNodeSelectable($a_node) : bool
    {
        $current_node = filter_input(INPUT_GET, 'item_ref_id') ?? ilObjOrgUnit::getRootOrgRefId();

        return !($a_node['child'] === $current_node || $this->tree->isGrandChild($current_node, $a_node['child']));
    }

    public function getChildsOfNode($a_parent_node_id)
    {
        $children = parent::getChildsOfNode($a_parent_node_id);

        if (!is_null($this->access)) {
            $children = $this->filterChildrenByPermission($children);
        }

        return $children;
    }

    protected function filterChildrenByPermission(array $children) : array
    {
        return array_filter(
            $children,
            function ($child) {
                return $this->access->checkAccess("visible", "", $child["ref_id"]);
            }
        );
    }
}
