<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilLDAPSettingsGUI: 
* @ingroup ServicesLDAP
*/
class ilLDAPSettingsGUI
{
	private $ref_id = null;
	
	public function __construct($a_auth_ref_id)
	{
		global $lng,$ilCtrl,$tpl,$ilTabs;
		
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('ldap');
		
		$this->tpl = $tpl;

		$this->ctrl->saveParameter($this,'ldap_server_id');
		$this->ref_id = $a_auth_ref_id;


		$this->initServer();
	}
	
	public function executeCommand()
	{
		global $ilAccess,$ilias, $ilErr, $ilCtrl;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id) && $cmd != "serverList")
		{
			ilUtil::sendFailure($this->lng->txt('msg_no_perm_write'), true);
			$ilCtrl->redirect($this, "serverList");
		}
		

		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "serverList";
				}
				$this->$cmd();
				break;
		}
		return true;
	}
	
	/**
	 * Edit role assignments
	 *
	 * @access public
	 * 
	 */
	public function roleAssignments()
	{
	 	global $rbacreview;

	 	$this->setSubTabs();
		$this->tabs_gui->setSubTabActive('ldap_role_assignments');

	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.ldap_role_assignments.html','Services/LDAP');

	 	include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');
	 	$this->initFormRoleAssignments('create',$this->role_mapping_rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId(0));
	 	$this->tpl->setVariable('NEW_ASSIGNMENT_TBL',$this->form->getHTML());
	 	

		if(count($rules = ilLDAPRoleAssignmentRule::_getRules()))
		{
			include_once("./Services/LDAP/classes/class.ilLDAPRoleAssignmentTableGUI.php");
			$table_gui = new ilLDAPRoleAssignmentTableGUI($this,'roleAssignments');
			$table_gui->setTitle($this->lng->txt("ldap_tbl_role_ass"));
			$table_gui->parse($rules);
			$table_gui->addMultiCommand("confirmDeleteRules", $this->lng->txt("delete"));
			$table_gui->setSelectAllCheckbox("rule_id");
			$this->tpl->setVariable('RULES_TBL',$table_gui->getHTML());
		}
		
	}

	/**
	 * Edit role assignment
	 *
	 * @access public
	 * 
	 */
	public function editRoleAssignment()
	{
	 	if(!(int) $_GET['rule_id'])
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_one'));
	 		$this->roleAssignments();
	 		return false;
	 	}
	 	$this->setSubTabs();
		$this->tabs_gui->setSubTabActive('ldap_role_assignments');

		$this->ctrl->saveParameter($this,'rule_id',(int) $_GET['rule_id']);
	 	include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');
	 	$this->initFormRoleAssignments('edit',
	 		$this->role_mapping_rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId((int) $_GET['rule_id']));
		$this->setValuesByArray();
	 	$this->tpl->setContent($this->form->getHTML());
	}
	
	
	/**
	 * set values of form array
	 * @return 
	 */
	protected function setValuesByArray()
	{
		global $rbacreview;

		$role_id = $this->role_mapping_rule->getRoleId();
		if($rbacreview->isGlobalRole($role_id))
		{
			$val['role_name'] = 0;
			$val['role_id'] = $role_id;
		}
		else
		{
			$val['role_name'] = 1;
			$val['role_search'] = ilObject::_lookupTitle($role_id);	
		}
		$val['add_missing'] = (int) $this->role_mapping_rule->isAddOnUpdateEnabled();
		$val['remove_deprecated'] = (int) $this->role_mapping_rule->isRemoveOnUpdateEnabled();
		$val['type'] = (int) $this->role_mapping_rule->getType();
		$val['dn'] = $this->role_mapping_rule->getDN();
		$val['at'] = $this->role_mapping_rule->getMemberAttribute();
		$val['isdn'] = $this->role_mapping_rule->isMemberAttributeDN();
		$val['name'] = $this->role_mapping_rule->getAttributeName();
		$val['value'] = $this->role_mapping_rule->getAttributeValue();
		$val['plugin_id'] = $this->role_mapping_rule->getPluginId();
		
		$this->form->setValuesByArray($val);
	} 
	
	/**
	 * update role assignment
	 *
	 * @access public
	 * 
	 */
	public function updateRoleAssignment()
	{
		global $ilErr,$ilAccess;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->roleAssignment();
			return false;
		}
		
		include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		
		$this->initFormRoleAssignments('edit');
		if(!$this->form->checkInput() or ($err = $this->checkRoleAssignmentInput((int) $_REQUEST['rule_id'])))
		{
			if($err)
			{
				ilUtil::sendFailure($this->lng->txt($err));
			}

		 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.ldap_role_assignments.html','Services/LDAP');

			// DONE: wrap this
			$this->form->setValuesByPost();
			$this->tpl->setVariable('NEW_ASSIGNMENT_TBL',$this->form->getHTML());
			#$this->tpl->setVariable('RULES_TBL',$this->getRoleAssignmentTable());
			$this->tabs_gui->setSubTabActive('shib_role_assignment');
			return true;
			
		}
		
		// Might redirect
		$this->roleSelection();
		
		$this->rule->update();
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->roleAssignments();
		return true;
	}
	
	/**
	 * Confirm delete rules
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function confirmDeleteRules()
	{
	 	if(!is_array($_POST['rule_ids']))
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_one'));
	 		$this->roleAssignments();
	 		return false;
	 	}
		$this->setSubTabs();
		$this->tabs_gui->setSubTabActive('ldap_role_assignments');
		
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteRules"));
		$c_gui->setHeaderText($this->lng->txt("ldap_confirm_del_role_ass"));
		$c_gui->setCancel($this->lng->txt("cancel"), "roleAssignments");
		$c_gui->setConfirm($this->lng->txt("confirm"), "deleteRules");

		// add items to delete
		include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');
		foreach($_POST["rule_ids"] as $rule_id)
		{
			$rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($rule_id);
			$c_gui->addItem('rule_ids[]',$rule_id,$rule->conditionToString());
		}
		$this->tpl->setContent($c_gui->getHTML());
	}
	
	/**
	 * delete role assignment rule
	 *
	 * @access public
	 * 
	 */
	public function deleteRules()
	{
	 	if(!is_array($_POST['rule_ids']))
	 	{
	 		ilUtil::sendFailure($this->lng->txt('select_once'));
	 		$this->roleAssignments();
	 		return false;
	 	}
		include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');
		foreach($_POST["rule_ids"] as $rule_id)
		{
			$rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($rule_id);
			$rule->delete();
		}
		ilUtil::sendSuccess($this->lng->txt('ldap_deleted_rule'));
		$this->roleAssignments();
		return true;
	}
	
	/**
	 * add new role assignment
	 *
	 * @access public
	 * 
	 */
	public function addRoleAssignment()
	{
		global $ilErr,$ilAccess;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->roleAssignment();
			return false;
		}
		
		include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		
		$this->initFormRoleAssignments('create');
		if(!$this->form->checkInput() or ($err = $this->checkRoleAssignmentInput()))
		{
			if($err)
			{
				ilUtil::sendFailure($this->lng->txt($err));
			}

		 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.ldap_role_assignments.html','Services/LDAP');

			// DONE: wrap this
			$this->form->setValuesByPost();
			$this->tpl->setVariable('NEW_ASSIGNMENT_TBL',$this->form->getHTML());
			$this->tpl->setVariable('RULES_TBL',$this->getRoleAssignmentTable());
			$this->tabs_gui->setSubTabActive('shib_role_assignment');
			return true;
			
		}
		
		// Might redirect
		$this->roleSelection();

		$this->rule->create();
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		unset($_POST);
		$this->roleAssignments();
		return true;
	}
	
	/**
	 * 
	 * @return 
	 */
	protected function roleSelection()
	{
		if($this->rule->getRoleId() > 0)
		{
			return false;
		}

		$_SESSION['ldap_role_ass']['rule_id'] = $_REQUEST['rule_id'] ? $_REQUEST['rule_id'] : 0;
		$_SESSION['ldap_role_ass']['role_search'] = $this->form->getInput('role_search');
		$_SESSION['ldap_role_ass']['add_on_update'] = $this->form->getInput('add_on_update');
		$_SESSION['ldap_role_ass']['remove_on_update'] = $this->form->getInput('remove_deprecated');
		$_SESSION['ldap_role_ass']['type'] = $this->form->getInput('type');
		$_SESSION['ldap_role_ass']['dn'] = $this->form->getInput('dn');
		$_SESSION['ldap_role_ass']['at'] = $this->form->getInput('at');
		$_SESSION['ldap_role_ass']['isdn'] = $this->form->getInput('isdn');
		$_SESSION['ldap_role_ass']['name'] = $this->form->getInput('name');
		$_SESSION['ldap_role_ass']['value'] = $this->form->getInput('value');
		$_SESSION['ldap_role_ass']['plugin'] = $this->form->getInput('plugin_id');
		
		$this->ctrl->saveParameter($this,'rule_id');
		$this->ctrl->redirect($this,'showRoleSelection');
	}
	
	
	
	/**
	 * show role selection
	 * @return 
	 */
	protected function showRoleSelection()
	{
		$this->setSubTabs();
		$this->tabs_gui->setSubTabActive('ldap_role_assignment');
		$this->ctrl->saveParameter($this,'rule_id');
		
		include_once './Services/Search/classes/class.ilQueryParser.php';
		$parser = new ilQueryParser($_SESSION['ldap_role_ass']['role_search']);
		$parser->setMinWordLength(1);
		$parser->setCombination(QP_COMBINATION_AND);
		$parser->parse();
		
		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search = new ilLikeObjectSearch($parser);
		$object_search->setFilter(array('role'));
		$res = $object_search->performSearch();
		
		$entries = $res->getEntries();

		include_once './Services/AccessControl/classes/class.ilRoleSelectionTableGUI.php';
		$table = new ilRoleSelectionTableGUI($this,'showRoleSelection');
		$table->setTitle($this->lng->txt('ldap_role_selection'));
		$table->addMultiCommand('saveRoleSelection',$this->lng->txt('ldap_choose_role'));
		$table->addCommandButton('roleAssignment',$this->lng->txt('cancel'));
		$table->parse($entries);
		
		$this->tpl->setContent($table->getHTML());
		return true;
	}

	/**
	 * Save role selection
	 * @return 
	 */
	protected function saveRoleSelection()
	{
		global $ilErr,$ilAccess;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->roleAssignment();
			return false;
		}

		if(!(int) $_REQUEST['role_id'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->showRoleSelection();
			return false;
		}

		$this->loadRoleAssignmentRule((int) $_REQUEST['rule_id'],false);
		$this->rule->setRoleId((int) $_REQUEST['role_id']);
		
		if((int) $_REQUEST['rule_id'])
		{
			$this->rule->update();
		}
		else
		{
			$this->rule->create();
		}
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->roleAssignments();
		return true;
	}
	
	
	/**
	 * Check role assignment input
	 * @return 
	 * @param int $a_rule_id
	 */
	protected function checkRoleAssignmentInput($a_rule_id = 0)
	{
		global $ilErr;
		
		$this->loadRoleAssignmentRule($a_rule_id);
		$this->rule->validate();
		return $ilErr->getMessage();
	}
	
	
	/**
	 * Show active role assignments
	 * @return 
	 */
	protected function getRoleAssignmentTable()
	{
		if(count($rules = ilLDAPRoleAssignmentRule::_getRules()))
		{
			include_once("./Services/LDAP/classes/class.ilLDAPRoleAssignmentTableGUI.php");
			$table_gui = new ilLDAPRoleAssignmentTableGUI($this,'roleAssignments');
			$table_gui->setTitle($this->lng->txt("ldap_tbl_role_ass"));
			$table_gui->parse($rules);
			$table_gui->addMultiCommand("confirmDeleteRules", $this->lng->txt("delete"));
			$table_gui->setSelectAllCheckbox("rule_id");
			return $table_gui->getHTML();
		}
		return ''; 		
	}
	
	
	/**
	 * Load input from form
	 * @return 
	 * @param object $a_rule_id
	 */
	protected function loadRoleAssignmentRule($a_rule_id,$a_from_form = true)
	{
		if(is_object($this->rule))
		{
			return true;
		}
		
		include_once './Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php';
		$this->rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($a_rule_id);


		if($a_from_form)
		{
			if($this->form->getInput('role_name') == 0)
			{
				$this->rule->setRoleId($this->form->getInput('role_id'));
			}
			elseif($this->form->getInput('role_search'))
			{
				// Search role
				include_once './Services/Search/classes/class.ilQueryParser.php';
				
				$parser = new ilQueryParser($this->form->getInput('role_search'));
				
				// TODO: Handle minWordLength
				$parser->setMinWordLength(1);
				$parser->setCombination(QP_COMBINATION_AND);
				$parser->parse();
				
				include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
				$object_search = new ilLikeObjectSearch($parser);
				$object_search->setFilter(array('role'));
				$res = $object_search->performSearch();
				
				$entries = $res->getEntries();
				if(count($entries) == 1)
				{
					$role = current($entries);
					$this->rule->setRoleId($role['obj_id']);
				}
				elseif(count($entries) > 1)
				{
					$this->rule->setRoleId(-1);
				}
			}
			
			$this->rule->setAttributeName($this->form->getInput('name'));
			$this->rule->setAttributeValue($this->form->getInput('value'));
			$this->rule->setDN($this->form->getInput('dn'));
			$this->rule->setMemberAttribute($this->form->getInput('at'));
			$this->rule->setMemberIsDN($this->form->getInput('isdn'));
			$this->rule->enableAddOnUpdate($this->form->getInput('add_missing'));
			$this->rule->enableRemoveOnUpdate($this->form->getInput('remove_deprecated'));
			$this->rule->setPluginId($this->form->getInput('plugin_id'));
			$this->rule->setType($this->form->getInput('type'));
			return true;
		}
		
		// LOAD from session
		$this->rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($a_rule_id);
		$this->rule->setServerId(0);
		$this->rule->enableAddOnUpdate((int) $_SESSION['ldap_role_ass']['add_missing']);
		$this->rule->enableRemoveOnUpdate((int) $_SESSION['ldap_role_ass']['remove_deprecated']);
		$this->rule->setType(ilUtil::stripSlashes($_SESSION['ldap_role_ass']['type']));
		$this->rule->setDN(ilUtil::stripSlashes($_SESSION['ldap_role_ass']['dn']));
		$this->rule->setMemberAttribute( ilUtil::stripSlashes($_SESSION['ldap_role_ass']['at']));
		$this->rule->setMemberIsDN( ilUtil::stripSlashes($_SESSION['ldap_role_ass']['isdn']));
		$this->rule->setAttributeName( ilUtil::stripSlashes($_SESSION['ldap_role_ass']['name']));
		$this->rule->setAttributeValue(ilUtil::stripSlashes($_SESSION['ldap_role_ass']['value']));
		$this->rule->setPluginId(ilUtil::stripSlashes($_SESSION['ldap_role_ass']['plugin_id']));
		return true;
	}
	
	
	public function roleMapping()
	{
		$this->initRoleMapping();

		$this->setSubTabs();
		$this->tabs_gui->setSubTabActive('ldap_role_mapping');
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.ldap_role_mapping.html','Services/LDAP');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this,'saveRoleMapping'));
		
		// Role Sync
		$this->tpl->setVariable('TXT_ROLE_SETTINGS',$this->lng->txt('ldap_role_settings'));
		$this->tpl->setVariable('TXT_ROLE_ACTIVE',$this->lng->txt('ldap_role_active'));
		$this->tpl->setVariable('TXT_ROLE_BIND_USER',$this->lng->txt('ldap_role_bind_user'));
		$this->tpl->setVariable('TXT_ROLE_BIND_PASS',$this->lng->txt('ldap_role_bind_pass'));
		$this->tpl->setVariable('TXT_ROLE_ASSIGNMENTS',$this->lng->txt('ldap_role_assignments'));
		$this->tpl->setVariable('TXT_BINDING',$this->lng->txt('ldap_server_binding'));
		
		$this->tpl->setVariable('TXT_ROLE_BIND_USER_INFO',$this->lng->txt('ldap_role_bind_user_info'));
		$this->tpl->setVariable('TXT_ROLE_ASSIGNMENTS_INFO',$this->lng->txt('ldap_role_assignments_info'));
		
		
		$mapping_data = $this->role_mapping->getMappings();
		$mapping_data = $this->loadMappingCopy($mapping_data);
		$this->loadMappingDetails();
		
		// Section new assignment
		$this->tpl->setVariable('TXT_NEW_ASSIGNMENT',$this->lng->txt('ldap_new_role_assignment'));
		$this->tpl->setVariable('TXT_URL',$this->lng->txt('ldap_server'));
		$this->tpl->setVariable('TXT_DN',$this->lng->txt('ldap_group_dn'));
		$this->tpl->setVariable('TXT_MEMBER',$this->lng->txt('ldap_group_member'));
		$this->tpl->setVariable('TXT_MEMBER_ISDN',$this->lng->txt('ldap_memberisdn'));
		$this->tpl->setVariable('TXT_ROLE',$this->lng->txt('ldap_ilias_role'));
		$this->tpl->setVariable('TXT_ROLE_INFO',$this->lng->txt('ldap_role_info'));
		$this->tpl->setVariable('TXT_DN_INFO',$this->lng->txt('ldap_dn_info'));
		$this->tpl->setVariable('TXT_MEMBER_INFO',$this->lng->txt('ldap_member_info'));
		$this->tpl->setVariable('TXT_MEMBERISDN',$this->lng->txt('ldap_memberisdn'));
		$this->tpl->setVariable('TXT_INFO',$this->lng->txt('ldap_info_text'));
		$this->tpl->setVariable('TXT_INFO_INFO',$this->lng->txt('ldap_info_text_info'));
		
		
		$this->tpl->setVariable('ROLE_BIND_USER',$this->server->getRoleBindDN());
		$this->tpl->setVariable('ROLE_BIND_PASS',$this->server->getRoleBindPassword());
		$this->tpl->setVariable('CHECK_ROLE_ACTIVE',ilUtil::formCheckbox($this->server->enabledRoleSynchronization() ? true : false,
			'role_sync_active',
			1));
			
		// Section new assignment
		$this->tpl->setVariable('URL',$mapping_data[0]['url'] ? $mapping_data[0]['url'] : $this->server->getUrl());
		$this->tpl->setVariable('DN',$mapping_data[0]['dn']);
		$this->tpl->setVariable('ROLE',$mapping_data[0]['role_name']);
		$this->tpl->setVariable('MEMBER',$mapping_data[0]['member_attribute']);
		$this->tpl->setVariable('CHECK_MEMBERISDN',ilUtil::formCheckbox($mapping_data[0]['memberisdn'],
			'mapping[0][memberisdn]',
			1));
		$this->tpl->setVariable('MAPPING_INFO',$mapping_data[0]['info']);
		
		$info_type_checked = isset($mapping_data[0]['info_type']) ? $mapping_data[0]['info_type'] : 1;
		
		$this->tpl->setVariable('TXT_MAPPING_INFO_TYPE',$this->lng->txt('ldap_mapping_info_type'));
		$this->tpl->setVariable('CHECK_MAPPING_INFO_TYPE',ilUtil::formCheckbox($info_type_checked,
			'mapping[0][info_type]',
			1));
		
		unset($mapping_data[0]);
		
		// Section assignments
		if(count($mapping_data))
		{
			$this->tpl->setCurrentBlock('txt_assignments');
			$this->tpl->setVariable('TXT_ASSIGNMENTS',$this->lng->txt('ldap_role_group_assignments'));
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock('delete_btn');
			$this->tpl->setVariable('SOURCE',ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable('TXT_DELETE',$this->lng->txt('delete'));
			$this->tpl->parseCurrentBlock();
		}
		
		$mapping_data = $this->sortMappingData($mapping_data);
		
		foreach($mapping_data as $data)
		{
			$mapping_id = $data['mapping_id'];
			if(in_array($mapping_id,$_SESSION['ldap_mapping_details']))
			{
				$this->tpl->setCurrentBlock('show_mapping_details');
				$this->tpl->setVariable('ASS_GROUP_URL',$this->lng->txt('ldap_server_short'));
				$this->tpl->setVariable('ASS_GROUP_DN',$this->lng->txt('ldap_group_dn_short'));
				$this->tpl->setVariable('ASS_MEMBER_ATTR',$this->lng->txt('ldap_group_member_short'));
				$this->tpl->setVariable('ASS_ROLE',$this->lng->txt('ldap_ilias_role_short'));
				$this->tpl->setVariable('ASS_INFO',$this->lng->txt('ldap_info_text_short'));
				$this->tpl->setVariable('ROW_ID',$mapping_id);
				$this->tpl->setVariable('ROW_URL',$data['url']);
				$this->tpl->setVariable('ROW_ROLE',$data['role_name'] ? $data['role_name'] : $data['role']);
				$this->tpl->setVariable('ROW_DN',$data['dn']);
				$this->tpl->setVariable('ROW_MEMBER',$data['member_attribute']);
				$this->tpl->setVariable('TXT_ROW_MEMBERISDN',$this->lng->txt('ldap_memberisdn'));
				$this->tpl->setVariable('ROW_CHECK_MEMBERISDN',ilUtil::formCheckbox($data['member_isdn'],
					'mapping['.$mapping_id.'][memberisdn]',
					1));
				$this->tpl->setVariable('ROW_INFO',ilUtil::prepareFormOutput($data['info']));
				$this->tpl->setVariable('TXT_ROW_INFO_TYPE',$this->lng->txt('ldap_mapping_info_type'));
				$this->tpl->setVariable('ROW_CHECK_INFO_TYPE',ilUtil::formCheckbox($data['info_type'],
					'mapping['.$mapping_id.'][info_type]',
					1));
				$this->tpl->parseCurrentBlock();
			}
			
			// assignment row			
			$this->tpl->setCurrentBlock('assignments');
			
			// Copy link
			$this->ctrl->setParameter($this,'mapping_id',$mapping_id);
			$this->tpl->setVariable('COPY_LINK',$this->ctrl->getLinkTarget($this,'roleMapping'));
			$this->tpl->setVariable('TXT_COPY',$this->lng->txt('copy'));
			$this->ctrl->clearParameters($this);

			// Details link
			if(!in_array($mapping_id,$_SESSION['ldap_mapping_details']))
			{
				$this->ctrl->setParameter($this,'details_show',$mapping_id);
				$this->tpl->setVariable('DETAILS_LINK',$this->ctrl->getLinkTarget($this,'roleMapping'));
				$this->tpl->setVariable('TXT_DETAILS',$this->lng->txt('show_details'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$this->ctrl->setParameter($this,'details_hide',$mapping_id);
				$this->tpl->setVariable('DETAILS_LINK',$this->ctrl->getLinkTarget($this,'roleMapping'));
				$this->tpl->setVariable('TXT_DETAILS',$this->lng->txt('hide_details'));
				$this->ctrl->clearParameters($this);
			}
			if(!count($_SESSION['ldap_mapping_details']))
			{
				$this->tpl->setVariable('WIDTH',"50%");
			}
			$this->tpl->setVariable('ROW_CHECK',ilUtil::formCheckbox(0,
				'mappings[]',$mapping_id));
			$this->tpl->setVariable('TXT_TITLE_TITLE',$this->lng->txt('title'));
			$this->tpl->setVariable('TXT_TITLE_ROLE',$this->lng->txt('obj_role'));
			$this->tpl->setVariable('TXT_TITLE_GROUP',$this->lng->txt('obj_grp'));
			$this->tpl->setVariable('TITLE_GROUP',$this->role_mapping->getMappingInfoString($mapping_id));
			$this->tpl->setVariable('TITLE_TITLE',ilUtil::shortenText($data['obj_title'],30,true));
			$this->tpl->setVariable('TITLE_ROLE',$data['role_name']);
			
			$this->tpl->parseCurrentBlock();
		}
		

		$this->tpl->setVariable('TXT_SAVE',$this->lng->txt('save'));
		$this->tpl->setVariable('TXT_REQUIRED_FLD',$this->lng->txt('required_field'));
	}
	
	
	public function deleteRoleMapping()
	{
		if(!count($_POST['mappings']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->roleMapping();
			return false;
		}
		
		$this->initRoleMapping();
		
		foreach($_POST['mappings'] as $mapping_id)
		{
			$this->role_mapping->delete($mapping_id);
		}
		ilUtil::sendSuccess($this->lng->txt('ldap_deleted_role_mapping'));
		$this->roleMapping();
		return true;
	}
	
	public function reset()
	{
	 	unset($_POST['mapping_template']);
	 	$this->userMapping();
	}
	
	public function saveRoleMapping()
	{
		global $ilErr;
		
		$this->server->setRoleBindDN(ilUtil::stripSlashes($_POST['role_bind_user']));
		$this->server->setRoleBindPassword(ilUtil::stripSlashes($_POST['role_bind_pass']));
		$this->server->enableRoleSynchronization((int) $_POST['role_sync_active']);
		
		// Update or create
		if($this->server->getServerId())
		{
			$this->server->update();
		}
		else
		{
			$_GET['ldap_server_id'] = $this->server->create();
		}
		
		$this->initRoleMapping();
		$this->role_mapping->loadFromPost($_POST['mapping']);
		if(!$this->role_mapping->validate())
		{
			ilUtil::sendFailure($ilErr->getMessage());
			$this->roleMapping();
			return false;				
		}
		$this->role_mapping->save();

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->roleMapping();
		return true;
	}
	
	public function userMapping($a_show_defaults = false)
	{
		$this->initAttributeMapping();
		
		$this->setSubTabs();
		$this->tabs_gui->setSubTabActive('ldap_user_mapping');
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.ldap_user_mapping.html','Services/LDAP');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		
		$this->tpl->setVariable('TXT_LDAP_MAPPING',$this->lng->txt('ldap_mapping_table'));
		$this->tpl->setVariable('SELECT_MAPPING',$this->prepareMappingSelect());
		
		if($_POST['mapping_template'])
		{
			$this->tpl->setCurrentBlock('reset');
			$this->tpl->setVariable('TXT_RESET',$this->lng->txt('reset'));
			$this->tpl->parseCurrentBlock();
		}
		
		foreach($this->getMappingFields() as $mapping => $translation)
		{
			$this->tpl->setCurrentBlock('attribute_row');
			$this->tpl->setVariable('TXT_NAME',$translation);
			$this->tpl->setVariable('FIELD_NAME',$mapping.'_value');
			$this->tpl->setVariable('FIELD_VALUE',$this->mapping->getValue($mapping));
			$this->tpl->setVariable('CHECK_FIELD',ilUtil::formCheckbox($this->mapping->enabledUpdate($mapping),$mapping.'_update',1));
			$this->tpl->setVariable('UPDATE_INFO',$this->lng->txt('ldap_update_field_info'));
			$this->tpl->parseCurrentBlock();
		}
		
		// Show user defined fields
		$this->initUserDefinedFields();
		foreach($this->udf->getDefinitions() as $definition)
		{
			$this->tpl->setCurrentBlock('attribute_row');
			$this->tpl->setVariable('TXT_NAME',$definition['field_name']);
			$this->tpl->setVariable('FIELD_NAME','udf_'.$definition['field_id'].'_value');
			$this->tpl->setVariable('FIELD_VALUE',$this->mapping->getValue('udf_'.$definition['field_id']));
			$this->tpl->setVariable('CHECK_FIELD',ilUtil::formCheckbox($this->mapping->enabledUpdate('udf_'.$definition['field_id']),
																		'udf_'.$definition['field_id'].'_update',1));
			$this->tpl->setVariable('UPDATE_INFO',$this->lng->txt('ldap_update_field_info'));
			$this->tpl->parseCurrentBlock();

		}
		
		$this->tpl->setVariable('TXT_SAVE',$this->lng->txt('save'));
		$this->tpl->setVariable('TXT_SHOW',$this->lng->txt('show'));
	}
	
	public function chooseMapping()
	{
		if(!$_POST['mapping_template'])
		{
			$this->userMapping();
			return;
		}
		
		$this->initAttributeMapping();
		$this->mapping->clearRules();
		
		include_once('Services/LDAP/classes/class.ilLDAPAttributeMappingUtils.php');
		foreach(ilLDAPAttributeMappingUtils::_getMappingRulesByClass($_POST['mapping_template']) as $key => $value)
		{
			$this->mapping->setRule($key,$value,0);
		}
		$this->userMapping();
		return true;
	}
	
	public function saveMapping()
	{
		$this->initAttributeMapping();
		foreach($this->getMappingFields() as $key => $mapping)
		{
			$this->mapping->setRule($key,ilUtil::stripSlashes($_POST[$key.'_value']),(int) $_POST[$key.'_update']);
		}
		$this->initUserDefinedFields();
		foreach($this->udf->getDefinitions() as $definition)
		{
			$key = 'udf_'.$definition['field_id'];
			$this->mapping->setRule($key,ilUtil::stripSlashes($_POST[$key.'_value']),(int) $_POST[$key.'_update']);
		}
		
		$this->mapping->save();
		$this->userMapping();
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		unset($_POST['mapping_template']);
		return;
	}
	
	public function serverList()
	{
		global $ilAccess, $ilErr;
		
		if(!$ilAccess->checkAccess('read','',$this->ref_id) && $cmd != "serverList")
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$ilErr->WARNING);
		}

		$this->setSubTabs();
		$this->tabs_gui->setSubTabActive('ldap_settings');
		
		$this->initForm();
		$this->setServerFormValues();		
		return $this->tpl->setContent($this->form_gui->getHtml());
	}
	
	public function setServerFormValues()
	{
		$this->form_gui->setValuesByArray(array(
			'active' => $this->server->isActive(),
			'server_name' => $this->server->getName(),
			'server_url' => $this->server->getUrlString(), 
			'version' => $this->server->getVersion(),
			'base_dn' => $this->server->getBaseDN(),
			'referrals' => $this->server->isActiveReferrer(),
			'tls' => $this->server->isActiveTLS(),			
			'binding_type' => $this->server->getBindingType(),
			'bind_dn' => $this->server->getBindUser(),
			'bind_pass' => $this->server->getBindPassword(),
			'search_base' => $this->server->getSearchBase(),
			'user_scope' => $this->server->getUserScope(),
			'user_attribute' => $this->server->getUserAttribute(),			
			'filter' => $this->server->getFilter(),
			'group_dn' => $this->server->getGroupDN(),
			'group_scope' => $this->server->getGroupScope(),
			'group_filter' => $this->server->getGroupFilter(),
			'group_member' => $this->server->getGroupMember(),
			'memberisdn' => $this->server->enabledGroupMemberIsDN(),
			'group' => $this->server->getGroupName(),
			'group_attribute' => $this->server->getGroupAttribute(),
			'group_optional' => $this->server->isMembershipOptional(),
			'group_user_filter' => $this->server->getGroupUserFilter(),
			'sync_on_login' => $this->server->enabledSyncOnLogin(),
			'sync_per_cron' => $this->server->enabledSyncPerCron(),
			'global_role' => ilLDAPAttributeMapping::_lookupGlobalRole($this->server->getServerId()),
			'migration' => (int)$this->server->isAccountMigrationEnabled(),			
		));
	}
	
	private function initForm()
	{	
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';		
	 		 	
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'save'));
		$this->form_gui->setTitle($this->lng->txt('ldap_configure'));
 		
		$active = new ilCheckboxInputGUI($this->lng->txt('auth_ldap_enable'), 'active');
		$active->setValue(1);
		$this->form_gui->addItem($active);

		$servername = new ilTextInputGUI($this->lng->txt('ldap_server_name'), 'server_name');
		$servername->setRequired(true);
		$servername->setInfo($this->lng->txt('ldap_server_name_info'));
		$servername->setSize(32);
		$servername->setMaxLength(32);
		$this->form_gui->addItem($servername);
 		
		$serverurl = new ilTextInputGUI($this->lng->txt('ldap_server'), 'server_url');
		$serverurl->setRequired(true);
		$serverurl->setInfo($this->lng->txt('ldap_server_url_info'));
		$serverurl->setSize(64);
		$serverurl->setMaxLength(255);
		$this->form_gui->addItem($serverurl);
 		
		$version = new ilSelectInputGUI($this->lng->txt('ldap_version'), 'version');
		$version->setOptions(array(2 => 2, 3 => 3));
		$version->setInfo($this->lng->txt('ldap_server_version_info'));
		$this->form_gui->addItem($version);
 		
		$basedsn = new ilTextInputGUI($this->lng->txt('basedn'), 'base_dn');
		$basedsn->setRequired(true);
		$basedsn->setSize(64);
		$basedsn->setMaxLength(255);
		$this->form_gui->addItem($basedsn);
 		
		$referrals = new ilCheckboxInputGUI($this->lng->txt('ldap_referrals'), 'referrals');
		$referrals->setValue(1);
		$referrals->setInfo($this->lng->txt('ldap_referrals_info'));
		$this->form_gui->addItem($referrals);
 		
		$section_security = new ilFormSectionHeaderGUI();
		$section_security->setTitle($this->lng->txt('ldap_server_security_settings'));
		$this->form_gui->addItem($section_security);
 		
		$tls = new ilCheckboxInputGUI($this->lng->txt('ldap_tls'), 'tls');
		$tls->setValue(1);
		$this->form_gui->addItem($tls);		
 		
		$binding = new ilRadioGroupInputGUI($this->lng->txt('ldap_server_binding'), 'binding_type' );
			$anonymous = new ilRadioOption($this->lng->txt('ldap_bind_anonymous'), IL_LDAP_BIND_ANONYMOUS);
		$binding->addOption($anonymous);
			$user = new ilRadioOption($this->lng->txt('ldap_bind_user'), IL_LDAP_BIND_USER);
				$dn = new ilTextInputGUI($this->lng->txt('ldap_server_bind_dn'), 'bind_dn');
				$dn->setSize(32);
				$dn->setMaxLength(255);
			$user->addSubItem($dn);
				$pass = new ilPasswordInputGUI($this->lng->txt('ldap_server_bind_pass'), 'bind_pass');
				$pass->setSize(12);
				$pass->setMaxLength(36);
			$user->addSubItem($pass);
		$binding->addOption($user);
		$this->form_gui->addItem($binding);
 		
		$section_auth = new ilFormSectionHeaderGUI();
		$section_auth->setTitle($this->lng->txt('ldap_authentication_settings'));
		$this->form_gui->addItem($section_auth);
		
		$search_base = new ilTextInputGUI($this->lng->txt('ldap_user_dn'), 'search_base');
		$search_base->setInfo($this->lng->txt('ldap_search_base_info'));
		$search_base->setSize(32);
		$search_base->setMaxLength(255);
		$this->form_gui->addItem($search_base);
		
		$user_scope = new ilSelectInputGUI($this->lng->txt('ldap_user_scope'), 'user_scope');
		$user_scope->setOptions(array(IL_LDAP_SCOPE_ONE => $this->lng->txt('ldap_scope_one'),
				IL_LDAP_SCOPE_SUB => $this->lng->txt('ldap_scope_sub')));
		$user_scope->setInfo($this->lng->txt('ldap_user_scope_info'));
		$this->form_gui->addItem($user_scope);
		
		$user_attribute = new ilTextInputGUI($this->lng->txt('ldap_user_attribute'), 'user_attribute');
		$user_attribute->setSize(16);
		$user_attribute->setMaxLength(64);
		$user_attribute->setRequired(true);
		$this->form_gui->addItem($user_attribute);
		
		$filter = new ilTextInputGUI($this->lng->txt('ldap_search_filter'), 'filter');
		$filter->setInfo($this->lng->txt('ldap_filter_info'));
		$filter->setSize(32);
		$filter->setMaxLength(255);
		$this->form_gui->addItem($filter);
		
		$section_restrictions = new ilFormSectionHeaderGUI();
		$section_restrictions->setTitle($this->lng->txt('ldap_group_restrictions'));
		$this->form_gui->addItem($section_restrictions);
		
		$group_dn = new ilTextInputGUI($this->lng->txt('ldap_group_search_base'), 'group_dn');
		$group_dn->setInfo($this->lng->txt('ldap_group_dn_info'));
		$group_dn->setSize(32);
		$group_dn->setMaxLength(255);
		$this->form_gui->addItem($group_dn);
		
		$group_scope = new ilSelectInputGUI($this->lng->txt('ldap_group_scope'), 'group_scope');
		$group_scope->setOptions(array(IL_LDAP_SCOPE_ONE => $this->lng->txt('ldap_scope_one'),
				IL_LDAP_SCOPE_SUB => $this->lng->txt('ldap_scope_sub')));
		$group_scope->setInfo($this->lng->txt('ldap_group_scope_info'));
		$this->form_gui->addItem($group_scope);
		
		$group_filter = new ilTextInputGUI($this->lng->txt('ldap_group_filter'), 'group_filter');
		$group_filter->setInfo($this->lng->txt('ldap_group_filter_info'));
		$group_filter->setSize(32);
		$group_filter->setMaxLength(255);
		$this->form_gui->addItem($group_filter);
		
		$group_member = new ilTextInputGUI($this->lng->txt('ldap_group_member'), 'group_member');
		$group_member->setInfo($this->lng->txt('ldap_group_member_info'));
		$group_member->setSize(32);
		$group_member->setMaxLength(255);
			$group_member_isdn = new ilCheckboxInputGUI($this->lng->txt('ldap_memberisdn'), 'memberisdn');
			$group_member_isdn->setValue(1);
			$group_member->addSubItem($group_member_isdn);	
		$this->form_gui->addItem($group_member);		
		
		$group = new ilTextInputGUI($this->lng->txt('ldap_group_name'), 'group');
		$group->setInfo($this->lng->txt('ldap_group_name_info'));
		$group->setSize(32);
		$group->setMaxLength(255);
		$this->form_gui->addItem($group);
		
		$group_atrr = new ilTextInputGUI($this->lng->txt('ldap_group_attribute'), 'group_attribute');
		$group_atrr->setInfo($this->lng->txt('ldap_group_attribute_info'));
		$group_atrr->setSize(16);
		$group_atrr->setMaxLength(64);
		$this->form_gui->addItem($group_atrr);
		
		$group_optional = new ilCheckboxInputGUI($this->lng->txt('ldap_group_membership'), 'group_optional');
		$group_optional->setOptionTitle($this->lng->txt('ldap_group_member_optional'));
		$group_optional->setInfo($this->lng->txt('ldap_group_optional_info'));
		$group_optional->setValue(1);
			$group_user_filter = new ilTextInputGUI($this->lng->txt('ldap_group_user_filter'), 'group_user_filter');
			$group_user_filter->setSize(32);
			$group_user_filter->setMaxLength(255);
			$group_optional->addSubItem($group_user_filter);			
		$this->form_gui->addItem($group_optional);
	
		$section_sync = new ilFormSectionHeaderGUI();
		$section_sync->setTitle($this->lng->txt('ldap_user_sync'));
		$this->form_gui->addItem($section_sync);		
		
		$ci_gui = new ilCustomInputGUI($this->lng->txt('ldap_moment_sync'));
			$sync_on_login = new ilCheckboxInputGUI($this->lng->txt('ldap_sync_login'), 'sync_on_login');
			$sync_on_login->setValue(1);
		$ci_gui->addSubItem($sync_on_login);
			$sync_per_cron = new ilCheckboxInputGUI($this->lng->txt('ldap_sync_cron'), 'sync_per_cron');
			$sync_per_cron->setValue(1);
		$ci_gui->addSubItem($sync_per_cron);
		$ci_gui->setInfo($this->lng->txt('ldap_user_sync_info'));
		$this->form_gui->addItem($ci_gui);
		
		$global_role = new ilSelectInputGUI($this->lng->txt('ldap_global_role_assignment'), 'global_role');
		$global_role->setOptions($this->prepareRoleSelect(false));
		$global_role->setInfo($this->lng->txt('ldap_global_role_info'));
		$this->form_gui->addItem($global_role);
		
		$migr = new ilCheckboxInputGUI($this->lng->txt('auth_ldap_migration'), 'migration');
		$migr->setInfo($this->lng->txt('auth_ldap_migration_info'));
		$migr->setValue(1);
		$this->form_gui->addItem($migr);
		
		$this->form_gui->addCommandButton('save', $this->lng->txt('save'));			
 	}
	
	/* 
 	 * Update Settings
	 */
	function save()
	{
		global $ilErr;
		
		$this->setSubTabs();
		$this->tabs_gui->setSubTabActive('ldap_settings');
		
		$this->initForm();
		if($this->form_gui->checkInput())
 		{
			$this->server->toggleActive((int)$this->form_gui->getInput('active'));
			$this->server->setName($this->form_gui->getInput('server_name'));
			$this->server->setUrl($this->form_gui->getInput('server_url'));
			$this->server->setVersion($this->form_gui->getInput('version'));
			$this->server->setBaseDN($this->form_gui->getInput('base_dn'));
			$this->server->toggleReferrer($this->form_gui->getInput('referrals'));
			$this->server->toggleTLS($this->form_gui->getInput('tls'));
			$this->server->setBindingType((int)$this->form_gui->getInput('binding_type'));
			$this->server->setBindUser($this->form_gui->getInput('bind_dn'));
			$this->server->setBindPassword($this->form_gui->getInput('bind_pass'));
			$this->server->setSearchBase($this->form_gui->getInput('search_base'));
			$this->server->setUserScope($this->form_gui->getInput('user_scope'));
			$this->server->setUserAttribute($this->form_gui->getInput('user_attribute'));
			$this->server->setFilter($this->form_gui->getInput('filter'));
			$this->server->setGroupDN($this->form_gui->getInput('group_dn'));
			$this->server->setGroupScope((int)$this->form_gui->getInput('group_scope'));
			$this->server->setGroupFilter($this->form_gui->getInput('group_filter'));
			$this->server->setGroupMember($this->form_gui->getInput('group_member'));
			$this->server->enableGroupMemberIsDN((int)$this->form_gui->getInput('memberisdn'));
			$this->server->setGroupName($this->form_gui->getInput('group'));
			$this->server->setGroupAttribute($this->form_gui->getInput('group_attribute'));
			$this->server->setGroupUserFilter($this->form_gui->getInput('group_user_filter'));
			$this->server->toggleMembershipOptional((int)$this->form_gui->getInput('group_optional'));
			$this->server->enableSyncOnLogin((int)$this->form_gui->getInput('sync_on_login'));
			$this->server->enableSyncPerCron((int)$this->form_gui->getInput('sync_per_cron'));
			$this->server->setGlobalRole((int)$this->form_gui->getInput('global_role'));
			$this->server->enableAccountMigration((int)$this->form_gui->getInput('migration'));

			if(!$this->server->validate())
			{
				ilUtil::sendFailure($ilErr->getMessage());
				$this->form_gui->setValuesByPost();
				return $this->tpl->setContent($this->form_gui->getHtml());
			}
			
			// Update or create
			if($this->server->getServerId())
			{
				$this->server->update();
			}
			else
			{
				$_GET['ldap_server_id'] = $this->server->create();
			}
			
			// Now server_id exists => update LDAP attribute mapping
			$this->initAttributeMapping();
			$this->mapping->setRule('global_role', (int)$this->form_gui->getInput('global_role'), false);
			$this->mapping->save();
	
			ilUtil::sendSuccess($this->lng->txt('settings_saved'));
			$this->form_gui->setValuesByPost();
			return $this->tpl->setContent($this->form_gui->getHtml());
 		}		
		
		$this->form_gui->setValuesByPost();
		return $this->tpl->setContent($this->form_gui->getHtml());
	}
	
	
	
	/**
	 * Set sub tabs for ldap section
	 *
	 * @access private
	 */
	private function setSubTabs()
	{
		$this->tabs_gui->addSubTabTarget("ldap_settings",
			$this->ctrl->getLinkTarget($this,'serverList'),
			"serverList",get_class($this));
			
		// Disable all other tabs, if server hasn't been configured. 
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		if(!count(ilLDAPServer::_getServerList()))
		{
			return true;
		}

		$this->tabs_gui->addSubTabTarget("ldap_user_mapping",
			$this->ctrl->getLinkTarget($this,'userMapping'),
			"userMapping",get_class($this));
			
		$this->tabs_gui->addSubTabTarget('ldap_role_assignments',
			$this->ctrl->getLinkTarget($this,'roleAssignments'),
			"roleAssignments",get_class($this));			
			
		$this->tabs_gui->addSubTabTarget("ldap_role_mapping",
			$this->ctrl->getLinkTarget($this,'roleMapping'),
			"roleMapping",get_class($this));
			
	}
	
	
	private function initServer()
	{
		include_once './Services/LDAP/classes/class.ilLDAPServer.php';
		if(!$_GET['ldap_server_id'])
		{
			$_GET['ldap_server_id'] = ilLDAPServer::_getFirstServer();
		}
		$this->server = new ilLDAPServer((int) $_GET['ldap_server_id']);
	}
	
	private function initAttributeMapping()
	{
		include_once './Services/LDAP/classes/class.ilLDAPAttributeMapping.php';
		$this->mapping = ilLDAPAttributeMapping::_getInstanceByServerId((int) $_GET['ldap_server_id']);
	}
	
	private function initRoleMapping()
	{
		include_once './Services/LDAP/classes/class.ilLDAPRoleGroupMappingSettings.php';
		$this->role_mapping = ilLDAPRoleGroupMappingSettings::_getInstanceByServerId((int) $_GET['ldap_server_id']);
	}
	
	/**
	 * New implementation for InputForm
	 * @return 
	 * @param object $a_as_select[optional]
	 */
	private function prepareGlobalRoleSelection($a_as_select = true)
	{
		global $rbacreview,$ilObjDataCache;
		
		$global_roles = ilUtil::_sortIds($rbacreview->getGlobalRoles(),
			'object_data',
			'title',
			'obj_id');
		
		$select[0] = $this->lng->txt('links_select_one');
		foreach($global_roles as $role_id)
		{
			$select[$role_id] = ilObject::_lookupTitle($role_id);
		}
		return $select;
	}
	
	
	/**
	 * Used for old style table.
	 * @deprecated
	 * @return 
	 * @param object $a_as_select[optional]
	 */
	private function prepareRoleSelect($a_as_select = true)
	{
		global $rbacreview,$ilObjDataCache;
		
		include_once('./Services/LDAP/classes/class.ilLDAPAttributeMapping.php');

		$global_roles = ilUtil::_sortIds($rbacreview->getGlobalRoles(),
			'object_data',
			'title',
			'obj_id');
		
		$select[0] = $this->lng->txt('links_select_one');
		foreach($global_roles as $role_id)
		{
			$select[$role_id] = ilObject::_lookupTitle($role_id);
		}
		
		if($a_as_select)
		{
			return ilUtil::formSelect(ilLDAPAttributeMapping::_lookupGlobalRole($this->server->getServerId()),
				'global_role',$select,false,true);
		}
		else
		{
			return $select;
		}	
	}
	
		
	private function getMappingFields()
	{
		return array('gender' 	=> $this->lng->txt('gender'),
				'firstname'		=> $this->lng->txt('firstname'),
				'lastname'		=> $this->lng->txt('lastname'),
				'title'			=> $this->lng->txt('person_title'),
				'institution' 	=> $this->lng->txt('institution'),
				'department'	=> $this->lng->txt('department'),
				'street'		=> $this->lng->txt('street'),
				'city'			=> $this->lng->txt('city'),
				'zipcode'		=> $this->lng->txt('zipcode'),
				'country'		=> $this->lng->txt('country'),
				'phone_office'	=> $this->lng->txt('phone_office'),
				'phone_home'	=> $this->lng->txt('phone_home'),
				'phone_mobile'  => $this->lng->txt('phone_mobile'),
				'fax'			=> $this->lng->txt('fax'),
				'email'			=> $this->lng->txt('email'),
				'hobby'			=> $this->lng->txt('hobby'),
				'matriculation' => $this->lng->txt('matriculation'));
				#'photo'			=> $this->lng->txt('photo'));
	}
	
	private function initUserDefinedFields()
	{
		include_once("./Services/User/classes/class.ilUserDefinedFields.php");
		$this->udf = ilUserDefinedFields::_getInstance();
	}
	
	private function prepareMappingSelect()
	{
		return ilUtil::formSelect($_POST['mapping_template'],'mapping_template',array(0 => $this->lng->txt('ldap_mapping_template'),
													"inetOrgPerson" => 'inetOrgPerson',
													"organizationalPerson" => 'organizationalPerson',
													"person" => 'person',
													"ad_2003" => 'Active Directory (Win 2003)'),false,true);
	}
	
	/**
	 * Load mapping data in cas of copy
	 *
	 * @access private
	 * @param array mapping data
	 * @return array mapping_data
	 * 
	 */
	private function loadMappingCopy($a_mapping_data)
	{
	 	if(!isset($_GET['mapping_id']))
	 	{
	 		return $a_mapping_data;
	 	}
	 	$mapping_id = $_GET['mapping_id'];
	 	$a_mapping_data[0] = $a_mapping_data[$mapping_id];
	 	
	 	return $a_mapping_data;
	}
	
	/**
	 * Load info about hide/show details
	 *
	 * @access private
	 * 
	 */
	private function loadMappingDetails()
	{
	 	if(!isset($_SESSION['ldap_mapping_details']))
	 	{
	 		$_SESSION['ldap_mapping_details'] = array();
	 	}
	 	if(isset($_GET['details_show']))
	 	{
	 		$_SESSION['ldap_mapping_details'][$_GET['details_show']] = $_GET['details_show']; 
	 	}
	 	if(isset($_GET['details_hide']))
	 	{
	 		unset($_SESSION['ldap_mapping_details'][$_GET['details_hide']]);
	 	}
	}
	
	/**
	 * Sort mapping data by title
	 *
	 * @access private
	 * @param array mapping data
	 * 
	 */
	private function sortMappingData($a_mapping_data)
	{
		global $rbacreview,$ilObjDataCache;
	
		$new_mapping = array();
		$new_mapping = array();		
	 	foreach($a_mapping_data as $mapping_id => $data)
	 	{
	 		$new_mapping[$mapping_id] = $data;
	 		$new_mapping[$mapping_id]['obj_id'] = $obj_id = $rbacreview->getObjectOfRole($data['role']);
	 		$new_mapping[$mapping_id]['obj_title'] = $ilObjDataCache->lookupTitle($obj_id); 
			$new_mapping[$mapping_id]['mapping_id'] = $mapping_id;
	 	}
	 	return ilUtil::sortArray($new_mapping,'obj_title','DESC');
		
	}
	
	/**
	 * Init form table for new role assignments
	 *
	 * @param string mode edit | create
	 * @param object object of ilLDAPRoleAsssignmentRule
	 * @access protected
	 * 
	 */
	protected function initFormRoleAssignments($a_mode)
	{
	 	include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
	 	include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');
	 	
	 	$this->form = new ilPropertyFormGUI();
	 	$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	 	switch($a_mode)
	 	{
	 		case 'edit':
			 	$this->form->setTitle($this->lng->txt('ldap_edit_role_ass_rule'));
			 	$this->form->addCommandButton('updateRoleAssignment',$this->lng->txt('save'));
			 	$this->form->addCommandButton('roleAssignments',$this->lng->txt('cancel'));
			 	break;
	 		case 'create':
			 	$this->form->setTitle($this->lng->txt('ldap_add_role_ass_rule'));
			 	$this->form->addCommandButton('addRoleAssignment',$this->lng->txt('ldap_btn_add_role_ass'));
			 	$this->form->addCommandButton('roleAssignments',$this->lng->txt('cancel'));
			 	break;
	 	}

		// Role Selection
		$role = new ilRadioGroupInputGUI($this->lng->txt('ldap_ilias_role'),'role_name');
		$role->setRequired(true);
		
			$global = new ilRadioOption($this->lng->txt('ldap_global_role'),0);
			$role->addOption($global);
			
				$role_select = new ilSelectInputGUI('','role_id');
				$role_select->setOptions($this->prepareGlobalRoleSelection());
				$global->addSubItem($role_select);
			
			$local  = new ilRadioOption($this->lng->txt('ldap_local_role'),1);
			$role->addOption($local);
			
				include_once './Services/Form/classes/class.ilRoleAutoCompleteInputGUI.php';
				$role_search = new ilRoleAutoCompleteInputGUI('','role_search',$this,'addRoleAutoCompleteObject');
				$role_search->setSize(40);
				$local->addSubItem($role_search);

		$role->setInfo($this->lng->txt('ldap_role_name_info'));
		$this->form->addItem($role);
		
		// Update options
		$update = new ilNonEditableValueGUI($this->lng->txt('ldap_update_roles'),'update_roles');
		$update->setValue($this->lng->txt('ldap_check_role_assignment'));
		
			$add = new ilCheckboxInputGUI('','add_missing');
			$add->setOptionTitle($this->lng->txt('ldap_add_missing'));
			$update->addSubItem($add);
			
			$remove = new ilCheckboxInputGUI('','remove_deprecated');
			$remove->setOptionTitle($this->lng->txt('ldap_remove_deprecated'));
			$update->addSubItem($remove);
		
		$this->form->addItem($update);
		
		
	 	
	 	// Assignment Type
	 	$group = new ilRadioGroupInputGUI($this->lng->txt('ldap_assignment_type'),'type');
	 	#$group->setValue($current_rule->getType());
	 	$group->setRequired(true);
	 	
	 	// Option by group 
		 	$radio_group = new ilRadioOption($this->lng->txt('ldap_role_by_group'),ilLDAPRoleAssignmentRule::TYPE_GROUP);
		 	
		 	$dn = new ilTextInputGUI($this->lng->txt('ldap_group_dn'),'dn');
		 	#$dn->setValue($current_rule->getDN());
		 	$dn->setSize(32);
		 	$dn->setMaxLength(512);
		 	$dn->setInfo($this->lng->txt('ldap_role_grp_dn_info'));
		 	$radio_group->addSubItem($dn);
		 	$at = new ilTextInputGUI($this->lng->txt('ldap_role_grp_at'),'at');
		 	#$at->setValue($current_rule->getMemberAttribute());
		 	$at->setSize(16);
		 	$at->setMaxLength(128);
		 	$radio_group->addSubItem($at);
		 	$isdn = new ilCheckboxInputGUI($this->lng->txt('ldap_role_grp_isdn'),'isdn');
		 	#$isdn->setChecked($current_rule->isMemberAttributeDN());
		 	$isdn->setInfo($this->lng->txt('ldap_group_member_info'));
		 	$radio_group->addSubItem($isdn);
		 	$radio_group->setInfo($this->lng->txt('ldap_role_grp_info'));
	 	
	 	$group->addOption($radio_group);
	 	
	 	// Option by Attribute
		 	$radio_attribute = new ilRadioOption($this->lng->txt('ldap_role_by_attribute'),ilLDAPRoleAssignmentRule::TYPE_ATTRIBUTE);
		 	$name = new ilTextInputGUI($this->lng->txt('ldap_role_at_name'),'name');
		 	#$name->setValue($current_rule->getAttributeName());
		 	$name->setSize(32);
		 	$name->setMaxLength(128);
		 	#$name->setInfo($this->lng->txt('ldap_role_at_name_info'));
		 	$radio_attribute->addSubItem($name);
		 	
		 	// Radio Attribute
		 	$val = new ilTextInputGUI($this->lng->txt('ldap_role_at_value'),'value');
		 	#$val->setValue($current_rule->getAttributeValue());
		 	$val->setSize(32);
		 	$val->setMaxLength(128);
		 	#$val->setInfo($this->lng->txt('ldap_role_at_value_info'));
		 	$radio_attribute->addSubItem($val);
			$radio_attribute->setInfo($this->lng->txt('ldap_role_at_info'));

	 	$group->addOption($radio_attribute);
		
		// Option by Plugin
			$pl_active =  (bool) $this->hasActiveRoleAssignmentPlugins();
			$pl = new ilRadioOption($this->lng->txt('ldap_plugin'),3);
			$pl->setInfo($this->lng->txt('ldap_plugin_info'));
			$pl->setDisabled(!$pl_active);
			
			$id = new ilNumberInputGUI($this->lng->txt('ldap_plugin_id'),'plugin_id');
			$id->setDisabled(!$pl_active);
			$id->setSize(3);
			$id->setMaxLength(3);
			$id->setMaxValue(999);
			$id->setMinValue(1);
			$pl->addSubItem($id);

		$group->addOption($pl);
	 	$this->form->addItem($group);
	}
	
	/**
	 * Check if the plugin is active
	 * @return 
	 */
	private function hasActiveRoleAssignmentPlugins()
	{
		global $ilPluginAdmin;
		
		return count($ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE,'LDAP','ldaphk')) ? true : false;
	}
	
	
	/**
	* Add Member for autoComplete
	*/
	function addRoleAutoCompleteObject()
	{
		include_once("./Services/Form/classes/class.ilRoleAutoCompleteInputGUI.php");
		ilRoleAutoCompleteInputGUI::echoAutoCompleteList();
	}
	
}
?>