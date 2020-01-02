<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * name table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup Modules
 */
class ilCourseParticipantsGroupsTableGUI extends ilTable2GUI
{
    protected $filter;	      // array
    protected $groups;		  // array
    protected $groups_rights; // array
    protected $participants;  // array
    
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $ref_id)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule('grp');

        $this->ref_id = $ref_id;
        $this->obj_id = $ilObjDataCache->lookupObjId($this->ref_id);

        $this->setId('tblcrsprtgrp_' . $ref_id);

        parent::__construct($a_parent_obj, $a_parent_cmd);
        // $this->setTitle($lng->txt("tr_summary"));
        $this->setLimit(9999);
        // $this->setShowTemplates(true);
        
        $this->setTitle($this->lng->txt('crs_grp_assignments'));

        $this->addColumn('', '', 0);
        $this->addColumn($this->lng->txt("name"), "name", '35%');
        $this->addColumn($this->lng->txt("login"), "login", '35%');
        $this->addColumn($this->lng->txt("crs_groups_nr"), "groups_number");
        $this->addColumn($this->lng->txt("groups"));

        // $this->setExternalSorting(true);
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.crs_members_grp_row.html", "Modules/Course");
        $this->setSelectAllCheckbox("usrs");

        $this->initGroups();

        if ($this->groups) {
            $selectable_groups = [];
            foreach ($this->groups as $ref_id => $something) {
                if ($this->groups_rights[$ref_id]['manage_members']) {
                    $selectable_groups[$ref_id] = $this->groups[$ref_id];
                }
            }
            if (count($selectable_groups)) {
                $this->addMultiItemSelectionButton(
                    "grp_id",
                    $selectable_groups,
                    "add",
                    $this->lng->txt("crs_add_to_group")
                );
            }
            $this->initFilter();
        }
        
        
        
        $this->getItems();
    }

    /**
     * find groups in course, exclude groups in groups
     */
    public function initGroups()
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilAccess = $DIC['ilAccess'];
        
        $parent_node = $tree->getNodeData($this->ref_id);
        $groups = $tree->getSubTree($parent_node, true, "grp");
        if (is_array($groups) && sizeof($groups)) {
            include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
            $this->participants = $this->groups = $this->groups_rights = array();
            foreach ($groups as $idx => $group_data) {
                // check for group in group
                if ($group_data["parent"] != $this->ref_id  && $tree->checkForParentType($group_data["ref_id"], "grp", true)) {
                    unset($groups[$idx]);
                } else {
                    $this->groups[$group_data["ref_id"]] = $group_data["title"];
                    $this->groups_rights[$group_data["ref_id"]]["manage_members"] = (bool)
                        $GLOBALS['DIC']->access()->checkRbacOrPositionPermissionAccess(
                            'manage_members',
                            'manage_members',
                            $group_data['ref_id']
                        )
                    ;
                    
                    $this->groups_rights[$group_data["ref_id"]]["edit_permission"] = (bool)
                        $GLOBALS['DIC']->access()->checkAccess(
                            "edit_permission",
                            "",
                            $group_data["ref_id"]
                        );
                    
                    $gobj = ilGroupParticipants::_getInstanceByObjId($group_data["obj_id"]);
                    
                    $members = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
                        'manage_members',
                        'manage_members',
                        $group_data['ref_id'],
                        $gobj->getMembers()
                    );
                    $admins = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
                        'manage_members',
                        'manage_members',
                        $group_data['ref_id'],
                        $gobj->getAdmins()
                    );

                    $this->participants[$group_data["ref_id"]]["members"] = $members;
                    $this->participants[$group_data["ref_id"]]["admins"] = $admins;
                }
            }
        }
    }

    /**
    * Init filter
    */
    public function initFilter()
    {
        global $DIC;

        $lng = $DIC['lng'];

        $item = $this->addFilterItemByMetaType("name", ilTable2GUI::FILTER_TEXT);
        $this->filter["name"] = $item->getValue();

        if ($this->groups) {
            $item = $this->addFilterItemByMetaType("group", ilTable2GUI::FILTER_SELECT);
            $item->setOptions(array("" => $lng->txt("all"))+$this->groups);
            $this->filter["group"] = $item->getValue();
        }
    }

    /**
     * Build item rows for given object and filter(s)
     */
    public function getItems()
    {
        if ($this->groups) {
            include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
            $part = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
            $members = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
                'manage_members',
                'manage_members',
                $this->ref_id,
                $part->getMembers()
            );
            
            if (count($members)) {
                include_once './Services/User/classes/class.ilUserUtil.php';
                $usr_data = array();
                foreach ($members as $usr_id) {
                    $name = ilObjUser::_lookupName($usr_id);
                    // #9984
                    $user_groups = array("members"=>array(),"admins"=>array());
                    $user_groups_number = 0;
                    foreach (array_keys($this->participants) as $group_id) {
                        if (in_array($usr_id, $this->participants[$group_id]["members"])) {
                            $user_groups["members"][$group_id] = $this->groups[$group_id];
                            $user_groups_number++;
                        } elseif (in_array($usr_id, $this->participants[$group_id]["admins"])) {
                            $user_groups["admins"][$group_id] = $this->groups[$group_id];
                            $user_groups_number++;
                        }
                    }
                    
                    if ((!$this->filter["name"] || stristr(implode("", $name), $this->filter["name"])) &&
                        (!$this->filter["group"] || array_key_exists($this->filter["group"], $user_groups["members"]) ||
                        array_key_exists($this->filter["group"], $user_groups["admins"]))) {
                        $usr_data[] = array("usr_id" => $usr_id,
                            "name" => $name["lastname"] . ", " . $name["firstname"],
                            "groups" => $user_groups,
                            "groups_number" => $user_groups_number,
                            "login" => $name["login"]
                            );
                    }
                }

                // ???
                $usr_data = array_slice($usr_data, (int) $this->getOffset(), (int) $this->getLimit());

                $this->setMaxCount(sizeof($members));
                $this->setData($usr_data);
            }

            return $titles;
        }
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->tpl->setVariable("VAL_ID", $a_set["usr_id"]);

        $this->tpl->setVariable("TXT_USER", $a_set["name"]);
        $this->tpl->setVariable("TXT_LOGIN", $a_set["login"]);
        $this->tpl->setVariable("VAL_GROUP_NUMBER", $a_set["groups_number"]);

        if (sizeof($a_set["groups"])) {
            foreach ($a_set["groups"] as $type => $groups) {
                foreach ($groups as $grp_id => $title) {
                    if (
                        ($type == "admins" && $this->groups_rights[$grp_id]["edit_permission"]) ||
                        ($type == "members" && $this->groups_rights[$grp_id]["manage_members"])
                    ) {
                        $this->tpl->setCurrentBlock("groups_remove");

                        $this->tpl->setVariable("TXT_GROUP_REMOVE", $lng->txt("grp_unsubscribe"));

                        $ilCtrl->setParameter($this->parent_obj, "usr_id", $a_set["usr_id"]);
                        $ilCtrl->setParameter($this->parent_obj, "grp_id", $grp_id);
                        $this->tpl->setVariable("URL_REMOVE", $ilCtrl->getLinkTarget($this->parent_obj, "confirmremove"));
                        $ilCtrl->setParameter($this->parent_obj, "grp_id", "");
                        $ilCtrl->setParameter($this->parent_obj, "usr_id", "");

                        $this->tpl->parseCurrentBlock();
                    }

                    $this->tpl->setCurrentBlock("groups");
                    $this->tpl->setVariable("TXT_GROUP_TITLE", $title);
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
    }
}
