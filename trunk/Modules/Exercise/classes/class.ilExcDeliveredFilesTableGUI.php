<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Delivered files table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilExcDeliveredFilesTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_exc, $a_ass_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->exercise = $a_exc;
		$this->ass_id = $a_ass_id;		// assignment id
		$this->exc_id = $a_exc->getId();
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$this->ass = new ilExAssignment($this->ass_id);
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($this->getDeliveredFiles());
		$this->setTitle($this->lng->txt("already_delivered_files")." - ".
			$this->ass->getTitle());
		$this->setLimit(9999);
		
		$this->addColumn($this->lng->txt(""), "", "1", 1);
		$this->addColumn($this->lng->txt("filename"), "filetitle");
		
		if($this->ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			// #11957
			$this->lng->loadLanguageModule("file");
			$this->addColumn($this->lng->txt("file_uploaded_by"));			
			include_once "Services/User/classes/class.ilUserUtil.php";
		}
		
		$this->addColumn($this->lng->txt("date"), "timestamp14");
		
		$this->setDefaultOrderField("filetitle");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.delivered_file_row.html", "Modules/Exercise");
		$this->disable("footer");
		$this->setEnableTitle(true);

		if (mktime() < $this->ass->getDeadline() || ($this->ass->getDeadline() == 0))
		{
			$this->addMultiCommand("confirmDeleteDelivered", $lng->txt("delete"));
		}
		$this->addMultiCommand("download", $lng->txt("download"));				
	}

	/**
	 * Get delivered files
	 *
	 * @param
	 * @return
	 */
	function getDeliveredFiles()
	{
		global $ilUser;
		
		$files = ilExAssignment::getDeliveredFiles($this->exc_id, $this->ass_id,
			$ilUser->getId());
		return $files;
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($file)
	{
		global $lng;

		$this->tpl->setVariable("FILE_ID", $file["returned_id"]);
		$this->tpl->setVariable("DELIVERED_FILE", $file["filetitle"]);
				
		$date = new ilDateTime($file['timestamp14'],IL_CAL_TIMESTAMP);
		$this->tpl->setVariable("DELIVERED_DATE", ilDatePresentation::formatDate($date));
		
		if($this->ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$this->tpl->setVariable("DELIVERED_OWNER",
				ilUserUtil::getNamePresentation($file["owner_id"]));
		}
	}

}
	
?>