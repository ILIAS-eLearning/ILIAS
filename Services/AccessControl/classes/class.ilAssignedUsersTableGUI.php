<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for role administration
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesAccessControl
*/
class ilAssignedUsersTableGUI extends ilTable2GUI
{
	protected $role_id;
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_role_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $rbacsystem;
		
		$this->setId("rbac_ua_".$a_role_id);
		$this->role_id = $a_role_id;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
//		$this->setTitle($this->lng->txt("users"));
		
		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("login"), "login", "29%");
		$this->addColumn($this->lng->txt("firstname"), "firstname", "29%");
		$this->addColumn($this->lng->txt("lastname"), "lastname", "29%");
		$this->addColumn($this->lng->txt('actions'),'','13%');
		
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.user_assignment_row.html", "Services/AccessControl");

		$this->setEnableTitle(true);
		$this->setDefaultOrderField("login");
		$this->setDefaultOrderDirection("asc");

		$this->setSelectAllCheckbox("user_id[]");

		$this->addMultiCommand("deassignUser", $lng->txt("delete"));
		$this->getItems();
	}
	
	/**
	 * get current role id
	 * @return 
	 */
	public function getRoleId()
	{
		return $this->role_id;
	}
	
	/**
	* Get user items
	*/
	function getItems()
	{
		global $lng,$rbacreview;
		
		$this->determineOffsetAndOrder();
		
		include_once("./Services/User/classes/class.ilUserQuery.php");

		$usr_data = ilUserQuery::getUserListData(
			ilUtil::stripSlashes($this->getOrderField()),
			ilUtil::stripSlashes($this->getOrderDirection()),
			ilUtil::stripSlashes($this->getOffset()),
			ilUtil::stripSlashes($this->getLimit()),
			'',
			'',
			null,
			false,
			false,
			0,
			$this->getRoleId()
			);
		
		if($rbacreview->isAssigned(SYSTEM_USER_ID, $this->getRoleId()))
		{
			$this->setMaxCount($usr_data["cnt"] - 1);
		}
		else
		{
			$this->setMaxCount($usr_data["cnt"]);
		}
		$this->setData($usr_data["set"]);
	}
	
	
	
	/**
	* Fill table row
	*/
	protected function fillRow($user)
	{
		global $ilCtrl, $lng;

		$this->tpl->setVariable("VAL_LOGIN", $user["login"]);
		$this->tpl->setVariable("VAL_FIRSTNAME", $user["firstname"]);
		$this->tpl->setVariable("VAL_LASTNAME", $user["lastname"]);
		
		if($user['usr_id'] != SYSTEM_USER_ID and
			($user['usr_id'] != ANONYMOUS_USER_ID or $this->getRoleId() != ANONYMOUS_ROLE_ID))
		{
			$this->tpl->setVariable("ID", $user["usr_id"]);	
		}
		
		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$actions = new ilAdvancedSelectionListGUI();
		$actions->setSelectionHeaderClass("small");
		$actions->setItemLinkClass("small");
		
		$actions->setListTitle($lng->txt('actions'));
		$actions->setId($user['usr_id']);

		$link_contact = "ilias.php?baseClass=ilMailGUI&type=new&rcp_to=".urlencode($user["login"]);
		$actions->addItem(
			$lng->txt('message'),
			'',
			$link_contact
		);
		
		if(strtolower($_GET["baseClass"]) == 'iladministrationgui' && $_GET["admin_mode"] == "settings")
		{
			$ilCtrl->setParameterByClass("ilobjusergui", "ref_id", 7);
			$ilCtrl->setParameterByClass("ilobjusergui", "obj_id", $user["usr_id"]);
			$link_change = $ilCtrl->getLinkTargetByClass(array("iladministrationgui", "ilobjusergui"), "view");
			$actions->addItem(
				$this->lng->txt("edit"),
				'',
				$link_change
			);
		}
		
		if(($this->getRoleId() != SYSTEM_ROLE_ID or $user['usr_id'] != SYSTEM_USER_ID) and
			($this->getRoleId() != ANONYMOUS_ROLE_ID or $user['usr_id'] != ANONYMOUS_USER_ID))
		{
			$ilCtrl->setParameter($this->getParentObject(), "user_id", $user["usr_id"]);
			$link_leave = $ilCtrl->getLinkTarget($this->getParentObject(),"deassignUser");
			
			$actions->addItem(
				$this->lng->txt('remove'),
				'',
				$link_leave
			);
		}
		
		$this->tpl->setVariable('VAL_ACTIONS',$actions->getHTML());
	}

}
?>
