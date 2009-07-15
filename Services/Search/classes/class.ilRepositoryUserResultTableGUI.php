<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class user search results
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesSearch
*/
class ilRepositoryUserResultTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("login"), "login", "33%");
		$this->addColumn($this->lng->txt("firstname"), "firstname", "33%");
		$this->addColumn($this->lng->txt("lastname"), "lastname", "33%");
		
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj));
		$this->setRowTemplate("tpl.rep_search_usr_result_row.html", "Services/Search");
		$this->setTitle($this->lng->txt('search_results'));
		$this->setEnableTitle(true);
		$this->setId("user_table");
		$this->setDefaultOrderField("login");
		$this->setDefaultOrderDirection("asc");
		$this->enable('select_all');
		$this->setSelectAllCheckbox("user[]");
	}
	
	/**
	 * Init multi commands
	 * @return 
	 */
	public function initMultiCommands($a_commands)
	{
		if(!count($a_commands))
		{
			$this->addMultiCommand('addUser', $this->lng->txt('btn_add'));
			return true;
		}
		$this->addMultiItemSelectionButton('memberType', $a_commands, 'addUser', $this->lng->txt('btn_add'));
		return true;
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
		$this->tpl->setVariable("VAL_ID", $user["id"]);
	}
	
	/**
	 * Parse user data
	 * @return 
	 * @param array $a_user_ids
	 */
	public function parseUserIds($a_user_ids)
	{
		include_once './Services/User/classes/class.ilObjUser.php';
		foreach($a_user_ids as $usr_id)
		{
			$name = ilObjUser::_lookupName($usr_id);
			$row['login'] = $name['login'];
			$row['lastname'] = $name['lastname'];
			$row['firstname'] = $name['firstname'];
			$row['id'] = $usr_id;
			
			$data[] = $row;
			
		}
		$this->setData($data ? $data : array());
	}

}
?>
