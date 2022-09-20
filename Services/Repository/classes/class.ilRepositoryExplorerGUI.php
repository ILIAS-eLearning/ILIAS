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
 *********************************************************************/

use ILIAS\Repository\StandardGUIRequest;

/**
 * Repository explorer GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 * @todo: isClickable, top node id
 */
class ilRepositoryExplorerGUI extends ilTreeExplorerGUI
{
    protected ilSetting $settings;
    protected ilObjectDefinition $obj_definition;
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected ilDBInterface $db;
    protected ilObjUser $user;
    protected array $type_grps = [];
    protected array $session_materials = [];
    protected array $parent_node_id = [];
    protected array $node_data = [];
    protected StandardGUIRequest $request;
    protected int $cur_ref_id = 0;
    protected int $top_node_id;

    /**
     * @param ilRepositoryExplorerGUI|string $a_parent_obj
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();
        $this->obj_definition = $DIC["objDefinition"];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $tree = $DIC->repositoryTree();
        $ilSetting = $DIC->settings();
        $objDefinition = $DIC["objDefinition"];
        $this->request = $DIC->repository()->internal()->gui()->standardRequest();

        $this->cur_ref_id = $this->request->getRefId();
        $this->top_node_id = self::getTopNodeForRefId($this->cur_ref_id);

        parent::__construct("rep_exp", $a_parent_obj, $a_parent_cmd, $tree);

        $this->setSkipRootNode(false);
        $this->setNodeOpen($this->tree->readRootId());
        $this->setAjax(true);
        $this->setOrderField("title");
        if ($ilSetting->get("repository_tree_pres") == "" ||
            ($ilSetting->get("rep_tree_limit_grp_crs") && $this->top_node_id === 0)) {
            $this->setTypeWhiteList($objDefinition->getExplorerContainerTypes());
        } elseif ($ilSetting->get("repository_tree_pres") === "all_types") {
            $white = [];
            foreach ($objDefinition->getSubObjectsRecursively("root") as $rtype) {
                if (/* $rtype["name"] != "itgr" && */ !$objDefinition->isSideBlock($rtype["name"])) {
                    $white[] = $rtype["name"];
                }
            }
            $this->setTypeWhiteList($white);
        }

        if ($this->cur_ref_id > 0) {
            $this->setPathOpen($this->cur_ref_id);
        }

        $this->setChildLimit((int) $ilSetting->get("rep_tree_limit_number"));
    }

    public function getRootNode()
    {
        if ($this->top_node_id > 0) {
            $root_node = $this->getTree()->getNodeData($this->top_node_id);
        } else {
            $root_node = parent::getRootNode();
        }
        $this->node_data[$root_node["child"]] = $root_node;
        return $root_node;
    }

    public function getNodeContent($a_node): string
    {
        $lng = $this->lng;

        $title = $a_node["title"];

        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            if ($title === "ILIAS") {
                $title = $lng->txt("repository");
            }
        } elseif ($a_node["type"] === "sess" &&
            !trim($title)) {
            // #14367 - see ilObjSessionListGUI
            $app_info = ilSessionAppointment::_lookupAppointment($a_node["obj_id"]);
            $title = ilSessionAppointment::_appointmentToString($app_info['start'], $app_info['end'], (bool) $app_info['fullday']);
        }
        return $title;
    }

    public function getNodeIcon($a_node): string
    {
        $obj_id = ilObject::_lookupObjId($a_node["child"]);
        return ilObject::_getIcon($obj_id, "tiny", $a_node["type"]);
    }

    public function getNodeIconAlt($a_node): string
    {
        $lng = $this->lng;

        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            $title = $a_node["title"];
            if ($title === "ILIAS") {
                $title = $lng->txt("repository");
            }
            return $title;
        }

        $lng = $this->lng;
        return $lng->txt("obj_" . $a_node["type"]) . ": " . $this->getNodeContent($a_node);
    }

    public function isNodeHighlighted($a_node): bool
    {
        if ((int) $a_node["child"] === $this->cur_ref_id ||
            ($this->cur_ref_id === 0 && (int) $a_node["child"] === (int) $this->getNodeId($this->getRootNode()))) {
            return true;
        }
        return false;
    }

    public function getNodeHref($a_node): string
    {
        $ilCtrl = $this->ctrl;

        switch ($a_node["type"]) {
            case "cat":
            case "root":
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node["child"]);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->cur_ref_id);
                return $link;

            case "grpr":
            case "crsr":
            case "prgr":
            case "catr":
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node["child"]);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "redirect");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->cur_ref_id);
                return $link;

            case "grp":
                $ilCtrl->setParameterByClass("ilobjgroupgui", "ref_id", $a_node["child"]);
                $link = $ilCtrl->getLinkTargetByClass(["ilrepositorygui", "ilobjgroupgui"], "");
                $ilCtrl->setParameterByClass("ilobjgroupgui", "ref_id", $this->cur_ref_id);
                return $link;

            case "crs":
                $ilCtrl->setParameterByClass("ilobjcoursegui", "ref_id", $a_node["child"]);
                $link = $ilCtrl->getLinkTargetByClass(["ilrepositorygui", "ilobjcoursegui"], "view");
                $ilCtrl->setParameterByClass("ilobjcoursegui", "ref_id", $this->cur_ref_id);
                return $link;

            case 'rcrs':
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node["child"]);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "infoScreen");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->cur_ref_id);
                return $link;

            case 'prg':
                $ilCtrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $a_node["child"]);
                $link = $ilCtrl->getLinkTargetByClass(["ilrepositorygui", "ilobjstudyprogrammegui"], "view");
                $ilCtrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $this->cur_ref_id);
                return $link;

            default:
                return ilLink::_getStaticLink($a_node["child"], $a_node["type"], true);
        }
    }

    public function isNodeVisible($a_node): bool
    {
        $ilAccess = $this->access;
        $tree = $this->tree;
        $ilSetting = $this->settings;

        if (!$ilAccess->checkAccess('visible', '', $a_node["child"])) {
            return false;
        }

        if ($ilSetting->get("repository_tree_pres") === "all_types") {
            /*$container_parent_id = $tree->checkForParentType($a_node["child"], 'grp');
            if (!$container_parent_id) {
                $container_parent_id = $tree->checkForParentType($a_node["child"], 'crs');
            }*/
            // see #21215
            $container_parent_id = $this->getParentCourseOrGroup($a_node["child"]);
            if ($container_parent_id > 0) {
                // do not display session materials for container course/group
                if ($container_parent_id !== (int) $a_node["child"]) {
                    // get container event items only once
                    if (!isset($this->session_materials[$container_parent_id])) {
                        $this->session_materials[$container_parent_id] = ilEventItems::_getItemsOfContainer($container_parent_id);
                    }
                    if (in_array($a_node["child"], $this->session_materials[$container_parent_id])) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function getParentCourseOrGroup(int $node_id): int
    {
        $current_node_id = $node_id;
        while (isset($this->parent_node_id[$current_node_id])) {
            $parent_node_id = $this->parent_node_id[$current_node_id];
            if (isset($this->node_data[$parent_node_id]) && in_array($this->node_data[$parent_node_id]["type"], ["grp", "crs"])) {
                return $parent_node_id;
            }
            $current_node_id = $parent_node_id;
        }
        return 0;
    }


    public function sortChilds(array $a_childs, $a_parent_node_id): array
    {
        $objDefinition = $this->obj_definition;
        $ilAccess = $this->access;

        $parent_obj_id = ilObject::_lookupObjId((int) $a_parent_node_id);
        if ($parent_obj_id > 0) {
            $parent_type = ilObject::_lookupType($parent_obj_id);
        } else {
            $parent_type = "dummy";
            $this->type_grps["dummy"] = ["root" => "dummy"];
        }

        // alex: if this is not initialized, things are messed up
        // see bug 0015978
        $this->type_grps = [];

        $this->type_grps[$parent_type] =
            $objDefinition::getGroupedRepositoryObjectTypes($parent_type);

        // #14465 - item groups
        $group = [];
        $igroup = []; // used for item groups, see bug #0015978
        $in_any_group = [];
        foreach ($a_childs as $child) {
            // item group: get childs
            if ($child["type"] === "itgr") {
                $g = $child["child"];
                $items = ilObjectActivation::getItemsByItemGroup($g);
                if ($items) {
                    // add item group ref id to item group block
                    $this->type_grps[$parent_type]["itgr"]["ref_ids"][] = $g;

                    // #16697 - check item group permissions
                    $may_read = $ilAccess->checkAccess('read', '', $g);

                    // see bug #0015978
                    if ($may_read) {
                        $items = ilContainerSorting::_getInstance($parent_obj_id)->sortSubItems('itgr', $child["obj_id"], $items);
                    }

                    foreach ($items as $item) {
                        $in_any_group[] = $item["child"];

                        if ($may_read) {
                            $igroup[$g][] = $item;
                            $group[$g][] = $item;
                        }
                    }
                }
            }
            // type group
            else {
                $g = $objDefinition->getGroupOfObj($child["type"]);
                if ($g == "") {
                    $g = $child["type"];
                }
                $group[$g][] = $child;
            }
        }

        $in_any_group = array_unique($in_any_group);

        // custom block sorting?
        $sort = ilContainerSorting::_getInstance($parent_obj_id);
        $block_pos = $sort->getBlockPositions();
        if (is_array($block_pos) && count($block_pos) > 0) {
            $tmp = $this->type_grps[$parent_type];

            $this->type_grps[$parent_type] = [];
            foreach ($block_pos as $block_type) {
                // type group
                if (!is_numeric($block_type) &&
                    array_key_exists($block_type, $tmp)) {
                    $this->type_grps[$parent_type][$block_type] = $tmp[$block_type];
                    unset($tmp[$block_type]);
                }
                // item group
                else {
                    // using item group ref id directly
                    $this->type_grps[$parent_type][$block_type] = [];
                }
            }

            // append missing
            if (count($tmp)) {
                foreach ($tmp as $block_type => $grp) {
                    $this->type_grps[$parent_type][$block_type] = $grp;
                }
            }

            unset($tmp);
        }

        $childs = [];
        $done = [];

        foreach ($this->type_grps[$parent_type] as $t => $g) {
            // type group
            if (isset($group[$t]) && is_array($group[$t])) {
                // see bug #0015978
                // custom sorted igroups
                if (isset($igroup[$t]) && is_array($igroup[$t])) {
                    foreach ($igroup[$t] as $k => $item) {
                        if (!in_array($item["child"], $done)) {
                            $childs[] = $item;
                            $done[] = $item["child"];
                        }
                    }
                } else {
                    // do we have to sort this group??
                    $sort = ilContainerSorting::_getInstance($parent_obj_id);
                    $group = $sort->sortItems($group);

                    // need extra session sorting here
                    if ($t === "sess") {
                        foreach ($group[$t] as $k => $v) {
                            $app_info = ilSessionAppointment::_lookupAppointment($v["obj_id"]);
                            $group[$t][$k]["start"] = $app_info["start"];
                        }
                        $group[$t] = ilArrayUtil::sortArray($group[$t], 'start', 'asc', true, false);
                    }

                    foreach ($group[$t] as $k => $item) {
                        if (!in_array($item["child"], $done) &&
                            !in_array($item["child"], $in_any_group)) { // #16697
                            $childs[] = $item;
                            $done[] = $item["child"];
                        }
                    }
                }
            }
            // item groups (if not custom block sorting)
            elseif ($t === "itgr" &&
                isset($g["ref_ids"]) &&
                is_array($g["ref_ids"])) {
                foreach ($g["ref_ids"] as $ref_id) {
                    if (isset($group[$ref_id])) {
                        foreach ($group[$ref_id] as $k => $item) {
                            if (!in_array($item["child"], $done)) {
                                $childs[] = $item;
                                $done[] = $item["child"];
                            }
                        }
                    }
                }
            }
        }

        return $childs;
    }

    /**
     * @param object|array $a_node
     * @return bool
     */
    public function nodeHasVisibleChilds($a_node): bool
    {
        if (!$this->obj_definition->isContainer($a_node["type"] ?? "")) {
            return false;
        }
        return parent::nodeHasVisibleChilds($a_node);
    }

    public function getChildsOfNode($a_parent_node_id): array
    {
        $rbacsystem = $this->rbacsystem;

        if (!$rbacsystem->checkAccess("read", $a_parent_node_id)) {
            return [];
        }

        $obj_id = ilObject::_lookupObjId($a_parent_node_id);
        if (!ilConditionHandler::_checkAllConditionsOfTarget($a_parent_node_id, $obj_id)) {
            return [];
        }

        $childs = parent::getChildsOfNode($a_parent_node_id);

        foreach ($childs as $c) {
            $this->parent_node_id[$c["child"]] = $a_parent_node_id;
            $this->node_data[$c["child"]] = $c;
        }

        return $childs;
    }

    public function isNodeClickable($a_node): bool
    {
        $rbacsystem = $this->rbacsystem;
        $ilDB = $this->db;

        $obj_id = ilObject::_lookupObjId($a_node["child"]);
        if (!ilConditionHandler::_checkAllConditionsOfTarget($a_node["child"], $obj_id)) {
            return false;
        }

        switch ($a_node["type"]) {
            case 'tst':
                if (!$rbacsystem->checkAccess("read", $a_node["child"])) {
                    return false;
                }

                $query = sprintf("SELECT * FROM tst_tests WHERE obj_fi=%s", $obj_id);
                $res = $ilDB->query($query);
                while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                    return (bool) $row->complete;
                }
                return false;

            case 'svy':
                if (!$rbacsystem->checkAccess("read", $a_node["child"])) {
                    return false;
                }

                $query = sprintf("SELECT * FROM svy_svy WHERE obj_fi=%s", $obj_id);
                $res = $ilDB->query($query);
                while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                    return (bool) $row->complete;
                }
                return false;

                // media pools can only be edited
            case "mep":
                if ($rbacsystem->checkAccess("read", $a_node["child"])) {
                    return true;
                }
                return false;
            case 'grpr':
            case 'crsr':
            case 'catr':
                return ilContainerReferenceAccess::_isAccessible($a_node["child"]);

            case 'prg':
                return $rbacsystem->checkAccess("read", $a_node["child"]);

                // all other types are only clickable, if read permission is given
            default:
                if ($rbacsystem->checkAccess("read", $a_node["child"])) {
                    // check if lm is online
                    if ($a_node["type"] === "lm") {
                        $lm_obj = new ilObjLearningModule($a_node["child"]);
                        if (($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $a_node["child"]))) {
                            return false;
                        }
                    }
                    // check if fblm is online
                    if ($a_node["type"] === "htlm") {
                        $lm_obj = new ilObjFileBasedLM($a_node["child"]);
                        if (($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $a_node["child"]))) {
                            return false;
                        }
                    }
                    // check if fblm is online
                    if ($a_node["type"] === "sahs") {
                        $lm_obj = new ilObjSAHSLearningModule($a_node["child"]);
                        if (($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $a_node["child"]))) {
                            return false;
                        }
                    }
                    // check if glossary is online
                    if ($a_node["type"] === "glo") {
                        $obj_id = ilObject::_lookupObjectId($a_node["child"]);
                        if ((!ilObjGlossary::_lookupOnline($obj_id)) &&
                            (!$rbacsystem->checkAccess('write', $a_node["child"]))) {
                            return false;
                        }
                    }

                    return true;
                }
                return false;
        }
    }

    public static function getTopNodeForRefId(int $ref_id): int
    {
        global $DIC;

        $setting = $DIC->settings();
        $tree = $DIC->repositoryTree();

        $top_node = 0;
        if ($ref_id > 0 && $setting->get("rep_tree_limit_grp_crs")) {
            $path = $tree->getPathId($ref_id);
            foreach ($path as $n) {
                if ($top_node > 0) {
                    break;
                }
                if (in_array(
                    ilObject::_lookupType(ilObject::_lookupObjId($n)),
                    ["crs", "grp"]
                )) {
                    $top_node = $n;
                }
            }
        }
        return $top_node;
    }
}
