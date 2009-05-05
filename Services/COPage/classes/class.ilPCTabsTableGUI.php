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

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for tabs
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTabsTableGUI extends ilTable2GUI
{

	function ilPCTabsTableGUI($a_parent_obj, $a_parent_cmd,
		$a_tabs)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn("", "", "1");
		$this->addColumn($lng->txt("cont_position"), "", "1");
		$this->addColumn($lng->txt("cont_caption"), "", "100%");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.tabs_row.html",
			"Services/COPage");
			
		$this->tabs = $a_tabs;
		$caps = $this->tabs->getCaptions();
		$this->setData($this->tabs->getCaptions());
		$this->setLimit(0);
		
		$this->addMultiCommand("confirmTabsDeletion", $lng->txt("delete"));
		$this->addCommandButton("saveTabs", $lng->txt("save"));
		$this->addCommandButton("addTab", $lng->txt("cont_add_tab"));
		
		$this->setTitle($lng->txt("cont_tabs"));
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$this->pos += 10;
		$this->tpl->setVariable("POS", $this->pos);
		$this->tpl->setVariable("TID", $a_set["hier_id"].":".$a_set["pc_id"]);
		$this->tpl->setVariable("VAL_CAPTION", $a_set["caption"]);
	}

}
?>
