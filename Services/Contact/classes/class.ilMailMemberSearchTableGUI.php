<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 *
 * @author Nadia Matuschek <nmatuschek@databay.de>
 * @version $Id$
 */
class ilMailMemberSearchTableGUI extends ilTable2GUI
{
	/**
	 * @param object	parent object
	 */
	public function __construct($a_parent_obj)
	{
		global $ilCtrl, $lng;
		$obj_id = ilObject::_lookupObjectId($a_parent_obj->ref_id);
		$this->setId('mmsearch_'.$obj_id);
		parent::__construct($a_parent_obj, 'showSelectableUsers');
		$lng->loadLanguageModule('crs');
		$lng->loadLanguageModule('grp');
		$this->setTitle($lng->txt('members'));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$ilCtrl->clearParameters($a_parent_obj);

		$this->setRowTemplate('tpl.mail_member_search_row.html', 'Services/Contact');

		// setup columns
		$this->addColumn('', '', '1%', true);
		$this->addColumn($lng->txt('login'), 'login', '22%');
		$this->addColumn($lng->txt('name'), 'name', '22%');
		$this->addColumn($lng->txt('role'), 'role', '22%');

		$this->setSelectAllCheckbox('user_ids[]');
		$this->setShowRowsSelector(true);
		
		$this->addMultiCommand('sendMailToSelectedUsers', $lng->txt('mail_members'));
		$this->addCommandButton('cancel', $lng->txt('cancel'));
	}

	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set)
	{
		foreach($a_set as $key => $value)
		{
			$this->tpl->setVariable(strtoupper($key), $value);
		}
	}
} 
