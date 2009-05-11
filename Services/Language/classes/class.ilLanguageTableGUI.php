<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
		$this->addColumn($this->lng->txt("last_change"));
		$this->addColumn($this->lng->txt("usr_agreement"));
		$this->setDefaultOrderField("name");
		$this->setSelectAllCheckbox("id[]");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.lang_list_row.html", "Services/Language");
		$this->disable("footer");
		$this->setEnableTitle(true);

		$this->addMultiCommand("install", $lng->txt("install"));
		$this->addMultiCommand("installLocal", $lng->txt("install_local"));
		$this->addMultiCommand("uninstall", $lng->txt("uninstall"));
		if ($ilSetting->get("lang_ext_maintenance") == "1")
		{
			$this->addMultiCommand("confirmRefreshSelected", $lng->txt("refresh"));
		}
		else
		{
			$this->addMultiCommand("RefreshSelected", $lng->txt("refresh"));
		}
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
				$remark = "<span class=\"smallgreen\"> ".$lng->txt($a_set["info"])."</span>";
				break;
			default:
				$remark = "";
				break;
		}

		if (file_exists("./Customizing/clients/".CLIENT_ID."/agreement/".
			"agreement_".$a_set["key"].".html"))
		{
			$agreement_exists_str = $lng->txt("available")." (".$lng->txt("client").")";
		}
		else if (file_exists("./Customizing/global/agreement/".
			"agreement_".$a_set["key"].".html"))
		{
			$agreement_exists_str = $lng->txt("available");
		}
		else
		{
			if ($a_set["status"] == "system_language")
			{
				$agreement_exists_str = "<b>".$lng->txt("missing")."</b>";
			}
			else
			{
				$agreement_exists_str = $lng->txt("missing");
			}
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

		$this->tpl->setVariable("VAL_LAST_CHANGE",
			ilDatePresentation::formatDate(new ilDateTime($a_set["last_update"],IL_CAL_DATETIME)));
			
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
		$this->tpl->setVariable("VAL_USER_AGREEMENT", $agreement_exists_str);
		$this->tpl->setVariable("OBJ_ID", $a_set["obj_id"]);
	}

}
?>
