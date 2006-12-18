<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* TableGUI class for table NewsForContext
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilNewsForContextTableGUI extends ilTable2GUI
{

	function ilNewsForContextTableGUI($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn("", "f", "1");
		$this->addColumn($lng->txt("date"), "creation_date", "1");
		$this->addColumn($lng->txt("text"), "");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_row_news_for_context.html",
			"Services/News");
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		if ($a_set["creation_date"] != $a_set["update_date"])
		{
			$this->tpl->setCurrentBlock("ni_update");
			$this->tpl->setVariable("VAL_LAST_UPDATE", $a_set["update_date"]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("VAL_CREATION_DATE", $a_set["creation_date"]);
		$this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
		$this->tpl->setVariable("VAL_CONTENT", $a_set["content"]);
		$this->tpl->setVariable("VAL_ID", $a_set["id"]);
	}

}
?>
