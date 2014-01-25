<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 *
 * @ingroup Services
 */
class ilObjClipboardTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($lng->txt("clipboard"));
		
		$this->addColumn("", "", "1");
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("action"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.obj_cliboard_row.html", "Services/Object");
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;
//var_dump($a_set);
		$this->tpl->setVariable("ICON", ilUtil::img(ilObject::_getIcon($a_set["obj_id"], "tiny"),
			$a_set["type_txt"]));
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("CMD", $a_set["cmd"]);
	}

}
?>