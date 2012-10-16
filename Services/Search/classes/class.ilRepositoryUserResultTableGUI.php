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
	protected static $all_selectable_cols = NULL;
	protected $admin_mode;
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_admin_mode = false)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilUser;

		$this->admin_mode = (bool)$a_admin_mode;

		$this->setId("rep_search_".$ilUser->getId());
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn("", "", "1", true);

		$all_cols = $this->getSelectableColumns();
		foreach($this->getSelectedColumns() as $col)
		{
			$this->addColumn($all_cols[$col]['txt'], $col);
		}

		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj));
		$this->setRowTemplate("tpl.rep_search_usr_result_row.html", "Services/Search");
		$this->setTitle($this->lng->txt('search_results'));
		$this->setEnableTitle(true);
		$this->setDefaultOrderField("login");
		$this->setDefaultOrderDirection("asc");
		$this->enable('select_all');
		$this->setSelectAllCheckbox("user[]");				
	}

	/**
	 * Get all selectable columns
	 *
	 * @return array
	 *
	 * @global ilRbacReview $rbacreview
	 */
	public function  getSelectableColumns()
	{
		global $rbacreview, $ilUser;

		if(self::$all_selectable_cols)
		{
			return self::$all_selectable_cols;
		}
		include_once './Services/Search/classes/class.ilUserSearchOptions.php';
		return ilUserSearchOptions::getSelectableColumnInfo($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID));
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
		$this->addMultiItemSelectionButton('member_type', $a_commands, 'addUser', $this->lng->txt('btn_add'));
		return true;
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $ilCtrl, $lng;

		$this->tpl->setVariable("VAL_ID", $a_set["usr_id"]);
		foreach($this->getSelectedColumns() as $field)
		{			
			switch($field)
			{				
				case 'gender':
					$a_set['gender'] = $a_set['gender'] ? $this->lng->txt('gender_' . $a_set['gender']) : '';
					$this->tpl->setCurrentBlock('custom_fields');
					$this->tpl->setVariable('VAL_CUST', $a_set[$field]);
					$this->tpl->parseCurrentBlock();
					break;

				case 'birthday':
					$a_set['birthday'] = $a_set['birthday'] ? ilDatePresentation::formatDate(new ilDate($a_set['birthday'], IL_CAL_DATE)) : $this->lng->txt('no_date');
					$this->tpl->setCurrentBlock('custom_fields');
					$this->tpl->setVariable('VAL_CUST', $a_set[$field]);
					$this->tpl->parseCurrentBlock();
					break;

				case 'login':
					if($this->admin_mode)
					{
						$ilCtrl->setParameterByClass("ilobjusergui", "ref_id", "7");
						$ilCtrl->setParameterByClass("ilobjusergui", "obj_id", $a_set["usr_id"]);
						$ilCtrl->setParameterByClass("ilobjusergui", "search", "1");
						$link = $ilCtrl->getLinkTargetByClass(array("iladministrationgui", "ilobjusergui"), "view");
						$a_set[$field] = "<a href=\"".$link."\">".$a_set[$field]."</a>";												
					}					
					// fallthrough
				
				default:
					$this->tpl->setCurrentBlock('custom_fields');
					$this->tpl->setVariable('VAL_CUST', (string) ($a_set[$field] ? $a_set[$field] : ''));
					$this->tpl->parseCurrentBlock();
					break;
			}
		}

	}
	
	/**
	 * Parse user data
	 * @return 
	 * @param array $a_user_ids
	 */
	public function parseUserIds($a_user_ids)
	{
		if(!$a_user_ids)
		{
			$this->setData(array());
			return true;
		}

		$additional_fields = $this->getSelectedColumns();

		$udf_ids = $usr_data_fields = $odf_ids = array();
		foreach($additional_fields as $field)
		{
			if(substr($field, 0, 3) == 'udf')
			{
				$udf_ids[] = substr($field,4);
				continue;
			}
			$usr_data_fields[] = $field;
		}
		include_once './Services/User/classes/class.ilUserQuery.php';
		$usr_data = ilUserQuery::getUserListData(
				'login',
				'ASC',
				0,
				999999,
				'',
				'',
				null,
				false,
				false,
				0,
				0,
				null,
				$usr_data_fields,
				$a_user_ids
		);

		// Custom user data fields
		if($udf_ids)
		{
			include_once './Services/User/classes/class.ilUserDefinedData.php';
			$data = ilUserDefinedData::lookupData($a_user_ids, $udf_ids);

			$users = array();
			$counter = 0;
			foreach($usr_data['set'] as $set)
			{
				$users[$counter] = $set;
				foreach($udf_ids as $udf_field)
				{
					$users[$counter]['udf_'.$udf_field] = $data[$set['usr_id']][$udf_field];
				}
				++$counter;
			}
		}
		else
		{
			$users = $usr_data['set'];
		}
		$this->setData($users);
	}

}
?>
