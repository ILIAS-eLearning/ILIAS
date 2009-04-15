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

/**
* Table GUI for Bookmark management
*
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
*
*/

include_once 'Services/Table/classes/class.ilTable2GUI.php';

class ilBookmarkAdministrationTableGUI extends ilTable2GUI
{
	/**
	* @param object	the object to which this table refers
	*/
	
	
	
	public function __construct($a_ref)
	{
		global $lng, $ilCtrl;
		parent::__construct($a_ref);
		
		$this->setTitle($lng->txt('bookmarks'), "icon_bm.gif");
		
		$this->setRowTemplate('tpl.bookmark_administration_row.html', 'Services/PersonalDesktop');
		$this->addColumn('', 'id', '3%');
		$this->addColumn('', 'image', '3%');
		$this->addColumn($lng->txt('title'), 'title', '84%');
		$this->addColumn('', 'edit', '10%');
		
		$this->setFormAction($ilCtrl->getFormAction($a_ref));
		$this->setSelectAllCheckbox('bm_id');
		
		$this->addCommandButton('newFormBookmark', $lng->txt("bookmark_new"));
		$this->addCommandButton('newFormBookmarkFolder', $lng->txt("bookmark_folder_new"));
		
		$this->addMultiCommand('delete', $lng->txt('delete'));
		$this->addMultiCommand('export', $lng->txt('export'));
		$this->addMultiCommand('sendmail', $lng->txt('bkm_sendmail'));
		
		$ilCtrl->saveParameter($this->getParentObject(), 'bmf_id');
	}

	public function fillRow($a_data)
	{
		global $lng, $ilCtrl;
		
		$this->tpl->setVariable("VAL_ID", $a_data["obj_id"]);
		
		// edit link
		if ($a_data["type"] != "parent")
		{
			$ilCtrl->setParameter($this->parent_obj, "bmf_id", $this->parent_obj->id);
			$ilCtrl->setParameter($this->parent_obj, "obj_id", $a_data["obj_id"]);
			$link = ($a_data["type"] == "bmf")
				? $ilCtrl->getLinkTarget($this->parent_obj, "editFormBookmarkFolder")
				: $ilCtrl->getLinkTarget($this->parent_obj, "editFormBookmark");
			$this->tpl->setVariable("EDIT_TXT", $this->lng->txt("edit"));
			$this->tpl->setVariable("EDIT_HREF", $link);
		}
		
		// icon
		$img_type = ($a_data["type"] == "bmf"  || $a_data["type"] == "parent") ? "cat" : $a_data["type"];
		$val = ilUtil::getImagePath("icon_".$img_type.".gif");
		$this->tpl->setVariable("VAL_ICON", $val);
		$this->tpl->setVariable("VAL_ICON_ALT", $lng->txt($object["type"]));
		
		// folder links
		if ($a_data["type"] == "bmf" || $a_data["type"] == "parent")
		{
			$this->tpl->setVariable("VAL_BMF_TITLE", $a_data["title"]);
			$ilCtrl->setParameter($this->parent_obj, "bmf_id", $a_data["obj_id"]);
			$this->tpl->setVariable("VAL_BMF_TARGET", $ilCtrl->getLinkTarget($this->parent_obj));
			$this->tpl->setVariable("FRAME_TARGET", ilFrameTargetInfo::_getFrame("MainContent"));
		}
		else
		{
			$this->tpl->setVariable("VAL_BM_TITLE", $a_data["title"]);
			$this->tpl->setVariable("VAL_BM_TARGET", $a_data["target"]);
			$this->tpl->setVariable("VAL_BM_DESCRIPTION", $a_data["description"]);
			$this->tpl->setVariable("FRAME_TARGET", ilFrameTargetInfo::_getFrame("ExternalContent"));
		}
		$ilCtrl->clearParameters($this->parent_obj);
	}
}
