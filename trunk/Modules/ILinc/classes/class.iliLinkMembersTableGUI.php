<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * iliLinkMembersTableGUI
 *
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @extends ilTable2GUI
 */
class iliLinkMembersTableGUI extends ilTable2GUI
{
	protected $type = 'show';
	
	public function __construct($a_parent_obj, $a_data, $a_type, $a_cmd, $a_default_form_action)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_cmd);
		
		$this->type = $a_type;		
		$this->setData($a_data);
		
		if($this->type == 'show')
		{		
			if($ilAccess->checkAccess('write', '', $a_parent_obj->object->getRefId()))
			{
				$this->addColumn('', 'f', '1');
				if(is_array($a_data) && count($a_data))
				{
					$this->addMultiCommand('removeMember', $lng->txt('remove'));
					$this->addMultiCommand('changeMember', $lng->txt('change'));				
					$this->enable('select_all');
					$this->setSelectAllCheckbox('user_id[]');
				}
			}		
		
		 	$this->addColumn($lng->txt('username'), 'login', '20%');
		 	$this->addColumn($lng->txt('firstname'), 'firstname', '15%');
			$this->addColumn($lng->txt('lastname'), 'username', '15%');
			$this->addColumn($lng->txt('ilinc_coursemember_status'), 'ilinc_coursemember_status', '20%');
			$this->addColumn($lng->txt('role'), 'role', '20%');
			$this->addColumn($lng->txt('grp_options'), 'functions', '10%');
			
			$this->setRowTemplate('tpl.icrs_members_row.html', 'Modules/ILinc');
		}
		else if($this->type == 'change')
		{
			$this->addColumn($lng->txt('username'), 'login', '20%');
		 	$this->addColumn($lng->txt('firstname'), 'firstname', '15%');
			$this->addColumn($lng->txt('lastname'), 'username', '15%');
			$this->addColumn($lng->txt('ilinc_coursemember_status'), 'ilinc_coursemember_status', '20%');
			$this->addColumn($lng->txt('role'), 'role', '30%');
			
			$this->addCommandButton('members', $this->lng->txt('back'));
			if(is_array($a_data) && count($a_data))
			{				
				$this->addCommandButton('updateMemberStatus', $this->lng->txt('confirm'));
			}
			
			$this->setRowTemplate('tpl.icrs_members_change_row.html', 'Modules/ILinc');
			$this->setLimit(32000);
		}
		
		$this->enable('sort');
		$this->enable('header');
		$this->enable('numinfo');
		
		$this->setPrefix('members');		
		$this->setFormName('members');
			
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_default_form_action));			
	}
}
?>