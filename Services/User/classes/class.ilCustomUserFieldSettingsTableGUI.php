<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for custom defined user fields
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesUser
*/
class ilCustomUserFieldSettingsTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($lng->txt("user_defined_list"));
		$this->setLimit(9999);
		
		//$this->addColumn($this->lng->txt("usrs_group"), "");
		$this->addColumn($this->lng->txt("user_field"), "");
		$this->addColumn($this->lng->txt("access"), "");
		$this->addColumn($this->lng->txt("export")." / ".$this->lng->txt("search"), "");
		$this->addColumn($this->lng->txt("actions"), "");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.std_fields_settings_row.html", "Services/User");
		$this->disable("footer");
		$this->setEnableTitle(true);

		$user_field_definitions = ilUserDefinedFields::_getInstance();
		$fds = $user_field_definitions->getDefinitions();

		foreach ($fds as $k => $f)
		{
			$fds[$k]["key"] = $k;
		}
		$this->setData($fds);
		$this->addCommandButton("updateFields", $lng->txt("save"));
		$this->addCommandButton("chooseFieldType", $lng->txt("add_user_defined_field"));
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilSetting, $ilCtrl;
		
		$field = $a_set["field_id"];
		
		$props = array("visible" => "user_visible_in_profile",
			"changeable" => "changeable",
			"searchable" => "header_searchable",
			"required"  => "required_field",
			"export" => "export",
			"course_export" => "course_export",
			"registration" => "header_visible_registration");
//var_dump($a_set);
		foreach ($props as $prop => $lv)
		{
			$up_prop = strtoupper($prop);

			if ($a_set["field_type"] != UDF_TYPE_WYSIWYG ||
				($prop != "searchable"))
			{
				$this->tpl->setCurrentBlock($prop);
				$this->tpl->setVariable("HEADER_".$up_prop,
					$lng->txt($lv));
				$this->tpl->setVariable("PROFILE_OPTION_".$up_prop, $prop."_".$field);
				
				// determine checked status
				$checked = false;
				if ($a_set[$prop])
				{
					$checked = true;
				}
				if ($prop == "registration" && $a_set["visible_registration"])
				{
					$checked = true;
				}
				if ($this->confirm_change == 1)	// confirm value
				{
					$checked = $_POST["chb"][$prop."_".$field];
				}
	
				if ($checked)
				{
					$this->tpl->setVariable("CHECKED_".$up_prop, " checked=\"checked\"");
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		
		// actions
		$ilCtrl->setParameter($this->parent_obj, 'field_id', $a_set["field_id"]);
		$this->tpl->setCurrentBlock("action");
		switch($a_set['field_type'])
		{
			case UDF_TYPE_TEXT:
				$this->tpl->setVariable("HREF_CMD",
					$ilCtrl->getLinkTarget($this->parent_obj, 'editTextField'));
				break;

			case UDF_TYPE_WYSIWYG:
				$this->tpl->setVariable("HREF_CMD",
					$ilCtrl->getLinkTarget($this->parent_obj, 'editWysiwygField'));
				break;

			case UDF_TYPE_SELECT:
				$this->tpl->setVariable("HREF_CMD",
					$ilCtrl->getLinkTarget($this->parent_obj, 'editSelectField'));
				break;
		}
		$this->tpl->setVariable("TXT_CMD", $lng->txt("edit"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("action");
		$this->tpl->setVariable("HREF_CMD",
			$ilCtrl->getLinkTarget($this->parent_obj, 'askDeleteField'));
		$this->tpl->setVariable("TXT_CMD", $lng->txt("delete"));
		$this->tpl->parseCurrentBlock();
		
		// field name
		$this->tpl->setVariable("TXT_FIELD", $a_set["field_name"]);
	}

}
?>
