<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for role assignment in user administration
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesUser
*/
class ilRoleAssignmentTableGUI extends ilTable2GUI
{
    /**
     * @var ilPathGUI
     */
    protected $path_gui;
    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];

        $lng->loadLanguageModule('rbac');
        $this->setId("usrroleass");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("role_assignment"));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->setDisableFilterHiding(true);
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("role"), "title");
        $this->addColumn($this->lng->txt("description"), "description");
        $this->addColumn($this->lng->txt("context"), "context");
        $this->addColumn($this->lng->txt('path'), 'path');
        $this->initFilter();
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.role_assignment_row.html", "Services/User");
        $this->setEnableTitle(true);

        if ($rbacsystem->checkAccess("edit_roleassignment", USER_FOLDER_ID)) {
            $this->setSelectAllCheckbox("role_id[]");
            $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
            $this->addMultiCommand("assignSave", $lng->txt("change_assignment"));
        }

        $this->path_gui = new ilPathGUI();
        $this->getPathGUI()->enableTextOnly(false);
        $this->getPathGUI()->enableHideLeaf(false);
    }

    /**
     * @return ilPathGUI
     */
    public function getPathGUI()
    {
        return $this->path_gui;
    }
    
    /**
    * Init filter
    */
    public function initFilter()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        // roles
        $option[0] = $lng->txt('assigned_roles');
        $option[1] = $lng->txt('all_roles');
        $option[2] = $lng->txt('all_global_roles');
        $option[3] = $lng->txt('all_local_roles');
        $option[4] = $lng->txt('internal_local_roles_only');
        $option[5] = $lng->txt('non_internal_local_roles_only');

        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new ilSelectInputGUI($lng->txt("roles"), "role_filter");
        $si->setOptions($option);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["role_filter"] = $si->getValue();
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        global $DIC;

        $lng = $DIC['lng'];

        if (isset($a_set['checkbox']['id'])) {
            $this->tpl->setVariable('VAL_ID', $a_set['checkbox']['id']);
            if ($a_set['checkbox']['disabled']) {
                $this->tpl->setVariable('VAL_DISABLED', 'disabled="disabled"');
            }
            if ($a_set['checkbox']['checked']) {
                $this->tpl->setVariable('VAL_CHECKED', 'checked="checked"');
            }
        }
        $this->tpl->setVariable("ROLE", $a_set["role"]);
        $this->tpl->setVariable("DESCRIPTION", $a_set["description"]);
        $this->tpl->setVariable("CONTEXT", $a_set["context"]);
        $this->tpl->setVariable('PATH', $a_set['path']);
    }

    public function parse(int $usr_id)
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $tree = $DIC->repositoryTree();
        $ilUser = $DIC->user();


        // now get roles depending on filter settings
        $role_list = $rbacreview->getRolesByFilter($this->filter["role_filter"], $usr_id);
        $assigned_roles = $rbacreview->assignedRoles($usr_id);

        $counter = 0;

        $records = [];
        foreach ($role_list as $role) {
            // fetch context path of role
            $rolf = $rbacreview->getFoldersAssignedToRole($role["obj_id"], true);
            $ref_id = $rbacreview->getObjectReferenceOfRole($role['rol_id']);

            // only list roles that are not set to status "deleted"
            if ($rbacreview->isDeleted($rolf[0])) {
                continue;
            }

            $path = "";
            if ($tree->isInTree($rolf[0])) {
                if ($rolf[0] == ROLE_FOLDER_ID) {
                    $path = $this->lng->txt("global");
                } else {
                    $tmpPath = $tree->getPathFull($rolf[0]);
                    $path = $this->getTitleForReference($ref_id);
                }
            } else {
                $path = "<b>Rolefolder " . $rolf[0] . " not found in tree! (Role " . $role["obj_id"] . ")</b>";
            }

            $disabled = false;
            // disable checkbox for system role for the system user
            if (
                ($usr_id == SYSTEM_USER_ID && $role["obj_id"] == SYSTEM_ROLE_ID) ||
                (!in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId())) && $role["obj_id"] == SYSTEM_ROLE_ID)
            ) {
                $disabled = true;
            }

            // protected admin role
            if ($role['obj_id'] == SYSTEM_ROLE_ID && !$rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID)) {
                if (ilSecuritySettings::_getInstance()->isAdminRoleProtected()) {
                    $disabled = true;
                }
            }

            if (substr($role["title"], 0, 3) == "il_") {
                if (!$assignable) {
                    $rolf_arr = $rbacreview->getFoldersAssignedToRole($role["obj_id"], true);
                    $rolf2 = $rolf_arr[0];
                } else {
                    $rolf2 = $rolf;
                }

                $parent_node = $tree->getNodeData($rolf2);

                $role["description"] = $this->lng->txt("obj_" . $parent_node["type"]) . "&nbsp;(#" . $parent_node["obj_id"] . ")";
            }

            $role_ids[$counter] = $role["obj_id"];


            $checkbox = [
                'id' => $role['obj_id'],
                'disabled' => $disabled,
                'checked' => in_array($role['obj_id'], $assigned_roles)
            ];
            $this->ctrl->setParameterByClass("ilobjrolegui", "ref_id", $rolf[0]);
            $this->ctrl->setParameterByClass("ilobjrolegui", "obj_id", $role["obj_id"]);
            $result_set[$counter][] = $link = "<a href=\"" . $this->ctrl->getLinkTargetByClass("ilobjrolegui", "perm") . "\">" . ilObjRole::_getTranslation($role["title"]) . "</a>";
            $title = ilObjRole::_getTranslation($role["title"]);
            $result_set[$counter][] = $role["description"];

            // Add link to objector local Rores
            if ($role["role_type"] == "local") {
                $result_set[$counter][] = $context = "<a href='" . ilLink::_getLink(
                    $ref_id,
                    ilObject::_lookupType(ilObject::_lookupObjId($ref_id))
                ) . "' target='_top'>" . $path . "</a>";
            } else {
                $result_set[$counter][] = $path;
                $context = $path;
            }

            $records[] = [
                "path" => $path,
                "description" => $role["description"],
                "context" => $context,
                "checkbox" => $checkbox,
                "role" => $link,
                "title" => $title,
                'path' => $this->getPathGUI()->getPath(ROOT_FOLDER_ID, $ref_id)
            ];
            ++$counter;
        }
        $this->setData($records);
    }

    /**
     * @param int $ref_id
     * @return string
     */
    protected function getTitleForReference(int $ref_id) : string
    {
        $type = ilObject::_lookupType($ref_id, true);
        $obj_id = ilObject::_lookupObjId($ref_id);
        $title = ilObject::_lookupTitle($obj_id);

        $list = ilObjectListGUIFactory::_getListGUIByType($type);
        $list->initItem(
            $ref_id,
            $obj_id,
            $type,
            $title
        );

        ilDatePresentation::setUseRelativeDates(false);
        $title = $list->getTitle();
        ilDatePresentation::resetToDefaults();
        return (string) $title;
    }

}
