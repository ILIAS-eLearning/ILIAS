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
	const IL_MEP_SELECT = "select";
	const IL_MEP_EDIT = "edit";
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd,
		$a_media_pool, $a_folder_par = "obj_id",
		$a_mode = ilMediaPoolTableGUI::IL_MEP_EDIT, $a_all_objects = false)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setMode($a_mode);
		$this->setId("mep_table");
		$this->all_objects = $a_all_objects;
		$lng->loadLanguageModule("mep");
		
		$this->media_pool = $a_media_pool;
		$this->tree = ilObjMediaPool::getPoolTree($this->media_pool->getId());
		$this->folder_par = $a_folder_par;
		
		if ($this->all_objects)
		{
			$this->setExternalSorting(true);
			$this->initFilter();
		}
		// folder determination
		if ($_GET[$this->folder_par] > 0)
		{
			$this->current_folder = $_GET[$this->folder_par];
		}
		else if ($_SESSION["mep_pool_folder"] > 0  && $this->tree->isInTree($_SESSION["mep_pool_folder"]))
		{
			$this->current_folder = $_SESSION["mep_pool_folder"];
		}
		else
		{
			$this->current_folder = $this->tree->getRootId();
		}
		$_SESSION["mep_pool_folder"] = $this->current_folder;

		$this->addColumn("", "", "1");	// checkbox
		$this->addColumn($lng->txt("mep_thumbnail"), "", "1");
		$this->addColumn($lng->txt("mep_title_and_description"), "", "100%");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.mep_list_row.html", "Modules/MediaPool");
		$this->getItems();

		// title
		if ($this->current_folder != $this->tree->getRootId() && !$this->all_objects)
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
		if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()) &&
			$this->getMode() == ilMediaPoolTableGUI::IL_MEP_EDIT)
		{
			$this->addMultiCommand("copyToClipboard", $lng->txt("cont_copy_to_clipboard"));
			$this->addMultiCommand("confirmRemove", $lng->txt("remove"));
			
			if (!$this->all_objects)
			{
				$this->addCommandButton("createFolderForm", $lng->txt("mep_create_folder"));
				$this->addCommandButton("createMediaObject", $lng->txt("mep_create_mob"));
			}
		}
		
		if ($this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT)
		{
			// maybe this is a little bit to strong coupled with ilpcmediaobjectgui
			$this->addMultiCommand("create_mob", $lng->txt("insert"));
			$this->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
		
		if ($this->getMode() == ilMediaPoolTableGUI::IL_MEP_EDIT)
		{
			$this->setSelectAllCheckbox("id");
		}
		
		if ($this->current_folder != $this->tree->getRootId() && !$this->all_objects)
		{
			$ilCtrl->setParameter($this->parent_obj, $this->folder_par,
				$this->tree->getParentId($this->current_folder));
			$this->addHeaderCommand($ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd),
				$lng->txt("mep_parent_folder"));
			$ilCtrl->setParameter($this->parent_obj, $this->folder_par,
				$this->current_folder);
		}

	}

	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng, $rbacreview, $ilUser;
		
		// title/description
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["title"] = $ti->getValue();
		
		// format
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$options = array(
			"" => $lng->txt("mep_all"),
			);
		$formats = $this->media_pool->getUsedFormats();
		$options = array_merge($options, $formats);
		$si = new ilSelectInputGUI($this->lng->txt("mep_format"), "format");
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter["format"] = $si->getValue();
		
	}

	/**
	* Set Mode.
	*
	* @param	string	$a_mode	Mode
	*/
	function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	/**
	* Get Mode.
	*
	* @return	string	Mode
	*/
	function getMode()
	{
		return $this->mode;
	}

	/**
	* Get items of current folder
	*/
	function getItems()
	{
		if (!$this->all_objects)
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
		}
		else
		{
			$objs = $this->media_pool->getMediaObjects($this->filter["title"],
				$this->filter["format"]);
		}

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
		
		switch($a_set["type"])
		{
			case "fold":
				$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
				$ilCtrl->setParameter($this->parent_obj, $this->folder_par, $a_set["obj_id"]);
				$this->tpl->setVariable("LINK_VIEW",
					$ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
				$this->tpl->parseCurrentBlock();
				
				if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()) &&
					$this->getMode() == ilMediaPoolTableGUI::IL_MEP_EDIT)
				{
					$this->tpl->setCurrentBlock("edit");
					$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
					$ilCtrl->setParameterByClass("ilobjfoldergui", $this->folder_par, $a_set["obj_id"]);
					$this->tpl->setVariable("EDIT_LINK",
						$ilCtrl->getLinkTargetByClass("ilobjfoldergui", "edit"));
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("IMG", ilUtil::img(ilUtil::getImagePath("icon_".$a_set["type"].".gif")));
				$ilCtrl->setParameter($this->parent_obj, $this->folder_par, $this->current_folder);
				break;

			case "mob":
				if ($this->getMode() == ilMediaPoolTableGUI::IL_MEP_SELECT)
				{
					$this->tpl->setVariable("TXT_NO_LINK_TITLE", $a_set["title"]);
				}
				else
				{
					$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
					$this->tpl->touchBlock("nf");
					$ilCtrl->setParameterByClass("ilobjmediaobjectgui", "obj_id", $a_set["obj_id"]);
					$ilCtrl->setParameter($this->parent_obj, "mob_id", $a_set["obj_id"]);
					$this->tpl->setVariable("LINK_VIEW",
						$ilCtrl->getLinkTarget($this->parent_obj, "showMedia"));
				}
					
				// edit link
				if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()) &&
					$this->getMode() == ilMediaPoolTableGUI::IL_MEP_EDIT)
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
					$this->tpl->setVariable("IMG", ilUtil::img($target));
				}
				else
				{
					$this->tpl->setVariable("IMG",
						ilUtil::img(ilUtil::getImagePath("icon_".$a_set["type"].".gif")));
				}
				if (ilUtil::deducibleSize($med->getFormat()) && 
					$med->getLocationType() == "Reference")
				{
					$size = @getimagesize($med->getLocation());
					if ($size[0] > 0 && $size[1] > 0)
					{
						$wr = $size[0] / 80;
						$hr = $size[1] / 80;
						$r = max($wr, hr);
						$w = (int) ($size[0]/$r);
						$h = (int) ($size[1]/$r);
						$this->tpl->setVariable("IMG",
							ilUtil::img($med->getLocation(), "", $w, $h));
					}
				}
				
				// output media info
				include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
				$this->tpl->setVariable("MEDIA_INFO",
					ilObjMediaObjectGUI::_getMediaInfoHTML($mob));
				$ilCtrl->setParameter($this->parent_obj, $this->folder_par, $this->current_folder);
				break;
		}

		if ($ilAccess->checkAccess("write", "", $this->media_pool->getRefId()) &&
			($this->getMode() == ilMediaPoolTableGUI::IL_MEP_EDIT || $a_set["type"] == "mob"))
		{
			$this->tpl->setCurrentBlock("chbox");
			$this->tpl->setVariable("CHECKBOX_ID", $a_set["obj_id"]);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("tbl_content");
		}
	}

}
?>
