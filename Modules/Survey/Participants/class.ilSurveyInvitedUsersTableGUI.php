<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyInvitedUsersTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $DIC;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$lng = $DIC->language();
		$ilCtrl = $DIC->ctrl();

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->counter = 1;
		
		$this->setFormName('invitedusers');
		$this->setStyle('table', 'fullwidth');

		$this->addColumn('','f','1%');
		$this->addColumn($this->lng->txt("login"),'login', '');
		$this->addColumn($this->lng->txt("firstname"),'firstname', '');
		$this->addColumn($this->lng->txt("lastname"),'lastname', '');
	
		$this->setTitle($this->lng->txt('invited_users'), 'icon_usr.svg', $this->lng->txt('usr'));
	
		$this->setRowTemplate("tpl.il_svy_svy_invite_users_row.html", "Modules/Survey");

		$this->addMultiCommand('disinviteUserGroup', $this->lng->txt('disinvite'));

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->setDefaultOrderField("login");
		$this->setDefaultOrderDirection("asc");
		$this->setPrefix('user_select');
		$this->setSelectAllCheckbox('user_select');
		
		$this->enable('header');
		$this->disable('sort');
		$this->enable('select_all');
	}

	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($data)
	{
		$this->tpl->setVariable("USER_ID", $data['usr_id']);
		$this->tpl->setVariable("LOGIN", $data['login']);
		$this->tpl->setVariable("FIRSTNAME", $data['firstname']);
		$this->tpl->setVariable("LASTNAME", $data['lastname']);
	}
}
?>