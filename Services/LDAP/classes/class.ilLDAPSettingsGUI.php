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
		global $ilAccess,$ilErr;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$ilErr->WARNING);
		}
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

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
			$table_gui->addCommandButton("updateRecords", $this->lng->txt("save"));
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
	 	if(!$_GET['rule_id'])
	 	{
	 		ilUtil::sendinfo($this->lng->txt('select_one'));
	 		$this->roleAssignments();
	 		return false;
	 	}
	 	$this->setSubTabs();
		$this->tabs_gui->setSubTabActive('ldap_role_assignments');

		$this->ctrl->saveParameter($this,'rule_id',(int) $_GET['rule_id']);
	 	include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');
	 	$this->initFormRoleAssignments('edit',
	 		$this->role_mapping_rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId((int) $_GET['rule_id']));
	 	$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * update role assignment
	 *
	 * @access public
	 * 
	 */
	public function updateRoleAssignment()
	{
	 	global $ilErr;
	 	
	 	if(!$_GET['rule_id'])
	 	{
	 		ilUtil::sendInfo($this->lng->txt('select_one'));
	 		$this->roleAssignments();
	 		return false;
	 	}
	 	include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');
 		$role_assignment = ilLDAPRoleAssignmentRule::_getInstanceByRuleId((int) $_GET['rule_id']);
		$role_assignment->setDN(ilUtil::stripSlashes($_POST['dn']));
		$role_assignment->setMemberAttribute(ilUtil::stripSlashes($_POST['at']));
		$role_assignment->setMemberIsDN((int) $_POST['isdn']);
		$role_assignment->setAttributeName(ilUtil::stripSlashes($_POST['name']));
		$role_assignment->setAttributeValue(ilUtil::stripSlashes($_POST['value']));
		$role_assignment->setRoleId((int) $_POST['role']);
		$role_assignment->setType((int) $_POST['type']);
		
		if(!$role_assignment->validate())
		{		
			ilUtil::sendInfo($this->lng->txt($ilErr->getMessage()));
			$this->editRoleAssignment();
			return false;
		}
		
		$role_assignment->update();
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->roleAssignments();
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
	 		ilUtil::sendInfo($this->lng->txt('select_one'));
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
	 		ilUtil::sendInfo($this->lng->txt('select_once'));
	 		$this->roleAssignments();
	 		return false;
	 	}
		include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');
		foreach($_POST["rule_ids"] as $rule_id)
		{
			$rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($rule_id);
			$rule->delete();
		}
		ilUtil::sendInfo($this->lng->txt('ldap_deleted_rule'));
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
		global $ilErr;
		
		include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		
		$role_assignment = ilLDAPRoleAssignmentRule::_getInstanceByRuleId(0);
		$role_assignment->setServerId(ilLDAPServer::_getFirstActiveServer());
		$role_assignment->setDN(ilUtil::stripSlashes($_POST['dn']));
		$role_assignment->setMemberAttribute(ilUtil::stripSlashes($_POST['at']));
		$role_assignment->setMemberIsDN((int) $_POST['isdn']);
		$role_assignment->setAttributeName(ilUtil::stripSlashes($_POST['name']));
		$role_assignment->setAttributeValue(ilUtil::stripSlashes($_POST['value']));
		$role_assignment->setRoleId((int) $_POST['role']);
		$role_assignment->setType((int) $_POST['type']);
		
		if(!$role_assignment->validate())
		{		
			ilUtil::sendInfo($this->lng->txt($ilErr->getMessage()));
			$this->roleAssignments();
			return false;
		}
		
		$role_assignment->create();
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		unset($_POST);
		$this->roleAssignments();
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
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->roleMapping();
			return false;
		}
		
		$this->initRoleMapping();
		
		foreach($_POST['mappings'] as $mapping_id)
		{
			$this->role_mapping->delete($mapping_id);
		}
		ilUtil::sendInfo($this->lng->txt('ldap_deleted_role_mapping'));
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
			ilUtil::sendInfo($ilErr->getMessage());
			$this->roleMapping();
			return false;				
		}
		$this->role_mapping->save();

		ilUtil::sendInfo($this->lng->txt('settings_saved'));
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
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		unset($_POST['mapping_template']);
		return;
	}
	
	public function serverList()
	{
		$this->setSubTabs();
		$this->tabs_gui->setSubTabActive('ldap_settings');
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.ldap_server_list.html','Services/LDAP');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		
		// Text variables
		$this->tpl->setVariable("TXT_LDAP_SERVER_SETTINGS",$this->lng->txt('ldap_configure'));
		$this->tpl->setVariable('TXT_ACTIVE',$this->lng->txt('auth_ldap_enable'));
		$this->tpl->setVariable('TXT_SERVER_NAME',$this->lng->txt('ldap_server_name'));
		$this->tpl->setVariable('TXT_SERVER_URL',$this->lng->txt('ldap_server'));
		$this->tpl->setVariable('TXT_SERVER_VERSION',$this->lng->txt('ldap_version'));
		$this->tpl->setVariable('TXT_BASE_DN',$this->lng->txt('basedn'));
		$this->tpl->setVariable('TXT_REFERRALS',$this->lng->txt('ldap_referrals'));
		
		$this->tpl->setVariable('TXT_SECURITY_SETTINGS',$this->lng->txt('ldap_server_security_settings'));
		$this->tpl->setVariable('TXT_TLS',$this->lng->txt('ldap_tls'));
		$this->tpl->setVariable('TXT_BINDING',$this->lng->txt('ldap_server_binding'));
		$this->tpl->setVariable('TXT_ANONYMOUS',$this->lng->txt('ldap_bind_anonymous'));
		$this->tpl->setVariable('TXT_USER',$this->lng->txt('ldap_bind_user'));
		$this->tpl->setVariable('TXT_BIND_DN',$this->lng->txt('ldap_server_bind_dn'));
		$this->tpl->setVariable('TXT_BIND_PASSWD',$this->lng->txt('ldap_server_bind_pass'));
		
		$this->tpl->setVariable('TXT_AUTH_SETTINGS',$this->lng->txt('ldap_authentication_settings'));
		$this->tpl->setVariable('TXT_USER_ATTRIBUTE',$this->lng->txt('ldap_user_attribute'));
		$this->tpl->setVariable('TXT_USER_SCOPE',$this->lng->txt('ldap_user_scope'));
		$this->tpl->setVariable('TXT_SEARCH_BASE',$this->lng->txt('ldap_user_dn'));
		$this->tpl->setVariable('TXT_FILTER',$this->lng->txt('ldap_search_filter'));
		$this->tpl->setVariable('TXT_REQUIRED_FLD',$this->lng->txt('required_field'));
		
		// Group restrictions
		$this->tpl->setVariable('TXT_GROUP_RESTRICTIONS',$this->lng->txt('ldap_group_restrictions'));
		$this->tpl->setVariable('TXT_GROUP_DN',$this->lng->txt('ldap_group_search_base'));
		$this->tpl->setVariable('TXT_GROUP_SCOPE',$this->lng->txt('ldap_group_scope'));
		$this->tpl->setVariable('TXT_GROUP_FILTER',$this->lng->txt('ldap_group_filter'));
		$this->tpl->setVariable('TXT_MEMBER',$this->lng->txt('ldap_group_member'));
		$this->tpl->setVariable('TXT_MEMBERISDN',$this->lng->txt('ldap_memberisdn'));
		$this->tpl->setVariable('TXT_GROUP',$this->lng->txt('ldap_group_name'));
		$this->tpl->setVariable('TXT_GROUP_ATTRIBUTE',$this->lng->txt('ldap_group_attribute'));
		$this->tpl->setVariable('TXT_GROUP_MEMBERSHIP',$this->lng->txt('ldap_group_membership'));
		$this->tpl->setVariable('TXT_OPTIONAL',$this->lng->txt('ldap_group_member_optional'));
		$this->tpl->setVariable('TXT_GROUP_USER_FILTER',$this->lng->txt('ldap_group_user_filter'));
		$this->tpl->setVariable('TXT_OPTIONAL_INFO',$this->lng->txt('ldap_group_optional_info'));
		
		// User Sync
		$this->tpl->setVariable('TXT_USER_SYNC',$this->lng->txt('ldap_user_sync'));
		$this->tpl->setVariable('TXT_MOMENT',$this->lng->txt('ldap_moment_sync'));
		$this->tpl->setVariable('TXT_LOGIN',$this->lng->txt('ldap_sync_login'));
		$this->tpl->setVariable('TXT_CRON',$this->lng->txt('ldap_sync_cron'));
		$this->tpl->setVariable('TXT_GLOBAL_ROLE',$this->lng->txt('ldap_global_role_assignment'));

		$this->tpl->setVariable('TXT_SAVE',$this->lng->txt('save'));
		
		// Info text
		$this->tpl->setVariable('TXT_SERVER_NAME_INFO',$this->lng->txt('ldap_server_name_info'));
		$this->tpl->setVariable('TXT_SERVER_URL_INFO',$this->lng->txt('ldap_server_url_info'));
		$this->tpl->setVariable('TXT_SERVER_VERSION_INFO',$this->lng->txt('ldap_server_version_info'));
		$this->tpl->setVariable('TXT_REFERRALS_INFO',$this->lng->txt('ldap_referrals_info'));
		$this->tpl->setVariable('TXT_SEARCH_BASE_INFO',$this->lng->txt('ldap_search_base_info'));
		$this->tpl->setVariable('TXT_FILTER_INFO',$this->lng->txt('ldap_filter_info'));
		$this->tpl->setVariable('TXT_GROUP_DN_INFO',$this->lng->txt('ldap_group_dn_info'));
		$this->tpl->setVariable('TXT_GROUP_FILTER_INFO',$this->lng->txt('ldap_group_filter_info'));
		$this->tpl->setVariable('TXT_MEMBER_INFO',$this->lng->txt('ldap_group_member_info'));
		$this->tpl->setVariable('TXT_GROUP_INFO',$this->lng->txt('ldap_group_name_info'));
		$this->tpl->setVariable('TXT_GROUP_ATTRIBUTE_INFO',$this->lng->txt('ldap_group_attribute_info'));
		$this->tpl->setVariable('TXT_GROUP_SCOPE_INFO',$this->lng->txt('ldap_group_scope_info'));
		$this->tpl->setVariable('TXT_USER_SCOPE_INFO',$this->lng->txt('ldap_user_scope_info'));
		$this->tpl->setVariable('TXT_USER_SYNC_INFO',$this->lng->txt('ldap_user_sync_info'));
		$this->tpl->setVariable('TXT_GLOBAL_ROLE_INFO',$this->lng->txt('ldap_global_role_info'));
		
		
		// Settings
		$this->tpl->setVariable('CHECK_ACTIVE',ilUtil::formCheckbox($this->server->isActive() ? true : false,'active',1));
		$this->tpl->setVariable('SERVER_NAME',$this->server->getName());
		$this->tpl->setVariable('SERVER_URL',$this->server->getUrlString());
		$this->tpl->setVariable('SELECT_VERSION',ilUtil::formSelect($this->server->getVersion(),
			'version',array(2 => 2,3 => 3),false,true));
		$this->tpl->setVariable('BASE_DN',$this->server->getBaseDN());
		$this->tpl->setVariable('CHECK_REFERRALS',ilUtil::formCheckbox($this->server->isActiveReferrer() ? true : false,'referrals',1));
		$this->tpl->setVariable('CHECK_TLS',ilUtil::formCheckbox($this->server->isActiveTLS() ? true : false,'tls',1));
					
		$this->tpl->setVariable('RADIO_ANONYMOUS',ilUtil::formRadioButton($this->server->getBindingType() == IL_LDAP_BIND_ANONYMOUS ? true : false,
			'binding_type',IL_LDAP_BIND_ANONYMOUS));
		$this->tpl->setVariable('RADIO_USER',ilUtil::formRadioButton($this->server->getBindingType() == IL_LDAP_BIND_USER ? true : false,
			'binding_type',IL_LDAP_BIND_USER));
		$this->tpl->setVariable('BIND_DN',$this->server->getBindUser());
		$this->tpl->setVariable('BIND_PASS',$this->server->getBindPassword());
		
		$this->tpl->setVariable('SEARCH_BASE',$this->server->getSearchBase());
		$this->tpl->setVariable('USER_ATTRIBUTE',$this->server->getUserAttribute());
		$this->tpl->setVariable('SELECT_USER_SCOPE',ilUtil::formSelect($this->server->getUserScope(),
			'user_scope',
			array(IL_LDAP_SCOPE_ONE => $this->lng->txt('ldap_scope_one'),
				IL_LDAP_SCOPE_SUB => $this->lng->txt('ldap_scope_sub')),false,true));
		$this->tpl->setVariable('FILTER',$this->server->getFilter());
		$this->tpl->setVariable('GROUP_DN',$this->server->getGroupDN());
		$this->tpl->setVariable('SELECT_GROUP_SCOPE',ilUtil::formSelect($this->server->getGroupScope(),
			'group_scope',
			array(IL_LDAP_SCOPE_ONE => $this->lng->txt('ldap_scope_one'),
				IL_LDAP_SCOPE_SUB => $this->lng->txt('ldap_scope_sub')),false,true));
		$this->tpl->setVariable('GROUP_FILTER',$this->server->getGroupFilter());
		$this->tpl->setVariable('GROUP_MEMBER',$this->server->getGroupMember());
		$this->tpl->setVariable('CHECK_MEMBERISDN',ilUtil::formCheckbox($this->server->enabledGroupMemberIsDN() ? 1 : 0,'memberisdn',1));
		$this->tpl->setVariable('GROUP',$this->server->getGroupName());
		$this->tpl->setVariable('GROUP_ATTRIBUTE',$this->server->getGroupAttribute());
		$this->tpl->setVariable('GROUP_USER_FILTER',$this->server->getGroupUserFilter());
		
		$this->tpl->setVariable('CHECK_OPTIONAL',ilUtil::formCheckbox($this->server->isMembershipOptional() ? 1 : 0,
			'group_optional',
			1));
		// User sync
		$this->tpl->setVariable('CHECK_LOGIN',ilUtil::formCheckbox($this->server->enabledSyncOnLogin() ? true : false,
			'sync_on_login',
			1));
		$this->tpl->setVariable('CHECK_CRON',ilUtil::formCheckbox($this->server->enabledSyncPerCron() ? true : false,
			'sync_per_cron',
			1));
		$this->tpl->setVariable('SELECT_GLOBAL_ROLE',$this->prepareRoleSelect());

		return true;
	}
	
	/* 
 	 * Update Settings
	 */
	function save()
	{
		global $ilErr;
		
		$this->server->toggleActive((int) $_POST['active']);
		$this->server->setName(ilUtil::stripSlashes($_POST['server_name']));
		$this->server->setUrl(ilUtil::stripSlashes($_POST['server_url']));
		$this->server->setVersion(ilUtil::stripSlashes($_POST['version']));
		$this->server->setBaseDN(ilUtil::stripSlashes($_POST['base_dn']));
		$this->server->toggleReferrer(ilUtil::stripSlashes($_POST['referrals']));
		$this->server->toggleTLS(ilUtil::stripSlashes($_POST['tls']));
		$this->server->setBindingType((int) $_POST['binding_type']);
		$this->server->setBindUser(ilUtil::stripSlashes($_POST['bind_dn']));
		$this->server->setBindPassword(ilUtil::stripSlashes($_POST['bind_pass']));
		$this->server->setSearchBase(ilUtil::stripSlashes($_POST['search_base']));
		$this->server->setUserScope((int) $_POST['user_scope']);
		$this->server->setUserAttribute(ilUtil::stripSlashes($_POST['user_attribute']));
		$this->server->setFilter(ilUtil::stripSlashes($_POST['filter']));
		$this->server->setGroupDN(ilUtil::stripSlashes($_POST['group_dn']));
		$this->server->setGroupScope((int) $_POST['group_scope']);
		$this->server->setGroupFilter(ilUtil::stripSlashes($_POST['group_filter']));
		$this->server->setGroupMember(ilUtil::stripSlashes($_POST['group_member']));
		$this->server->enableGroupMemberIsDN((int) $_POST['memberisdn']);
		$this->server->setGroupName(ilUtil::stripSlashes($_POST['group']));
		$this->server->setGroupAttribute(ilUtil::stripSlashes($_POST['group_attribute']));
		$this->server->setGroupUserFilter(ilUtil::stripSlashes($_POST['group_user_filter']));
		$this->server->toggleMembershipOptional((int) $_POST['group_optional']);
		$this->server->enableSyncOnLogin((int) $_POST['sync_on_login']);
		$this->server->enableSyncPerCron((int) $_POST['sync_per_cron']);
		$this->server->setGlobalRole((int) $_POST['global_role']);
		
		if(!$this->server->validate())
		{
			ilUtil::sendInfo($ilErr->getMessage());
			$this->serverList();
			return false;
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
		$this->mapping->setRule('global_role',(int) $_POST['global_role'],false);
		$this->mapping->save();

		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->serverList();
		return true;
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
		include_once("classes/class.ilUserDefinedFields.php");
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
	protected function initFormRoleAssignments($a_mode,ilLDAPRoleAssignmentRule $current_rule)
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

	 	$role = new ilSelectInputGUI($this->lng->txt('ldap_ilias_role'),'role');
	 	$role->setValue($current_rule->getRoleId());
	 	$role->setOptions($this->prepareRoleSelect(false));
	 	$role->setRequired(true);
	 	$this->form->addItem($role);
	 	
	 	// Radio group
	 	$group = new ilRadioGroupInputGUI($this->lng->txt('ldap_assignment_type'),'type');
	 	$group->setValue($current_rule->getType());
	 	$group->setRequired(true);
	 	
	 	// Radio Group 
	 	$radio_group = new ilRadioOption($this->lng->txt('ldap_role_by_group'),ilLDAPRoleAssignmentRule::TYPE_GROUP);
	 	
	 	// Group DN
	 	$dn = new ilTextInputGUI($this->lng->txt('ldap_group_dn'),'dn');
	 	$dn->setValue($current_rule->getDN());
	 	$dn->setSize(32);
	 	$dn->setMaxLength(512);
	 	$dn->setInfo($this->lng->txt('ldap_role_grp_dn_info'));
	 	$radio_group->addSubItem($dn);
	 	$at = new ilTextInputGUI($this->lng->txt('ldap_role_grp_at'),'at');
	 	$at->setValue($current_rule->getMemberAttribute());
	 	$at->setSize(16);
	 	$at->setMaxLength(128);
	 	$radio_group->addSubItem($at);
	 	$isdn = new ilCheckboxInputGUI($this->lng->txt('ldap_role_grp_isdn'),'isdn');
	 	$isdn->setChecked($current_rule->isMemberAttributeDN());
	 	$isdn->setInfo($this->lng->txt('ldap_group_member_info'));
	 	$radio_group->addSubItem($isdn);
	 	$radio_group->setInfo($this->lng->txt('ldap_role_grp_info'));
	 	
	 	$group->addOption($radio_group);
	 	
	 	// Radio Attribute
	 	$radio_attribute = new ilRadioOption($this->lng->txt('ldap_role_by_attribute'),ilLDAPRoleAssignmentRule::TYPE_ATTRIBUTE);
	 	$name = new ilTextInputGUI($this->lng->txt('ldap_role_at_name'),'name');
	 	$name->setValue($current_rule->getAttributeName());
	 	$name->setSize(32);
	 	$name->setMaxLength(128);
	 	#$name->setInfo($this->lng->txt('ldap_role_at_name_info'));
	 	$radio_attribute->addSubItem($name);
	 	
	 	// Radio Attribute
	 	$val = new ilTextInputGUI($this->lng->txt('ldap_role_at_value'),'value');
	 	$val->setValue($current_rule->getAttributeValue());
	 	$val->setSize(32);
	 	$val->setMaxLength(128);
	 	#$val->setInfo($this->lng->txt('ldap_role_at_value_info'));
	 	$radio_attribute->addSubItem($val);
		$radio_attribute->setInfo($this->lng->txt('ldap_role_at_info'));
	 	$group->addOption($radio_attribute);
	 	
	 	$this->form->addItem($group);
	}
}
?>