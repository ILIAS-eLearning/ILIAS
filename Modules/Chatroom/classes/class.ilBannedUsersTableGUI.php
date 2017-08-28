<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilBannedUsersTableGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilBannedUsersTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 * Calls parent constructor.
	 * Prepares ilBannedUsersTableGUI.
	 * @global ilLanguage      $lng
	 * @param ilObjChatroomGUI $a_parent_obj
	 * @param string           $a_parent_cmd
	 * @param string           $a_template_context
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
	{
		$this->setId('banned_users');

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $lng;

		$this->setTitle($lng->txt('ban_table_title'));
		$this->setExternalSegmentation(true);
		$this->setExternalSorting(false);

		$this->addColumn('', '', '', true);
		$this->addColumn($lng->txt('login'), 'login');
		$this->addColumn($lng->txt('firstname'), 'firstname');
		$this->addColumn($lng->txt('lastname'), 'lastname');
		$this->addColumn($lng->txt('chtr_ban_ts_tbl_head'), 'timestamp');
		$this->addColumn($lng->txt('chtr_ban_actor_tbl_head'), 'actor');

		$this->setSelectAllCheckbox('banned_user_id');
		$this->setRowTemplate('tpl.banned_user_table_row.html', 'Modules/Chatroom');

		$this->addMultiCommand('ban-delete', $lng->txt('unban'));
	}

	/**
	 * @inheritdoc
	 */
	protected function fillRow($a_set)
	{
		if($a_set['timestamp'] > 0)
		{
			$a_set['timestamp'] = ilDatePresentation::formatDate(new ilDateTime($a_set['timestamp'], IL_CAL_UNIX));
		}

		parent::fillRow($a_set);
	}
}