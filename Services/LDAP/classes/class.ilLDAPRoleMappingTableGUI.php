<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/Table/classes/class.ilTable2GUI.php');

/** 
* 
* @author Fabian Wolf <wolf@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesLDAP 
*/
class ilLDAPRoleMappingTableGUI extends ilTable2GUI
{
	public function __construct($a_parent_obj,$a_server_id ,$a_parent_cmd = '')
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	$this->server_id = $a_server_id;
		
	 	parent::__construct($a_parent_obj,$a_parent_cmd);
	 	$this->addColumn("");
		$this->addColumn($this->lng->txt('title'), "role");
		$this->addColumn($this->lng->txt('obj_role'), "role");
		$this->addColumn($this->lng->txt('ldap_group_dn'), "dn");
		$this->addColumn($this->lng->txt('ldap_server'), "url");
		$this->addColumn($this->lng->txt('ldap_group_member'), "member_attribute");
		$this->addColumn($this->lng->txt('ldap_info_text'), "info");
		$this->addColumn($this->lng->txt('action'));
	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.ldap_role_mapping_row.html","Services/LDAP");
		$this->setDefaultOrderField('title');
		$this->setDefaultOrderDirection("desc");
		$this->addMultiCommand('confirmDeleteRoleMapping', $this->lng->txt("delete"));
		
		$this->getItems();
	}
	
	/**
	 * fill row
	 * @global type $ilObjDataCache
	 * @global ilRbacReview $rbacreview
	 * @param array $a_set 
	 */
	function fillRow($a_set)
	{
		global $ilObjDataCache, $rbacreview;
		$title = $ilObjDataCache->lookupTitle($rbacreview->getObjectOfRole($a_set["role"]));
		$this->tpl->setVariable("VAL_ID", $a_set['mapping_id']);
		$this->tpl->setVariable("VAL_TITLE", ilUtil::shortenText($title,30,true));
		$this->tpl->setVariable("VAL_ROLE", $a_set["role_name"]);
		$this->tpl->setVariable("VAL_GROUP", $a_set["dn"]);
		$this->tpl->setVariable("VAL_URL", $a_set["url"]);
		$this->tpl->setVariable("VAL_MEMBER", $a_set["member_attribute"]);
		$this->tpl->setVariable("VAL_INFO",ilUtil::prepareFormOutput($a_set['info']));
		$this->ctrl->setParameter($this->getParentObject(),'mapping_id', $a_set['mapping_id']);
		$this->tpl->setVariable("EDIT_URL", $this->ctrl->getLinkTarget($this->getParentObject(),'addRoleMapping'));
		$this->tpl->setVariable("EDIT_TXT", $this->lng->txt('copy'));
		$this->ctrl->setParameter($this->getParentObject(),'mapping_id', $a_set['mapping_id']);
		$this->tpl->setVariable("COPY_URL", $this->ctrl->getLinkTarget($this->getParentObject(),'editRoleMapping'));
		$this->tpl->setVariable("COPY_TXT", $this->lng->txt('edit'));
	}
	
	/**
	 * get items from db 
	 */
	function getItems()
	{
		include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMappingSettings.php');
		$mapping_instance = ilLDAPRoleGroupMappingSettings::_getInstanceByServerId($this->server_id);
		$this->setData($mapping_instance->getMappings());
	}
}
?>
