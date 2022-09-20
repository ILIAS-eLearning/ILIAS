<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilTestInviteGroupsTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);

        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->counter = 1;

        $this->setFormName('invitegroups');
        $this->setStyle('table', 'fullwidth');

        $this->addColumn('', 'f', '1%');
        $this->addColumn($this->lng->txt("title"), 'title', '');
        $this->addColumn($this->lng->txt("description"), 'description', '');

        $this->setTitle($this->lng->txt('search_groups'), 'icon_grp.svg', $this->lng->txt('grp'));

        $this->setRowTemplate("tpl.il_as_tst_invite_groups_row.html", "Modules/Test");

        $this->addMultiCommand('addParticipants', $this->lng->txt('add'));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setDefaultOrderField("login");
        $this->setDefaultOrderDirection("asc");
        $this->setPrefix('group_select');
        $this->setSelectAllCheckbox('group_select');

        $this->enable('header');
        $this->enable('sort');
        $this->enable('select_all');
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("GROUP_ID", $a_set['ref_id']);
        $this->tpl->setVariable("TITLE", $a_set['title']);
        $this->tpl->setVariable("DESCRIPTION", $a_set['description']);
    }
}
