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
		
		$this->addColumn("", "c", "1");
		$this->addColumn("", "d", "1");
		$this->addColumn($lng->txt("date"), "", "33%");
		$this->addColumn($lng->txt("user"), "", "33%");
		$this->addColumn($lng->txt("action"), "", "33%");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.page_history_row.html", "Services/COPage");
		$this->setDefaultOrderField("sortkey");
		$this->setDefaultOrderDirection("desc");
		$this->addMultiCommand("compareVersion", $lng->txt("cont_page_compare"));
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
	}
	
	/**
	* Should this field be sorted numeric?
	*
	* @return	boolean		numeric ordering; default is false
	*/
	function numericOrdering($a_field)
	{
		if ($a_field == "sortkey")
		{
			return true;
		}
		return false;
	}

	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilAccess;

		// rollback command
		if ($a_set["nr"] > 0)
		{
			$ilCtrl->setParameter($this->getParentObject(), "old_nr", $a_set["nr"]);
			$this->tpl->setCurrentBlock("command");
			$this->tpl->setVariable("TXT_COMMAND", $lng->txt("cont_rollback"));
			$this->tpl->setVariable("HREF_COMMAND",
				$ilCtrl->getLinkTarget($this->getParentObject(), "rollbackConfirmation"));
			$this->tpl->parseCurrentBlock();
			$ilCtrl->setParameter($this->getParentObject(), "old_nr", "");
		}
		
		$this->tpl->setVariable("NR", $a_set["nr"]);
		$this->tpl->setVariable("TXT_HDATE",
			ilDatePresentation::formatDate(new ilDateTime($a_set["hdate"], IL_CAL_DATETIME)));

		$ilCtrl->setParameter($this->getParentObject(), "old_nr", $a_set["nr"]);
		$this->tpl->setVariable("HREF_OLD_PAGE",
			$ilCtrl->getLinkTarget($this->getParentObject(), "preview"));
			
		if (ilObject::_exists($a_set["user"]))
		{
			// user name
			$user = ilObjUser::_lookupName($a_set["user"]);
			$login = ilObjUser::_lookupLogin($a_set["user"]);
			$this->tpl->setVariable("TXT_LINKED_USER",
				$user["lastname"].", ".$user["firstname"]." [".$login."]");
				
			// profile link
			$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "user", $a_set["user"]);
			$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "back_url",
				rawurlencode($ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCmd())));
			$this->tpl->setVariable("USER_LINK",
				$ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML"));
			$img = ilObjUser::_getPersonalPicturePath($a_set["user"], "xxsmall", true);
			$this->tpl->setVariable("IMG_USER", $img);
		}
			
		$ilCtrl->setParameter($a_parent_obj, "old_nr", "");
	}

}
?>
