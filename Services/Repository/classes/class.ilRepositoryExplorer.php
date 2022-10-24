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

/*
 * Repository Explorer
 *
 * @author Alexander Killing <killing@leifos.de>
 * @deprecated
 * only use seems to be ilPasteIntoMultipleItemsExplorer
 * which is still used in repository and workspace.
 */
class ilRepositoryExplorer extends ilExplorer
{
    protected ilSetting $settings;
    protected ilDBInterface $db;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected ilCtrl $ctrl;
    protected array $force_open_path;
    protected StandardGUIRequest $request;
    protected array $session_materials;
    protected array $item_group_items;
    protected array $type_grps;

    public function __construct(string $a_target, int $a_top_node = 0)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->obj_definition = $DIC["objDefinition"];
        $this->rbacsystem = $DIC->rbac()->system();
        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilSetting = $DIC->settings();
        $objDefinition = $DIC["objDefinition"];

        $this->ctrl = $ilCtrl;


        $this->force_open_path = [];
        $this->request = $DIC->repository()->internal()->gui()->standardRequest();


        parent::__construct($a_target);
        $this->tree = $tree;
        $this->root_id = $this->tree->readRootId();
        $this->order_column = "title";
        $this->setSessionExpandVariable("repexpand");
        $this->setTitle($lng->txt("overview"));

        // please do not uncomment this
        if ($ilSetting->get("repository_tree_pres") == "" ||
            ($ilSetting->get("rep_tree_limit_grp_crs") && $a_top_node === 0)) {
            foreach ($objDefinition->getExplorerContainerTypes() as $type) {
                $this->addFilter($type);
            }
            $this->setFiltered(true);
            $this->setFilterMode(IL_FM_POSITIVE);
        } elseif ($ilSetting->get("repository_tree_pres") === "all_types") {
            foreach ($objDefinition->getAllRBACObjects() as $rtype) {
                $this->addFilter($rtype);
            }
            $this->setFiltered(true);
            $this->setFilterMode(IL_FM_POSITIVE);
        }
    }

    /**
     * set force open path
     */
    public function setForceOpenPath(array $a_path): void
    {
        $this->force_open_path = $a_path;
    }

    /**
    * note: most of this stuff is used by ilCourseContentInterface too
    */
    public function buildLinkTarget($a_node_id, string $a_type): string
    {
        $ilCtrl = $this->ctrl;

        $ref_id = $this->request->getRefId();

        switch ($a_type) {
            case "cat":
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
                return $link;

            case "grpr":
            case "crsr":
            case "catr":
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "redirect");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
                return $link;

            case "grp":
                $ilCtrl->setParameterByClass("ilobjgroupgui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass(["ilrepositorygui", "ilobjgroupgui"], "");
                $ilCtrl->setParameterByClass("ilobjgroupgui", "ref_id", $ref_id);
                return $link;

            case "crs":
                $ilCtrl->setParameterByClass("ilobjcoursegui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass(["ilrepositorygui", "ilobjcoursegui"], "view");
                $ilCtrl->setParameterByClass("ilobjcoursegui", "ref_id", $ref_id);
                return $link;

            case 'rcrs':
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "infoScreen");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
                return $link;

            case 'prg':
                $ilCtrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilobjstudyprogrammegui", "view");
                $ilCtrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $ref_id);
                return $link;

            default:
                return ilLink::_getStaticLink($a_node_id, $a_type, true);

        }
    }

    public function getImage(string $a_name, string $a_type = "", $a_obj_id = ""): string
    {
        if ($a_type !== "") {
            return ilObject::_getIcon((int) $a_obj_id, "tiny", $a_type);
        }

        return parent::getImage($a_name);
    }

    public function isClickable(string $type, int $ref_id = 0): bool
    {
        $rbacsystem = $this->rbacsystem;
        $ilDB = $this->db;

        $obj_id = ilObject::_lookupObjId($ref_id);
        if (!ilConditionHandler::_checkAllConditionsOfTarget(
            $ref_id,
            $obj_id
        )) {
            return false;
        }

        switch ($type) {
            case 'tst':
                if (!$rbacsystem->checkAccess("read", $ref_id)) {
                    return false;
                }

                $query = sprintf("SELECT * FROM tst_tests WHERE obj_fi=%s", $obj_id);
                $res = $ilDB->query($query);
                while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                    return (bool) $row->complete;
                }
                return false;

            case 'svy':
                if (!$rbacsystem->checkAccess("read", $ref_id)) {
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
                if ($rbacsystem->checkAccess("read", $ref_id)) {
                    return true;
                }
                return false;
            case 'grpr':
            case 'crsr':
            case 'catr':
                return ilContainerReferenceAccess::_isAccessible($ref_id);
            case 'prg':
                    return $rbacsystem->checkAccess("visible", $ref_id);



            // all other types are only clickable, if read permission is given
            default:
                if ($rbacsystem->checkAccess("read", $ref_id)) {
                    // check if lm is online
                    if ($type === "lm") {
                        $lm_obj = new ilObjLearningModule($ref_id);
                        if (($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $ref_id))) {
                            return false;
                        }
                    }
                    // check if fblm is online
                    if ($type === "htlm") {
                        $lm_obj = new ilObjFileBasedLM($ref_id);
                        if (($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $ref_id))) {
                            return false;
                        }
                    }
                    // check if fblm is online
                    if ($type === "sahs") {
                        $lm_obj = new ilObjSAHSLearningModule($ref_id);
                        if (($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $ref_id))) {
                            return false;
                        }
                    }
                    // check if glossary is online
                    if ($type === "glo") {
                        $obj_id = ilObject::_lookupObjectId($ref_id);
                        if ((!ilObjGlossary::_lookupOnline($obj_id)) &&
                            (!$rbacsystem->checkAccess('write', $ref_id))) {
                            return false;
                        }
                    }

                    return true;
                }
                return false;
        }
    }

    /**
     * @param int|string $a_parent_id
     */
    public function showChilds($a_parent_id, int $a_obj_id = 0): bool
    {
        $rbacsystem = $this->rbacsystem;

        if ($a_parent_id == 0) {
            return true;
        }
        if (!ilConditionHandler::_checkAllConditionsOfTarget((int) $a_parent_id, $a_obj_id)) {
            return false;
        }
        if ($rbacsystem->checkAccess("read", (int) $a_parent_id)) {
            return true;
        }

        return false;
    }

    public function isVisible($a_ref_id, string $a_type): bool
    {
        $ilAccess = $this->access;
        $tree = $this->tree;
        $ilSetting = $this->settings;

        if (!$ilAccess->checkAccess('visible', '', $a_ref_id)) {
            return false;
        }

        $is_course = false;
        $container_parent_id = $tree->checkForParentType($a_ref_id, 'grp');
        if (!$container_parent_id) {
            $is_course = true;
            $container_parent_id = $tree->checkForParentType($a_ref_id, 'crs');
        }
        if ($container_parent_id) {
            // do not display session materials for container course/group
            if ($container_parent_id !== $a_ref_id && $ilSetting->get("repository_tree_pres") === "all_types") {
                // get container event items only once
                if (!isset($this->session_materials[$container_parent_id])) {
                    $this->session_materials[$container_parent_id] = ilEventItems::_getItemsOfContainer($container_parent_id);
                }
                // get item group items only once
                if (!isset($this->item_group_items[$container_parent_id])) {
                    $this->item_group_items[$container_parent_id] = ilItemGroupItems::_getItemsOfContainer($container_parent_id);
                }
                if (in_array($a_ref_id, $this->session_materials[$container_parent_id])) {
                    return false;
                }
                if (in_array($a_ref_id, $this->item_group_items[$container_parent_id])) {
                    return false;
                }
            }
        }

        return true;
    }


    public function formatHeader(ilTemplate $tpl, $a_obj_id, array $a_option): void
    {
        $lng = $this->lng;
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;

        // custom icons
        $path = ilObject::_getIcon((int) $a_obj_id, "tiny", "root");

        $tpl->setCurrentBlock("icon");
        $nd = $tree->getNodeData(ROOT_FOLDER_ID);
        $title = $nd["title"];
        if ($title === "ILIAS") {
            $title = $lng->txt("repository");
        }

        $tpl->setVariable("ICON_IMAGE", $path);
        $tpl->setVariable("TXT_ALT_IMG", $lng->txt("icon") . " " . $title);
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("link");
        $tpl->setVariable("TITLE", $title);
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", "1");
        $tpl->setVariable(
            "LINK_TARGET",
            $ilCtrl->getLinkTargetByClass("ilrepositorygui", "")
        );
        $ilCtrl->setParameterByClass(
            "ilrepositorygui",
            "ref_id",
            $this->request->getRefId()
        );
        $tpl->setVariable("TARGET", " target=\"_top\"");
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("element");
        $tpl->parseCurrentBlock();
    }

    public function sortNodes(array $a_nodes, $a_parent_obj_id): array
    {
        $objDefinition = $this->obj_definition;

        if ($a_parent_obj_id > 0) {
            $parent_type = ilObject::_lookupType($a_parent_obj_id);
        } else {
            $parent_type = "dummy";
            $this->type_grps["dummy"] = ["root" => "dummy"];
        }

        if (empty($this->type_grps[$parent_type])) {
            $this->type_grps[$parent_type] = $objDefinition->getGroupedRepositoryObjectTypes($parent_type);
        }
        $group = [];

        foreach ($a_nodes as $node) {
            $g = $objDefinition->getGroupOfObj($node["type"]);
            if ($g == "") {
                $g = $node["type"];
            }
            $group[$g][] = $node;
        }

        $nodes = [];
        foreach ($this->type_grps[$parent_type] as $t => $g) {
            if (is_array($group[$t])) {
                // do we have to sort this group??
                $sort = ilContainerSorting::_getInstance($a_parent_obj_id);
                $group = $sort->sortItems($group);

                foreach ($group[$t] as $k => $item) {
                    $nodes[] = $item;
                }
            }
        }

        return $nodes;
    }

    public function forceExpanded($a_obj_id): bool
    {
        if (in_array($a_obj_id, $this->force_open_path)) {
            return true;
        }
        return false;
    }
}
