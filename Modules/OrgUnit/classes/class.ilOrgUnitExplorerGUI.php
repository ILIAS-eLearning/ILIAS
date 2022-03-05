<?php
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

    public function __construct(string $a_expl_id, string $a_parent_obj, string $a_parent_cmd, ilTree $a_tree)
    {
        parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree);
        $this->setAjax(true);
        $this->setTypeWhiteList(array(self::ORGU));
        $this->tree->initLangCode();
    }

    /**
     * Get content of a node
     * @param object|array $a_node node array or object
     * @return string content of the node
     */
    public function getNodeContent(object|array $a_node) : string
    {
        global $DIC;

        $node = $this->getNodeArrayRepresentation($a_node);

        if ($node['title'] === '__OrgUnitAdministration') {
            $node['title'] = $DIC->language()->txt('objs_orgu');
        }
        if ((int) $node['child'] === (int) $_GET['ref_id']) {
            return "<span class='ilExp2NodeContent ilHighlighted'>" . $node['title'] . '</span>';
        }

        return $node['title'];
    }

    public function getRootNode() : array
    {
        return $this->getTree()->getNodeData(ilObjOrgUnit::getRootOrgRefId());
    }

    /**
     * Get node icon
     * Return custom icon of OrgUnit type if existing
     * @return string
     */
    public function getNodeIcon(array|object $a_node) : string
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        $node = $this->getNodeArrayRepresentation($a_node);

        $obj_id = 0;
        if ($ilias->getSetting('custom_icons')) {
            $icons_cache = ilObjOrgUnit::getIconsCache();
            $obj_id = ilObject::_lookupObjId($node["child"]);
            if (isset($icons_cache[$obj_id])) {
                return $icons_cache[$obj_id];
            }
        }

        return ilObject::_getIcon($obj_id, "tiny", $node["type"]);
    }

    public function getNodeHref(array|object $a_node) : string
    {
        global $DIC;

        $node = $this->getNodeArrayRepresentation($a_node);

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

    protected function getLinkTarget() : string
    {
        global $DIC;
        if ($DIC->ctrl()->getCmdClass() === strtolower(ilObjOrgUnitGUI::class) && in_array($DIC->ctrl()->getCmd(),
                $this->stay_with_command, true)) {
            return $DIC->ctrl()->getLinkTargetByClass(array(ilAdministrationGUI::class, $DIC->ctrl()->getCmdClass()),
                $DIC->ctrl()->getCmd());
        }

        return $DIC->ctrl()->getLinkTargetByClass(array(ilAdministrationGUI::class, ilObjOrgUnitGUI::class), 'view');
    }

    protected function getPluginLinkTarget() : string
    {
        global $DIC;

        return $DIC->ctrl()->getLinkTargetByClass(ilObjPluginDispatchGUI::class, 'forward');
    }

    public function isNodeClickable(object|array $a_node) : bool
    {
        global $DIC;
        $ilAccess = $DIC->access();

        $node = $this->getNodeArrayRepresentation($a_node);

        if ($ilAccess->checkAccess('read', '', $node['ref_id'])) {
            return true;
        }

        return false;
    }

    public function isNodeSelectable(object|array $a_node) : bool
    {
        $current_node = filter_input(INPUT_GET, 'item_ref_id') ?? ilObjOrgUnit::getRootOrgRefId();
        $node = $this->getNodeArrayRepresentation($a_node);

        return !($node['child'] === $current_node || $this->tree->isGrandChild($current_node, $node['child']));
    }

    private function getNodeArrayRepresentation(object|array $a_node): array {
        if(is_object($a_node)) {
            return (array) $a_node;
        }

        return $a_node;
    }
}
