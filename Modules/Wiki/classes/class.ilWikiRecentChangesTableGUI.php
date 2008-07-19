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
* TableGUI class for recent changes in wiki
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesWiki
*/
class ilWikiRecentChangesTableGUI extends ilTable2GUI
{

	function ilWikiRecentChangesTableGUI($a_parent_obj, $a_parent_cmd = "",
		$a_wiki_id)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->wiki_id = $a_wiki_id;
		
		$this->addColumn($lng->txt("wiki_last_changed"), "", "33%");
		$this->addColumn($lng->txt("wiki_page"), "", "33%");
		$this->addColumn($lng->txt("wiki_last_changed_by"), "", "67%");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_row_recent_changes.html",
			"Modules/Wiki");
		$this->getRecentChanges();
		
		$this->setTitle($lng->txt("wiki_recent_changes"));
	}
	
	/**
	* Get pages for list.
	*/
	function getRecentChanges()
	{
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		$changes = ilWikiPage::getRecentChanges("wpg", $this->wiki_id);
		$this->setDefaultOrderField("date");
		$this->setDefaultOrderDirection("desc");
		$this->setData($changes);
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		$title = ilWikiPage::lookupTitle($a_set["id"]);
		$this->tpl->setVariable("TXT_PAGE_TITLE", $title);
		$this->tpl->setVariable("DATE",
			ilDatePresentation::formatDate(new ilDateTime($a_set["date"], IL_CAL_DATETIME)));
		$ilCtrl->setParameterByClass("ilwikipagegui", "page", rawurlencode($title));
		$ilCtrl->setParameterByClass("ilwikipagegui", "old_nr", $a_set["nr"]);
		$this->tpl->setVariable("HREF_PAGE",
			$ilCtrl->getLinkTargetByClass("ilwikipagegui", "preview"));

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

	}

}
?>
