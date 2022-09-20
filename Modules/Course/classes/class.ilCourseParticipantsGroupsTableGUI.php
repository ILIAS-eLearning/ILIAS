<?php

declare(strict_types=0);
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

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilCourseParticipantsGroupsTableGUI extends ilTable2GUI
{
    protected int $ref_id;
    protected int $obj_id;
    protected array $filter = [];
    protected array $groups = [];
    protected array $groups_rights = [];
    protected array $participants = [];

    protected ilAccessHandler $access;
    protected ilTree $tree;

    /**
     * Constructor
     */
    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $ref_id)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();

        $this->ref_id = $ref_id;
        $this->obj_id = ilObject::_lookupObjId($ref_id);

        $this->setId('tblcrsprtgrp_' . $ref_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->lng->loadLanguageModule('grp');
        $this->setLimit(9999);

        $this->setTitle($this->lng->txt('crs_grp_assignments'));

        $this->addColumn('', '', '0');
        $this->addColumn($this->lng->txt("name"), "name", '35%');
        $this->addColumn($this->lng->txt("login"), "login", '35%');
        $this->addColumn($this->lng->txt("crs_groups_nr"), "groups_number");
        $this->addColumn($this->lng->txt("groups"));

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
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
            if ($selectable_groups !== []) {
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
    public function initGroups(): void
    {
        $parent_node = $this->tree->getNodeData($this->ref_id);
        $groups = $this->tree->getSubTree($parent_node, true, ['grp']);
        if (is_array($groups) && count($groups)) {
            $this->participants = $this->groups = $this->groups_rights = array();
            foreach ($groups as $idx => $group_data) {
                // check for group in group
                if ($group_data["parent"] != $this->ref_id && $this->tree->checkForParentType(
                    $group_data["ref_id"],
                    "grp",
                    true
                )) {
                    unset($groups[$idx]);
                } else {
                    $this->groups[$group_data["ref_id"]] = $group_data["title"];
                    $this->groups_rights[$group_data["ref_id"]]["manage_members"] = $this->access->checkRbacOrPositionPermissionAccess(
                        'manage_members',
                        'manage_members',
                        $group_data['ref_id']
                    );

                    $this->groups_rights[$group_data["ref_id"]]["edit_permission"] = $this->access->checkAccess(
                        "edit_permission",
                        "",
                        $group_data["ref_id"]
                    );

                    $gobj = ilGroupParticipants::_getInstanceByObjId($group_data["obj_id"]);

                    $members = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                        'manage_members',
                        'manage_members',
                        $group_data['ref_id'],
                        $gobj->getMembers()
                    );
                    $admins = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
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
    public function initFilter(): void
    {
        $item = $this->addFilterItemByMetaType("name", ilTable2GUI::FILTER_TEXT);
        $this->filter["name"] = $item->getValue();

        if ($this->groups) {
            $item = $this->addFilterItemByMetaType("group", ilTable2GUI::FILTER_SELECT);
            $item->setOptions(array("" => $this->lng->txt("all")) + $this->groups);
            $this->filter["group"] = $item->getValue();
        }
    }

    public function getItems(): void
    {
        if ($this->groups) {
            $part = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
            $members = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'manage_members',
                'manage_members',
                $this->ref_id,
                $part->getMembers()
            );

            if ($members !== []) {
                $usr_data = array();
                foreach ($members as $usr_id) {
                    $name = ilObjUser::_lookupName($usr_id);
                    $user_groups = array("members" => array(), "admins" => array());
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

                $usr_data = array_slice($usr_data, $this->getOffset(), $this->getLimit());
                $this->setMaxCount(count($members));
                $this->setData($usr_data);
            }
        }
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("VAL_ID", $a_set["usr_id"]);

        $this->tpl->setVariable("TXT_USER", $a_set["name"]);
        $this->tpl->setVariable("TXT_LOGIN", $a_set["login"]);
        $this->tpl->setVariable("VAL_GROUP_NUMBER", $a_set["groups_number"]);

        if (count($a_set["groups"]) !== 0) {
            foreach ($a_set["groups"] as $type => $groups) {
                foreach ($groups as $grp_id => $title) {
                    if (
                        ($type == "admins" && $this->groups_rights[$grp_id]["edit_permission"]) ||
                        ($type == "members" && $this->groups_rights[$grp_id]["manage_members"])
                    ) {
                        $this->tpl->setCurrentBlock("groups_remove");

                        $this->tpl->setVariable("TXT_GROUP_REMOVE", $this->lng->txt("grp_unsubscribe"));

                        $this->ctrl->setParameter($this->parent_obj, "usr_id", $a_set["usr_id"]);
                        $this->ctrl->setParameter($this->parent_obj, "grp_id", $grp_id);
                        $this->tpl->setVariable(
                            "URL_REMOVE",
                            $this->ctrl->getLinkTarget($this->parent_obj, "confirmremove")
                        );
                        $this->ctrl->setParameter($this->parent_obj, "grp_id", "");
                        $this->ctrl->setParameter($this->parent_obj, "usr_id", "");

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
