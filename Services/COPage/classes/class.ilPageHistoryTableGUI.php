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
* Page History Table GUI Class
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesNews
*/
class ilPageHistoryTableGUI extends ilTable2GUI
{
	function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($lng->txt("content_page_history"));
		
		$this->addColumn("", "a", "1");
		$this->addColumn("", "b", "1");
		$this->addColumn("", "c", "1");
		$this->addColumn("", "d", "1");
		$this->addColumn($lng->txt("date"), "", "33%");
		$this->addColumn($lng->txt("user"), "", "67%");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.page_history_row.html",
			"Services/COPage");
		$this->setDefaultOrderField("hdate");
		$this->setDefaultOrderDirection("desc");
		$this->addMultiCommand("compareVersion", $lng->txt("cont_page_compare"));
		//$this->addCommandButton("compareVersion", $lng->txt("cont_page_compare"));
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilAccess;
		$this->tpl->setVariable("TXT_CUR", $lng->txt("content_current"));
		$this->tpl->setVariable("TXT_LAST", $lng->txt("content_last"));
		$this->tpl->setVariable("NR", $a_set["nr"]);
		$this->tpl->setVariable("TXT_HDATE", $a_set["hdate"]);
		
		$ilCtrl->setParameter($this->getParentObject(), "old_nr", $a_set["nr"]);
		$this->tpl->setVariable("HREF_OLD_PAGE",
			$ilCtrl->getLinkTarget($this->getParentObject(), "preview")); 
		$user = ilObjUser::_lookupName($a_set["user"]);
		$login = ilObjUser::_lookupLogin($a_set["user"]);
		$this->tpl->setVariable("TXT_USER",
			$user["lastname"].", ".$user["firstname"]." [".$login."]");
		$ilCtrl->setParameter($a_parent_obj, "old_nr", "");
	}

}
?>
