<?php 
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Export table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilExportTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_exp_obj)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->obj = $a_exp_obj;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($this->getExportFiles());
		$this->setTitle($lng->txt("exp_export_files"));
		
		$this->addColumn($this->lng->txt(""), "", "1", true);
		$this->addColumn($this->lng->txt("type"), "type");
		$this->addColumn($this->lng->txt("file"), "file");
		$this->addColumn($this->lng->txt("size"), "size");
		$this->addColumn($this->lng->txt("date"), "timestamp");
		
		$this->setDefaultOrderField("timestamp");
		$this->setDefaultOrderDirection("asc");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.export_table_row.html", "Services/Export");
		//$this->disable("footer");
		//$this->setEnableTitle(true);

		$this->addMultiCommand("delete", $lng->txt("delete"));
		$this->addMultiCommand("download", $lng->txt("download"));
	}

	/**
	 * Get export files
	 *
	 * @param
	 * @return
	 */
	function getExportFiles()
	{
	
		$types = array();
		foreach ($this->parent_obj->getFormats() as $f)
		{
			$types[] = $f["key"];
		}
		include_once("./Services/Export/classes/class.ilExport.php");
		$files = ilExport::_getExportFiles($this->obj->getId(),
			$types, $this->obj->getType());
		return $files;
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("VAL_TYPE", $a_set["type"]);
		$this->tpl->setVariable("VAL_FILE", $a_set["file"]);
		$this->tpl->setVariable("VAL_SIZE", $a_set["size"]);
		$this->tpl->setVariable("VAL_DATE", 
			ilDatePresentation::formatDate(new ilDateTime($a_set["timestamp"],IL_CAL_UNIX)));
	}

}

?>