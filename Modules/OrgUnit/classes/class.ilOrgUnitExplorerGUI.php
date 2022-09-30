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
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Tree\TreeRecursion;

/**
 * Class ilOrgUnitExplorerGUI
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 */
class ilOrgUnitExplorerGUI extends ilTreeExplorerGUI implements TreeRecursion
{
    protected const ORGU = "orgu";
    protected array $stay_with_command = array('', 'render', 'view', 'infoScreen', 'showStaff', 'performPaste', 'cut');
    protected ?ilTree $tree = null;
    protected ilAccessHandler $access;
    private ilSetting $settings;

    /**
     * @param $a_expl_id
     * @param $a_parent_obj
     * @param $a_parent_cmd
     * @param $a_tree
     * @param $access
     */
    public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree, \ilAccessHandler $access = null)
    {
        global $DIC;
        parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree);
        $this->setAjax(true);
        $this->setTypeWhiteList(array(self::ORGU));
        $this->tree->initLangCode();
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
    }

    /**
     * Get content of a node
     * @param object|array $a_node node array or object
     * @return string content of the node
     */
    public function getNodeContent($a_node): string
    {
        $node = $this->getNodeArrayRepresentation($a_node);

        if ($node['title'] === '__OrgUnitAdministration') {
            $node['title'] = $this->lng->txt('objs_orgu');
        }
        if ((int) $node['child'] === (int) $_GET['ref_id']) {
            return "<span class='ilExp2NodeContent ilHighlighted'>" . $node['title'] . '</span>';
        }

        return $node['title'];
    }

    public function getRootNode(): array
    {
        return $this->getTree()->getNodeData(ilObjOrgUnit::getRootOrgRefId());
    }

    /**
     * Get node icon
     * @param array|object $a_node
     * Return custom icon of OrgUnit type if existing
     * @return string
     */
    public function getNodeIcon($a_node): string
    {
        $node = $this->getNodeArrayRepresentation($a_node);

        $obj_id = 0;
        if ($this->settings->get('custom_icons')) {
            $icons_cache = ilObjOrgUnit::getIconsCache();
            $obj_id = ilObject::_lookupObjId($node["child"]);
            if (isset($icons_cache[$obj_id])) {
                return $icons_cache[$obj_id];
            }
        }

        return ilObject::_getIcon($obj_id, "tiny", $node["type"]);
    }

    /**
     * @param array|object $a_node
     * @throws ilCtrlException
     */
    public function getNodeHref($a_node): string
    {
        $node = $this->getNodeArrayRepresentation($a_node);

        if ($this->select_postvar) {
            return '#';
        }

        if ($this->ctrl->getCmd() === 'performPaste') {
            $this->ctrl->setParameterByClass(ilObjOrgUnitGUI::class, 'target_node', $node['child']);
        }
        $array = $this->ctrl->getParameterArrayByClass(ilObjOrgUnitGUI::class);
        $temp = $array['ref_id'];

        $this->ctrl->setParameterByClass(ilObjOrgUnitGUI::class, 'ref_id', $node['child']);
        $this->ctrl->setParameterByClass(ilObjPluginDispatchGUI::class, 'ref_id', $node['child']);

        $link_target = ($node['type'] === self::ORGU) ? $this->getLinkTarget() : $this->getPluginLinkTarget();
        $this->ctrl->setParameterByClass(ilObjOrgUnitGUI::class, 'ref_id', $temp);
        return $link_target;
    }

    protected function getLinkTarget(): string
    {
        if ($this->ctrl->getCmdClass() === strtolower(ilObjOrgUnitGUI::class) && in_array(
            $this->ctrl->getCmd(),
            $this->stay_with_command,
            true
        )) {
            return $this->ctrl->getLinkTargetByClass(
                array(ilAdministrationGUI::class, $this->ctrl->getCmdClass()),
                $this->ctrl->getCmd()
            );
        }

        return $this->ctrl->getLinkTargetByClass(array(ilAdministrationGUI::class, ilObjOrgUnitGUI::class), 'view');
    }

    /**
     * @throws ilCtrlException
     */
    protected function getPluginLinkTarget(): string
    {
        return $this->ctrl->getLinkTargetByClass(ilObjPluginDispatchGUI::class, 'forward');
    }

    /**
     * @param object|array $a_node
     * @return bool
     */
    public function isNodeClickable($a_node): bool
    {
        $node = $this->getNodeArrayRepresentation($a_node);

        $node = $this->getNodeArrayRepresentation($a_node);

        if ($this->access->checkAccess('read', '', $node['ref_id'])) {
            return true;
        }

        return false;
    }


    /**
     * @param $a_node
     * @return bool
     */
    public function isNodeSelectable($a_node): bool
    {
        $current_node = filter_input(INPUT_GET, 'item_ref_id') ?? ilObjOrgUnit::getRootOrgRefId();
        $node = $this->getNodeArrayRepresentation($a_node);

        return !($node['child'] === $current_node || $this->tree->isGrandChild($current_node, $node['child']));
    }

    /**
     * @param $a_node
     * @return array
     */
    private function getNodeArrayRepresentation($a_node): array
    {
        if (is_object($a_node)) {
            return (array) $a_node;
        }

        return $a_node;
    }

    public function getChildsOfNode($a_parent_node_id): array
    {
        $children = parent::getChildsOfNode($a_parent_node_id);
        return $this->filterChildrenByPermission($children);
    }

    protected function filterChildrenByPermission(array $children): array
    {
        return array_filter(
            $children,
            function ($child) {
                return $this->access->checkAccess("visible", "", $child["ref_id"]);
            }
        );
    }
}
