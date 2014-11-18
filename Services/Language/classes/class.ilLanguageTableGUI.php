<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for 
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup Services
*/
class ilLanguageTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_folder)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilSetting;
		
		$this->folder = $a_folder;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
//		$this->setTitle($lng->txt(""));
		$this->setLimit(9999);
		
		$this->addColumn("", "", "1", 1);
		$this->addColumn($this->lng->txt("language"));
		$this->addColumn($this->lng->txt("status"));
		$this->addColumn($this->lng->txt("users"));
		$this->addColumn($this->lng->txt("last_refresh"));
		if ($ilSetting->get("lang_ext_maintenance"))
		{
			$this->addColumn($this->lng->txt("last_change"));
		}
		$this->setSelectAllCheckbox("id[]");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		if ($ilSetting->get("lang_ext_maintenance"))
		{
			$this->setRowTemplate("tpl.lang_list_row_extended.html", "Services/Language");
		}
		else
		{
			$this->setRowTemplate("tpl.lang_list_row.html", "Services/Language");
		}
		$this->disable("footer");
		$this->setEnableTitle(true);

        if ($ilSetting->get("lang_ext_maintenance") == "1")
        {
            $this->addMultiCommand("confirmRefreshSelected", $lng->txt("refresh"));
        }
        else
        {
            $this->addMultiCommand("RefreshSelected", $lng->txt("refresh"));
        }
		$this->addMultiCommand("install", $lng->txt("install"));
		$this->addMultiCommand("installLocal", $lng->txt("install_local"));
		$this->addMultiCommand("uninstall", $lng->txt("uninstall"));
		$this->addMultiCommand("setSystemLanguage", $lng->txt("setSystemLanguage"));
		$this->addMultiCommand("setUserLanguage", $lng->txt("setUserLanguage"));
		$this->getItems();
	}
	
	/**
	* Get language data
	*/
	function getItems()
	{
		$languages = $this->folder->getLanguages();
		$data = array();
		foreach ($languages as $k => $l)
		{
			$data[] = array_merge($l, array("key" => $k));
		}

        // sort alphabetically but shoe installed languages first
        $data = ilUtil::stableSortArray($data, 'name', 'asc', false);
        $data = ilUtil::stableSortArray($data, 'desc', 'asc', false);

		$this->setData($data);
	}
	
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng, $rbacsystem, $ilCtrl, $ilSetting;

		// set status info (in use or systemlanguage)
		if ($a_set["status"])
		{
			$status = "<span class=\"small\"> (".$lng->txt($a_set["status"]).")</span>";
		}

		// set remark color
		switch ($a_set["info"])
		{
			case "file_not_found":
				$remark = "<span class=\"smallred\"> ".$lng->txt($a_set["info"])."</span>";
				break;
			case "new_language":
				//$remark = "<span class=\"smallgreen\"> ".$lng->txt($a_set["info"])."</span>";
				break;
			default:
				$remark = "";
				break;
		}
		
		// show page translation
		if($ilSetting->get("lang_ext_maintenance")
		and $ilSetting->get("lang_translate_". $a_set['key'], false))
		{
			$remark .= $remark ? '<br />' : '';
			$remark .= "<span class=\"smallgreen\"> ".$lng->txt('language_translation_enabled')."</span>";
		}

		// make language name clickable
		if ($rbacsystem->checkAccess("write", $this->folder->getRefId()))
		{
			if ($ilSetting->get("lang_ext_maintenance") == "1")
			{
				if (substr($lang_data["description"],0,9) == "installed")
				{
					$ilCtrl->setParameterByClass("ilobjlanguageextgui","obj_id",$a_set["obj_id"]);
					$url = $ilCtrl->getLinkTargetByClass("ilobjlanguageextgui","");
					$a_set["name"] = '<a href="'.$url.'">'.$a_set["name"].'</a>';
				}
			}
		}

		if ($a_set["desc"] != "not_installed")
		{
			$this->tpl->setVariable("LAST_REFRESH",
				ilDatePresentation::formatDate(new ilDateTime($a_set["last_update"],IL_CAL_DATETIME)));

			if ($ilSetting->get("lang_ext_maintenance"))
			{
				$last_change = ilObjLanguage::_getLastLocalChange($a_set['key']);
				$this->tpl->setVariable("LAST_CHANGE",
					ilDatePresentation::formatDate(new ilDateTime($last_change,IL_CAL_DATETIME)));
			}
		}

		$this->tpl->setVariable("NR_OF_USERS", ilObjLanguage::countUsers($a_set["key"]));

		// make language name clickable
		if ($rbacsystem->checkAccess("write",$this->folder->getRefId()))
		{
			if ($ilSetting->get("lang_ext_maintenance") == "1")
			{
				if (substr($a_set["description"],0,9) == "installed")
				{
					$ilCtrl->setParameterByClass("ilobjlanguageextgui", "obj_id", $a_set["obj_id"]);
					$url = $ilCtrl->getLinkTargetByClass("ilobjlanguageextgui", "");
					$a_set["name"] = '<a href="'.$url.'">'.$a_set["name"].'</a>';
				}
			}
		}

		$this->tpl->setVariable("VAL_LANGUAGE", $a_set["name"].$status);
		$this->tpl->setVariable("VAL_STATUS", $lng->txt($a_set["desc"])."<br/>".$remark);
		$this->tpl->setVariable("OBJ_ID", $a_set["obj_id"]);
	}

}
?>
