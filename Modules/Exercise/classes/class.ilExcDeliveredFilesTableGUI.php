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
	function __construct($a_parent_obj, $a_parent_cmd, $a_exc)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->exercise = $a_exc;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($this->getDeliveredFiles());
		$this->setTitle($this->lng->txt("already_delivered_files"));
		$this->setLimit(9999);
		
		$this->addColumn($this->lng->txt(""), "", "1", 1);
		$this->addColumn($this->lng->txt("filename"));
		$this->addColumn($this->lng->txt("date"));
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.delivered_file_row.html", "Modules/Exercise");
		$this->disable("footer");
		$this->setEnableTitle(true);

		if (mktime() < $this->exercise->getTimestamp())
		{
			$this->addMultiCommand("deleteDelivered", $lng->txt("delete"));
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
		
		$files = $this->exercise->getDeliveredFiles($ilUser->getId());
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
	}

}
	
?>