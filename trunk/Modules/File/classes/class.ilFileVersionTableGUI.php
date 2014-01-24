<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/** @defgroup ModulesFile Modules/File
 */

/**
* Class ilFileVersionTableGUI
*
* @author Stefan Born <stefan.born@phzh.ch> 
* @version $Id$
*
* @ingroup ModulesFile
*/
class ilFileVersionTableGUI extends ilTable2GUI
{
	var $confirmDelete = false;
	var $current_version = 0;
	
	/**
	 * Creates a new ilFileVersionTableGUI instance.
	 * @param ilObjFileGUI $a_parent_obj The parent object.
	 * @param string $a_parent_cmd The parent command.
	 * @param int $a_file_id The id of the file object
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $confirmDelete = false)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->current_version = $a_parent_obj->object->getVersion();
		$this->confirmDelete = $confirmDelete;
		
		$lng->loadLanguageModule("file");
		
		// general properties
		$this->setRowTemplate("tpl.file_version_row.html", "Modules/File");
		$this->setLimit(9999);
		$this->setPrefix("versions");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setEnableHeader(true);
		$this->disable("footer");
		
		// properties depending on confirmation
		if (!$this->confirmDelete)
		{
			$this->setTitle($lng->txt("versions"));
			$this->setSelectAllCheckbox("hist_id[]");
		}
		
		// columns
		if (!$this->confirmDelete)
			$this->addColumn("", "", "1", true);
		
		$this->addColumn($lng->txt("version"), "", "1");
		$this->addColumn($lng->txt("date"));
		$this->addColumn($lng->txt("file_uploaded_by"));
		$this->addColumn($lng->txt("filename"));
		$this->addColumn($lng->txt("filesize"), "", "", false, "ilRight");
		
		if (!$this->confirmDelete)
		{
			$this->addColumn($lng->txt("action"));
			$this->addColumn("", "", "1");
		}
		
		// commands depending on confirmation
		if (!$this->confirmDelete)
		{
			$this->addMultiCommand("deleteVersions", $lng->txt("delete"));
			$this->addMultiCommand("rollbackVersion", $lng->txt("file_rollback"));
		}
		else
		{
			$this->addCommandButton("confirmDeleteVersions", $lng->txt("confirm"));
			$this->addCommandButton("cancelDeleteVersions", $lng->txt("cancel"));
		}
	}

	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilAccess;
		
		$hist_id = $a_set["hist_entry_id"];
		
		// split params
		$filename = $a_set["filename"];
		$version = $a_set["version"];
		$rollback_version = $a_set["rollback_version"];
		$rollback_user_id = $a_set["rollback_user_id"];
		
		// get user name	
		$name = ilObjUser::_lookupName($a_set["user_id"]);
		$username = trim($name["title"] . " " . $name["firstname"] . " " . $name["lastname"]);
		
		// get file size
		$filepath = $this->parent_obj->object->getDirectory($version) . "/" . $filename;
		$filesize = filesize($filepath);
		
		// get action text
		$action = $lng->txt("file_version_" . $a_set["action"]); // create, replace, new_version, rollback
		if ($a_set["action"] == "rollback")
		{
			$name = ilObjUser::_lookupName($rollback_user_id);
			$rollback_username = trim($name["title"] . " " . $name["firstname"] . " " . $name["lastname"]);
			$action = sprintf($action, $rollback_version, $rollback_username);		
		}
		
		// get download link
		$ilCtrl->setParameter($this->parent_obj, "hist_id", $hist_id);
		$link = $ilCtrl->getLinkTarget($this->parent_obj, "sendfile");
		
		// build actions
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$actions = new ilAdvancedSelectionListGUI();
		$actions->setId($hist_id);
		$actions->setListTitle($lng->txt("actions"));
		$actions->addItem($lng->txt("delete"), "", $ilCtrl->getLinkTarget($this->parent_obj, "deleteVersions"));
		if ($this->current_version != $version)
			$actions->addItem($lng->txt("file_rollback"), "", $ilCtrl->getLinkTarget($this->parent_obj, "rollbackVersion"));	
		
		// reset history parameter
		$ilCtrl->setParameter($this->parent_obj, "hist_id", "");
		
		// fill template
		$this->tpl->setVariable("TXT_VERSION", $version);
		$this->tpl->setVariable("TXT_DATE", ilDatePresentation::formatDate(new ilDateTime($a_set['date'],IL_CAL_DATETIME)));
		$this->tpl->setVariable("TXT_UPLOADED_BY", $username);
		$this->tpl->setVariable("DL_LINK", $link);
		$this->tpl->setVariable("TXT_FILENAME", $filename);
		$this->tpl->setVariable("TXT_FILESIZE", ilFormat::formatSize($filesize));
		
		// columns depending on confirmation
		if (!$this->confirmDelete)
		{
			$this->tpl->setCurrentBlock("version_selection");
			$this->tpl->setVariable("OBJ_ID", $hist_id);
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("version_txt_actions");
			$this->tpl->setVariable("TXT_ACTION", $action);
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("version_actions");
			$this->tpl->setVariable("ACTIONS", $actions->getHTML());
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("version_id");
			$this->tpl->setVariable("OBJ_ID", $hist_id);
			$this->tpl->parseCurrentBlock();
		}
	}
}
?>