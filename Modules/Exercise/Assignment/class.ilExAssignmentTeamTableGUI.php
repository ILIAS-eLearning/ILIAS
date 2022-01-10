<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * List all team members of an assignment
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssignmentTeamTableGUI extends ilTable2GUI
{
    public const MODE_ADD = 1;
    public const MODE_EDIT = 2;

    protected ilAccessHandler $access;
    protected int $mode;
    protected ilExAssignmentTeam $team;
    protected bool $read_only;
    protected int $parent_ref_id;
    protected bool $edit_permission;
    protected array $member_ids;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_mode,
        int $a_parent_ref_id,
        ilExAssignmentTeam $a_team,
        bool $a_read_only = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();

        $access = $this->access;
        $user = $DIC->user();
        $this->edit_permission = (bool) $access->checkAccessOfUser($user->getId(), "edit", "", $a_parent_ref_id);

        $this->mode = $a_mode;
        $this->team = $a_team;
        $this->read_only = $a_read_only;
        $this->parent_ref_id = $a_parent_ref_id;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        if (!$this->read_only) {
            $this->addColumn("", "", 1);
        }
        $this->addColumn($this->lng->txt("name"), "name");
        
        $this->setDefaultOrderField("name");
                        
        $this->setRowTemplate("tpl.exc_team_member_row.html", "Modules/Exercise");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

        if (!$this->read_only) {
            if ($this->mode == self::MODE_ADD) {
                $this->setTitle($this->lng->txt("exc_team_member_container_add"));
                $this->addMultiCommand("addTeamMemberContainerAction", $this->lng->txt("add"));
            } else {
                $this->setTitle($this->lng->txt("exc_team_members"));
                $this->addMultiCommand("confirmRemoveTeamMember", $this->lng->txt("remove"));
            }
        }
        
        $this->getItems();
    }

    protected function getItems() : void
    {
        if ($this->mode == self::MODE_ADD) {
            $assigned = $this->team->getMembersOfAllTeams();
        } else {
            $assigned = array();
            $this->member_ids = $this->team->getMembers();
        }
    
        $data = array();
        foreach ($this->member_ids as $id) {
            if (!in_array($id, $assigned)) {
                $data[] = array("id" => $id,
                    "name" => ilUserUtil::getNamePresentation($id, false, false, "", $this->edit_permission));
            }
        }
        
        $this->setData($data);
    }

    protected function fillRow(array $a_set) : void
    {
        $ilAccess = $this->access;
        
        if (!$this->read_only) {
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        }
        $this->tpl->setVariable("TXT_NAME", $a_set["name"]);
        
        // #18327
        if (!$ilAccess->checkAccessOfUser($a_set["id"], "read", "", $this->parent_ref_id) &&
            is_array($info = $ilAccess->getInfo())) {
            $this->tpl->setCurrentBlock('access_warning');
            $this->tpl->setVariable('PARENT_ACCESS', $info[0]["text"]);
            $this->tpl->parseCurrentBlock();
        }
    }
}
