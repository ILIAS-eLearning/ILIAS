<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once './Services/Registration/classes/class.ilRegistrationSettings.php';

/**
* Class ilRegistrationSettingsGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
*
* @ilCtrl_Calls ilRegistrationSettingsGUI:
* 
* @ingroup ServicesRegistration
*/
class ilRegistrationSettingsGUI
{
	var $ctrl;
	var $tpl;
	var $ref_id;

	function ilRegistrationSettingsGUI()
	{
		global $ilCtrl,$tpl,$lng;

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('administration');
		$this->lng->loadLanguageModule('registration');

		$this->ref_id = (int) $_GET['ref_id'];

		$this->registration_settings = new ilRegistrationSettings();
	}

	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = 'diplayForm';
				}
				$this->$cmd();
				break;
		}
		return true;
	}
	
	function view()
	{
		global $ilAccess,$ilErr;

		if(!$ilAccess->checkAccess('read','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.registration_settings.html','Services/Registration');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_REGISTRATION_SETTINGS",$this->lng->txt('reg_settings_header'));
		$this->tpl->setVariable("TXT_REG_TYPE",$this->lng->txt('reg_type'));
		$this->tpl->setVariable("TXT_REG_DEACTIVATED",$this->lng->txt('reg_disabled'));
		$this->tpl->setVariable("REG_DEACTIVATED_DESC",$this->lng->txt('reg_disabled_info'));
		$this->tpl->setVariable("TXT_DIRECT",$this->lng->txt('reg_direct'));
		$this->tpl->setVariable("REG_DIRECT_DESC",$this->lng->txt('reg_direct_info'));
		$this->tpl->setVariable("TXT_APPROVE",$this->lng->txt('reg_approve'));
		$this->tpl->setVariable("REG_APPROVE_DESC",$this->lng->txt('reg_approve_info'));
		$this->tpl->setVariable("TXT_ROLE_ASSIGNMENT",$this->lng->txt('reg_role_assignment'));
		$this->tpl->setVariable("TXT_REG_FIXED",$this->lng->txt('reg_fixed'));
		$this->tpl->setVariable("TXT_AVAILABLE",$this->lng->txt('reg_available_roles'));
		$this->tpl->setVariable("TXT_APPROVE_REC",$this->lng->txt('approve_recipient'));
		$this->tpl->setVariable("TXT_REG_NOTIFICATION",$this->lng->txt('reg_notification'));
		$this->tpl->setVariable("REG_NOTIFICATION_DESC",$this->lng->txt('reg_notification_info'));
		$this->tpl->setVariable("TXT_REG_EMAIL",$this->lng->txt('reg_email'));
		
		$this->tpl->setVariable("TXT_REG_ACTIVATION_LINK",$this->lng->txt('reg_type_confirmation'));
		$this->tpl->setVariable("REG_INFO_ACTIVATION",$this->lng->txt('reg_type_confirmation_info'));		

		$this->tpl->setVariable("TXT_REG_ACCESS_LIMITATIONS",$this->lng->txt('reg_access_limitations'));
		$this->tpl->setVariable("TXT_ENABLE_ACCESS_LIMITATIONS",$this->lng->txt('reg_enable_access_limitations'));

		$this->tpl->setVariable("EDIT",$this->lng->txt('edit'));
		$this->tpl->setVariable("LINK_EDIT_FIXED",$this->ctrl->getLinkTarget($this,'editRoles'));
		$this->tpl->setVariable("LINK_EDIT_EMAIL",$this->ctrl->getLinkTarget($this,'editEmailAssignments'));
		$this->tpl->setVariable("LINK_EDIT_ACCESS_LIMITATIONS",$this->ctrl->getLinkTarget($this,'editRoleAccessLimitations'));

		$this->__prepareRoleList();
		$this->__prepareAutomaticRoleList();
		$this->__prepareAccessLimitationRoleList();

		// pwd forwarding
		$this->tpl->setVariable("TXT_REG_PWD_FORWARD",$this->lng->txt('passwd_generation'));
		$this->tpl->setVariable("REG_INFO_PWD",$this->lng->txt('reg_info_pwd'));

		$this->tpl->setVariable("RADIO_DEACTIVATE",ilUtil::formRadioButton(!$this->registration_settings->enabled(),
																		   'reg_type',
																		   IL_REG_DISABLED));
								
		$this->tpl->setVariable("RADIO_DIRECT",ilUtil::formRadioButton($this->registration_settings->directEnabled(),
																	   'reg_type',
																	   IL_REG_DIRECT));

		$this->tpl->setVariable("RADIO_APPROVE",ilUtil::formRadioButton($this->registration_settings->approveEnabled(),
																	   'reg_type',
																	   IL_REG_APPROVE));
																	   
		$this->tpl->setVariable("CHECK_ACTIVATION_REG",ilUtil::formRadioButton($this->registration_settings->activationEnabled(),
																	   'reg_type',
																	   IL_REG_ACTIVATION));

		$this->tpl->setVariable("APPROVER",ilUtil::prepareFormOutput($this->registration_settings->getApproveRecipientLogins()));


		$this->tpl->setVariable("CHECK_PWD",ilUtil::formCheckbox($this->registration_settings->passwordGenerationEnabled(),
																 'reg_pwd',
																 1));

		$this->tpl->setVariable("RADIO_FIXED",ilUtil::formRadioButton($this->registration_settings->roleSelectionEnabled(),
																	   'reg_role_type',
																	   IL_REG_ROLES_FIXED));

		$this->tpl->setVariable("RADIO_EMAIL",ilUtil::formRadioButton($this->registration_settings->automaticRoleAssignmentEnabled(),
																	   'reg_role_type',
																	   IL_REG_ROLES_EMAIL));
        // access limitation
        if ($this->registration_settings->getAccessLimitation())
        {
            $this->tpl->setVariable("REG_ACCESS_LIMITATION_CHECK","checked=\"checked\"");
            $this->tpl->setVariable("CSS_DISPLAY_ACCESS_LIMITATION","block");
        }
        else
        {
        	$this->tpl->setVariable("CSS_DISPLAY_ACCESS_LIMITATION","none");
        }
        
        $this->tpl->setVariable('REG_HASH_LIFE_TIME', (int)$this->registration_settings->getRegistrationHashLifetime());
        $this->tpl->setVariable('REG_HASH_LIFE_TIME_INFO', $this->lng->txt('reg_confirmation_hash_life_time_info'));
        

		$this->tpl->setVariable("TXT_SAVE",$this->lng->txt('save'));
	}

	function save()
	{
		global $ilAccess,$ilErr;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}

		$this->registration_settings->setRegistrationType((int) $_POST['reg_type']);
		$this->registration_settings->setPasswordGenerationStatus((int) $_POST['reg_pwd']);
		$this->registration_settings->setApproveRecipientLogins(ilUtil::stripSlashes($_POST['reg_approver']));
		$this->registration_settings->setRoleType((int) $_POST['reg_role_type']);
		$this->registration_settings->setAccessLimitation((int) $_POST['reg_access_limitation']);
		
		if(!preg_match('/^([0]|([1-9][0-9]*))([\.,][0-9][0-9]*)?$/', (int)$_POST['reg_hash_life_time']))
			$this->registration_settings->setRegistrationHashLifetime(0);
		else
			$this->registration_settings->setRegistrationHashLifetime((int)$_POST['reg_hash_life_time']);

		if($error_code = $this->registration_settings->validate())
		{
			ilUtil::sendInfo($this->lng->txt('reg_unknown_recipients').' '.$this->registration_settings->getUnknown());
			$this->view();
			return false;
		}
		
		$this->registration_settings->save();
		ilUtil::sendInfo($this->lng->txt('saved_successfully'));
		$this->view();

		return true;
	}

	function editRoles()
	{
		include_once './Services/AccessControl/classes/class.ilObjRole.php';

		global $ilAccess,$ilErr,$rbacreview;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.edit_roles.html','Services/Registration');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SELECTABLE_ROLES",$this->lng->txt('reg_selectable_roles'));
		$this->tpl->setVariable("ARR_DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("ACTIONS",$this->lng->txt('actions'));
		$this->tpl->setVariable("UPDATE",$this->lng->txt('save'));
		$this->tpl->setVariable("CANCEL",$this->lng->txt('cancel'));

		$counter = 0;
		foreach($rbacreview->getGlobalRoles() as $role)
		{
			if($role == SYSTEM_ROLE_ID or
			   $role == ANONYMOUS_ROLE_ID)
			{
				continue;
			}
			$this->tpl->setCurrentBlock("roles");
			$this->tpl->setVariable("CSSROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->setVariable("CHECK_ROLE",ilUtil::formCheckbox(ilObjRole::_lookupAllowRegister($role),
																	  "roles[$role]",
																	  1));
			$this->tpl->setVariable("ROLE",ilObjRole::_lookupTitle($role));
			$this->tpl->parseCurrentBlock();

		}
	}

	function updateRoles()
	{
		global $ilAccess,$ilErr,$rbacreview;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}
		// Minimum one role
		if(count($_POST['roles']) < 1)
		{
			ilUtil::sendInfo($this->lng->txt('msg_last_role_for_registration'));
			$this->editRoles();
			return false;
		}
		// update allow register
		foreach($rbacreview->getGlobalRoles() as $role)
		{
			if($role_obj = ilObjectFactory::getInstanceByObjId($role,false))
			{
				$role_obj->setAllowRegister($_POST['roles'][$role] ? 1 : 0);
				$role_obj->update();
			}
		}
		
		ilUtil::sendInfo($this->lng->txt('saved_successfully'));
		$this->view();

		return true;
	}

	function editEmailAssignments()
	{
		global $ilAccess,$ilErr,$rbacreview;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}

		$this->__initRoleAssignments();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.reg_email_role_assignments.html','Services/Registration');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_EMAIL_ROLE_ASSIGN",$this->lng->txt('reg_email_role_assignment'));
		$this->tpl->setVariable("TXT_MAIL",$this->lng->txt('reg_email'));
		$this->tpl->setVariable("TXT_ROLE",$this->lng->txt('obj_role'));
		$this->tpl->setVariable("TXT_DEFAULT",$this->lng->txt('reg_default'));
		$this->tpl->setVariable("ARR_DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("TXT_DOMAIN",$this->lng->txt('reg_domain'));

		
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));
		$this->tpl->setVariable("BTN_SAVE",$this->lng->txt('save'));
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('reg_add_assignment'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));

		$counter = 0;
		foreach($this->assignments_obj->getAssignments() as $assignment)
		{
			$this->tpl->setCurrentBlock("roles");
			$this->tpl->setVariable("CSSROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow1'));
			$this->tpl->setVariable("ASSIGN_ID",$assignment['id']);
			$this->tpl->setVariable("DOMAIN",$assignment['domain']);
			$this->tpl->setVariable("CHECK_ROLE",ilUtil::formCheckbox(0,'del_assign[]',$assignment['id']));
			$this->tpl->setVariable("ROLE_SEL",$this->__buildRoleSelection($assignment['id']));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("DEF_CSSROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow1'));
		$this->tpl->setVariable("TXT_DEFAULT",$this->lng->txt('default'));
		$this->tpl->setVariable("DEF_ROLE",$this->__buildRoleSelection(-1));
	}
	
	function editRoleAccessLimitations()
	{
		global $lng,$ilAccess,$ilErr,$rbacreview;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}

		$this->__initRoleAccessLimitations();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.reg_role_access_limitations.html','Services/Registration');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_REG_ROLE_ACCESS_LIMITATIONS",$lng->txt('reg_role_access_limitations'));
		$this->tpl->setVariable("TXT_ROLE",$lng->txt('obj_role'));
		$this->tpl->setVariable("TXT_ACCESS_LIMITATION_MODE",$lng->txt('reg_access_limitation_mode'));

		$this->tpl->setVariable("BTN_SAVE",$lng->txt('save'));
		$this->tpl->setVariable("BTN_CANCEL",$lng->txt('cancel'));

		$counter = 0;
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
			
		foreach(ilObjRole::_lookupRegisterAllowed() as $role)
		{
			$this->tpl->setCurrentBlock("roles");
			$this->tpl->setVariable("CSSROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow1'));
			$this->tpl->setVariable("ROLE_ID",$role['id']);
			$this->tpl->setVariable("ROLE_TITLE",$role['title']);
			$this->tpl->setVariable("SEL_ACCESS_LIMITATION",$this->__buildAccessLimitationSelection($role['id']));
			$this->tpl->setVariable("CSS_DISPLAY_ABSOLUTE",($this->access_limitations_obj->getMode($role['id']) == 'absolute') ? 'inline' : 'none');
			$this->tpl->setVariable("CSS_DISPLAY_RELATIVE",($this->access_limitations_obj->getMode($role['id']) == 'relative') ? 'inline' : 'none');
			$this->tpl->setVariable("CSS_DISPLAY_UNLIMITED",($this->access_limitations_obj->getMode($role['id']) == 'unlimited') ? 'inline' : 'none');
			$this->tpl->setVariable("TXT_ACCESS_LIMITATION_UNLIMITED", $lng->txt('reg_access_limitation_none'));
			
			$date = $this->__prepareDateSelect($this->access_limitations_obj->getAbsolute($role['id']));
			$this->tpl->setVariable("SEL_ACCESS_LIMITATION_ABSOLUTE",ilUtil::makeDateSelect('access_limitation_absolute_'.$role['id'],$date['y'],$date['m'],$date['d'],'2007'));
			
			$this->tpl->setVariable("TXT_DAYS",$lng->txt('days'));
			$this->tpl->setVariable("TXT_MONTHS",$lng->txt('months'));
			$this->tpl->setVariable("TXT_YEARS",$lng->txt('years'));
			
			$this->tpl->setVariable("DAYS",$this->access_limitations_obj->getRelative($role['id'],'d'));
			$this->tpl->setVariable("MONTHS",$this->access_limitations_obj->getRelative($role['id'],'m'));
			$this->tpl->setVariable("YEARS",$this->access_limitations_obj->getRelative($role['id'],'y'));
			$this->tpl->parseCurrentBlock();			
		}
	}

	function addAssignment()
	{
		global $ilAccess,$ilErr,$rbacreview;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}

		$this->__initRoleAssignments();
		$this->assignments_obj->add();

		ilUtil::sendInfo($this->lng->txt('reg_added_assignment'));
		$this->editEmailAssignments();

		return true;
	}

	function deleteAssignment()
	{
		global $ilAccess,$ilErr,$rbacreview;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}

		if(!count($_POST['del_assign']))
		{
			ilUtil::sendInfo($this->lng->txt('reg_select_one'));
			$this->editEmailAssignments();
			return false;
		}

		$this->__initRoleAssignments();

		foreach($_POST['del_assign'] as $assignment_id)
		{
			$this->assignments_obj->delete($assignment_id);
		}

		ilUtil::sendInfo($this->lng->txt('reg_deleted_assignment'));
		$this->editEmailAssignments();

		return true;
	}

	function saveAssignment()
	{
		global $ilAccess,$ilErr,$rbacreview;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}

		$this->__initRoleAssignments();
		
		if (!is_array($_POST['domain']))
		{
			$_POST['domain'] = array();
		}

		foreach($_POST['domain'] as $id => $data)
		{
			$this->assignments_obj->setDomain($id,ilUtil::stripSlashes($_POST['domain'][$id]['domain']));
			$this->assignments_obj->setRole($id,ilUtil::stripSlashes($_POST['role'][$id]['role']));
		}
		$this->assignments_obj->setDefaultRole((int) $_POST['default_role']);

		if($err = $this->assignments_obj->validate())
		{
			switch($err)
			{
				case IL_REG_MISSING_DOMAIN:
					ilUtil::sendInfo($this->lng->txt('reg_missing_domain'));
					break;
					
				case IL_REG_MISSING_ROLE:
					ilUtil::sendInfo($this->lng->txt('reg_missing_role'));
					break;
			}
			$this->editEmailAssignments();
			return false;
		}


		$this->assignments_obj->save();
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->view();
		return true;
	}
	
	function saveRoleAccessLimitations()
	{
		global $ilAccess,$ilErr,$rbacreview;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}

		$this->__initRoleAccessLimitations();
		
		include_once './Services/AccessControl/classes/class.ilObjRole.php';

		foreach(ilObjRole::_lookupRegisterAllowed() as $role)
		{
			$this->access_limitations_obj->setMode($_POST['access_limitation_mode_'.$role['id']],$role['id']);
			$this->access_limitations_obj->setAbsolute($_POST['access_limitation_absolute_'.$role['id']],$role['id']);
			$this->access_limitations_obj->setRelative($_POST['access_limitation_relative_'.$role['id']],$role['id']);
		}
		
		//var_dump("<pre>",$_POST,$this->access_limitations_obj->getAbsolute(4),time(),"</pre>");exit;
		
		if($err = $this->access_limitations_obj->validate())
		{
			switch($err)
			{
				case IL_REG_ACCESS_LIMITATION_MISSING_MODE:
					ilUtil::sendInfo($this->lng->txt('reg_access_limitation_missing_mode'));
					break;
					
				case IL_REG_ACCESS_LIMITATION_OUT_OF_DATE:
					ilUtil::sendInfo($this->lng->txt('reg_access_limitation_out_of_date'));
					break;
			}
			$this->editRoleAccessLimitations();
			return false;
		}


		$this->access_limitations_obj->save();
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->view();
		return true;
	}


	function __prepareRoleList()
	{
		include_once './Services/AccessControl/classes/class.ilObjRole.php';

		foreach(ilObjRole::_lookupRegisterAllowed() as $role)
		{
			$this->tpl->setCurrentBlock("fixed_item");
			$this->tpl->setVariable("FIXED_ITEM_TITLE",$role['title']);
			$this->tpl->parseCurrentBlock();
		}
	}

	function __prepareAutomaticRoleList()
	{
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		$this->__initRoleAssignments();
		
		foreach($this->assignments_obj->getAssignments() as $assignment)
		{
			if(strlen($assignment['domain']) and $assignment['role'])
			{
				$this->tpl->setCurrentBlock("auto_item");
				$this->tpl->setVariable("AUTO_ITEM_TITLE",$assignment['domain']);
				$this->tpl->setVariable("AUTO_ROLE",ilObjRole::_lookupTitle($assignment['role']));
				$this->tpl->parseCurrentBlock();
			}
		}

		if(strlen($this->assignments_obj->getDefaultRole()))
		{
			$this->tpl->setCurrentBlock("auto_item");
			$this->tpl->setVariable("AUTO_ITEM_TITLE",$this->lng->txt('reg_default'));
			$this->tpl->setVariable("AUTO_ROLE",ilObjRole::_lookupTitle($this->assignments_obj->getDefaultRole()));
			$this->tpl->parseCurrentBlock();
		}			

		$this->tpl->setCurrentBlock("auto");
		$this->tpl->parseCurrentBlock();

	}
	
	function __prepareAccessLimitationRoleList()
	{
		global $lng;
		
		$this->__initRoleAccessLimitations();
		
		include_once './Services/AccessControl/classes/class.ilObjRole.php';

		foreach(ilObjRole::_lookupRegisterAllowed() as $role)
		{
			$this->tpl->setCurrentBlock("access_limitation_item");
			$this->tpl->setVariable("ACCESS_LIMITATION_ITEM_TITLE",$role['title']);
			
			switch ($this->access_limitations_obj->getMode($role['id']))
			{
				case 'absolute':
					$txt_access_value = $lng->txt('reg_access_limitation_limited_until');
					$txt_access_value .= " ".ilDatePresentation::formatDate(new ilDateTime($this->access_limitations_obj->getAbsolute($role['id'],IL_CAL_UNIX)));
					break;
				
				case 'relative':
					$years = $this->access_limitations_obj->getRelative($role['id'],'y');
					$months = $this->access_limitations_obj->getRelative($role['id'],'m');
					$days = $this->access_limitations_obj->getRelative($role['id'],'d');
					
					$txt_access_value = $lng->txt('reg_access_limitation_limited_time')." ";
					
					if ($years)
					{
						$txt_access_value .= $years." ";
						$txt_access_value .= ($years == 1) ? $lng->txt('year') : $lng->txt('years');
						
						if ($months)
						{
							if ($days)
							{
								$txt_access_value .= ", ";
							}
							else
							{
								$txt_access_value .= " ".$lng->txt('and')." ";
							}
						}
						elseif ($days)
						{
							$txt_access_value .= " ".$lng->txt('and')." ";
						}
					}
					
					if ($months)
					{
						$txt_access_value .= $months." ";
						$txt_access_value .= ($months == 1) ? $lng->txt('month') : $lng->txt('months');
						
						if ($days)
						{
							$txt_access_value .= " ".$lng->txt('and')." ";
						}
					}
					
					if ($days)
					{
						$txt_access_value .= $days." ";
						$txt_access_value .= ($days == 1) ? $lng->txt('day') : $lng->txt('days');
					}
					break;
					
				default:
					$txt_access_value = $lng->txt('reg_access_limitation_none');
					break;
			}
			
			$this->tpl->setVariable("ACCESS_LIMITATION_VALUE",$txt_access_value);
			$this->tpl->setVariable("EDIT_ITEM",$lng->txt('edit'));
			$this->tpl->parseCurrentBlock();
		}
	}

	function __initRoleAssignments()
	{
		if(is_object($this->assignments_obj))
		{
			return true;
		}

		include_once 'Services/Registration/classes/class.ilRegistrationEmailRoleAssignments.php';

		$this->assignments_obj = new ilRegistrationRoleAssignments();
	}
	
	function __initRoleAccessLimitations()
	{
		if(is_object($this->access_limitations_obj))
		{
			return true;
		}

		include_once 'Services/Registration/classes/class.ilRegistrationRoleAccessLimitations.php';

		$this->access_limitations_obj = new ilRegistrationRoleAccessLimitations();
	}

	function __buildRoleSelection($assignment_id)
	{
		include_once './Services/AccessControl/classes/class.ilObjRole.php';

		global $rbacreview;

		$assignments = $this->assignments_obj->getAssignments();
		$selected = ($assignment_id > 0) ?
			$assignments[$assignment_id]['role'] :
			$this->assignments_obj->getDefaultRole();

		if(!$selected)
		{
			$roles[0] = $this->lng->txt('please_choose');
		}

		foreach($rbacreview->getGlobalRoles() as $role_id)
		{
			if($role_id == SYSTEM_ROLE_ID or
			   $role_id == ANONYMOUS_ROLE_ID)
			{
				continue;
			}
			$roles[$role_id] = ilObjRole::_lookupTitle($role_id);
		}

		if($assignment_id > 0)
		{
			return ilUtil::formSelect($selected,
									  "role[$assignment_id][role]",
									  $roles,false,true);
		}
		else
		{
			return ilUtil::formSelect($selected,
									  "default_role",
									  $roles,false,true);
		}			
	}
	
	function __buildAccessLimitationSelection($a_role_id)
	{
		global $lng;

		$options = array(
						'null'		=> $lng->txt('please_choose'),
						'unlimited' => $lng->txt('reg_access_limitation_mode_unlimited'),
						'absolute'	=> $lng->txt('reg_access_limitation_mode_absolute'),
						'relative'	=> $lng->txt('reg_access_limitation_mode_relative')
						);
		
		$attribs = array('onchange' => 'displayAccessLimitationSelectionForm(document.cmd.access_limitation_mode_'.$a_role_id.','.$a_role_id.')');

		$selected = $this->access_limitations_obj->getMode($a_role_id);

		return ilUtil::formSelect($selected,'access_limitation_mode_'.$a_role_id,$options,false,true,0,"",$attribs);
		
	}
	
	function __prepareDateSelect($a_unix_time)
	{
		if (!$a_unix_time)
		{
			$a_unix_time = time();
		}

		return array('y' => date('Y',$a_unix_time),
					 'm' => date('n',$a_unix_time),
					 'd' => date('d',$a_unix_time));
	}
}
?>