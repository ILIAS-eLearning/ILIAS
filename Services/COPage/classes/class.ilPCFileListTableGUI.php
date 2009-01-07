<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for file list
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCFileListTableGUI extends ilTable2GUI
{

	function ilPCFileListTableGUI($a_parent_obj, $a_parent_cmd,
		$a_file_list)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn("", "", "1");
		$this->addColumn($lng->txt("cont_position"), "", "1");
		if ($this->getParentObject()->checkStyleSelection())
		{
			$this->addColumn($lng->txt("cont_file"), "", "50%");
			$this->addColumn($lng->txt("cont_characteristic"), "", "50%");
		}
		else
		{
			$this->addColumn($lng->txt("cont_file"), "", "100%");
		}
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.file_list_row.html",
			"Services/COPage");
			
		$this->file_list = $a_file_list;
		$this->setData($this->file_list->getFileList());
		$this->setLimit(0);
		
		$this->addMultiCommand("deleteFileItem", $lng->txt("delete"));
		if ($this->getParentObject()->checkStyleSelection())
		{
			$this->addCommandButton("savePositionsAndClasses", $lng->txt("cont_save_positions_and_classes"));
		}
		else
		{
			$this->addCommandButton("savePositions", $lng->txt("cont_save_positions"));
		}
		$this->addCommandButton("addFileItem", $lng->txt("cont_add_file"));
		
		$this->setTitle($lng->txt("cont_files"));
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		if ($this->getParentObject()->checkStyleSelection())
		{
			$this->tpl->setCurrentBlock("class_sel");
			$sel = ($a_set["class"] == "")
				? "FileListItem"
				: $a_set["class"];
			$this->tpl->setVariable("CLASS_SEL", ilUtil::formSelect($sel,
				"class[".$a_set["hier_id"].":".$a_set["pc_id"]."]",
				$this->getParentObject()->getCharacteristics(), false, true));
			$this->tpl->parseCurrentBlock();
		}

		$this->pos += 10;
		$this->tpl->setVariable("POS", $this->pos);
		$this->tpl->setVariable("FID", $a_set["hier_id"].":".$a_set["pc_id"]);
		$this->tpl->setVariable("TXT_FILE", ilObject::_lookupTitle($a_set["id"]));
	}

}
?>
