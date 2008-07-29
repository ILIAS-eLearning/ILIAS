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
* TableGUI class for recent changes in wiki
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesMediaPool
*/
class ilMediaPoolTableGUI extends ilTable2GUI
{

	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd,
		$a_media_pool, $a_folder_par = "obj_id")
	{
		global $ilCtrl, $lng, $ilAccess;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->media_pool = $a_media_pool;
		$this->tree = ilObjMediaPool::getPoolTree($this->media_pool->getId());
		$this->folder_par = $a_folder_par;
		$this->current_folder = ($_GET[$this->folder_par] > 0)
			? $_GET[$this->folder_par]
			: $this->tree->getRootId();

		$this->addColumn("", "", "1");	// checkbox
		$this->addColumn($lng->txt("mep_thumbnail"), "", "1");
		$this->addColumn($lng->txt("mep_title_and_description"), "", "100%");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.mep_list_row.html",
			"Modules/MediaPool");
		$this->getItems();
		
		// title
		if ($this->current_folder != $this->tree->getRootId())
		{
			$node = $this->tree->getNodeData($this->current_folder);
			$this->setTitle($node["title"], "icon_fold.gif",
				$node["title"]);
		}
		else
		{
			$this->setTitle(ilObject::_lookupTitle($this->media_pool->getId()),
				"icon_mep.gif",
				ilObject::_lookupTitle($this->media_pool->getId()));
		}
		
		// action commands
		if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()))
		{
			$this->addMultiCommand("confirmRemove", $lng->txt("remove"));
			$this->addMultiCommand("copyToClipboard", $lng->txt("cont_copy_to_clipboard"));
			
			$this->addCommandButton("createFolderForm", $lng->txt("mep_create_folder"));
			$this->addCommandButton("createMediaObject", $lng->txt("mep_create_mob"));
		}
	}

	/**
	* Get items of current folder
	*/
	function getItems()
	{
		$fobjs = $this->media_pool->getChilds($this->current_folder, "fold");
		$f2objs = array();
		foreach ($fobjs as $obj)
		{
			$f2objs[$obj["title"].":".$obj["id"]] = $obj;
		}
		ksort($f2objs);
		
		// get current media objects
		$mobjs = $this->media_pool->getChilds($this->current_folder, "mob");
		$m2objs = array();
		foreach ($mobjs as $obj)
		{
			$m2objs[$obj["title"].":".$obj["id"]] = $obj;
		}
		ksort($m2objs);
		
		// merge everything together
		$objs = array_merge($f2objs, $m2objs);

		$this->setData($objs);
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilAccess;

		$this->tpl->setCurrentBlock("link");
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		switch($a_set["type"])
		{
			case "fold":
				$ilCtrl->setParameter($this->parent_obj, "obj_id", $a_set["obj_id"]);
				$this->tpl->setVariable("LINK_VIEW",
					$ilCtrl->getLinkTarget($this->parent_obj, "listMedia"));
				$this->tpl->parseCurrentBlock();
				
				if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()))
				{
					$this->tpl->setCurrentBlock("edit");
					$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
					$ilCtrl->setParameterByClass("ilobjfoldergui", "obj_id", $a_set["obj_id"]);
					$this->tpl->setVariable("EDIT_LINK",
						$ilCtrl->getLinkTargetByClass("ilobjfoldergui", "edit"));
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_".$a_set["type"].".gif"));
				$ilCtrl->setParameter($this->parent_obj, "obj_id", $this->current_folder);
				break;

			case "mob":
				$this->tpl->touchBlock("nf");
				$ilCtrl->setParameterByClass("ilobjmediaobjectgui", "obj_id", $a_set["obj_id"]);
				$ilCtrl->setParameter($this->parent_obj, "mob_id", $a_set["obj_id"]);
				$this->tpl->setVariable("LINK_VIEW",
					$ilCtrl->getLinkTarget($this->parent_obj, "showMedia"));
					
				// edit link
				if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()))
				{
					$this->tpl->setCurrentBlock("edit");
					$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
					$this->tpl->setVariable("EDIT_LINK",
						$ilCtrl->getLinkTargetByClass("ilobjmediaobjectgui", "edit"));
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setCurrentBlock("link");
				$this->tpl->setCurrentBlock("tbl_content");
				
				// output thumbnail (or mob icon)
				$mob = new ilObjMediaObject($a_set["obj_id"]);
				$med = $mob->getMediaItem("Standard");
				$target = $med->getThumbnailTarget();
				if ($target != "")
				{
					$this->tpl->setVariable("IMG_OBJ", $target);
				}
				else
				{
					$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_".$a_set["type"].".gif"));
				}
				
				// output media info
				include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
				$this->tpl->setVariable("MEDIA_INFO",
					ilObjMediaObjectGUI::_getMediaInfoHTML($mob));
				$ilCtrl->setParameter($this->parent_obj, "obj_id", $this->current_folder);
				break;
		}

		$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
		
		if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()))
		{
			$this->tpl->setCurrentBlock("chbox");
			$this->tpl->setVariable("CHECKBOX_ID", $a_set["obj_id"]);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("tbl_content");
		}
		$this->tpl->setVariable("CSSROW", $css_row);
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("mob_row");
		$this->tpl->parseCurrentBlock();
	}

}
?>
