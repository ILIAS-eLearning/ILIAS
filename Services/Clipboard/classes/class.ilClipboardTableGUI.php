<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI clipboard items
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesClipboard
*/
class ilClipboardTableGUI extends ilTable2GUI
{

	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$lng->loadLanguageModule("mep");

		$this->addColumn("", "", "1");	// checkbox
		$this->addColumn($lng->txt("mep_thumbnail"), "", "1");
		$this->addColumn($lng->txt("mep_title_and_description"), "", "100%");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.clipboard_tbl_row.html",
			"Services/Clipboard");
		$this->getItems();
		
		// title
		$this->setTitle($lng->txt("clipboard"));

		$this->setDefaultOrderField("title");
		
		// action commands
		if ($this->parent_obj->mode == "getObject")
		{
			$this->addMultiCommand("insert", $this->parent_obj->getInsertButtonTitle());
		}
		$this->addMultiCommand("remove", $lng->txt("remove"));
		
		$this->setSelectAllCheckbox("id");
	}

	/**
	* Get items from user clipboard
	*/
	function getItems()
	{
		global $ilUser;
		
		$objs = $ilUser->getClipboardObjects("mob");
		$objs2 = $ilUser->getClipboardObjects("incl");
		$objs = array_merge($objs, $objs2);
		$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);

		$this->setData($objs);
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilAccess;

		if ($a_set["type"] == "mob")
		{
			// output thumbnail
			$mob = new ilObjMediaObject($a_set["id"]);
			$med = $mob->getMediaItem("Standard");
			$target = $med->getThumbnailTarget();
			if ($target != "")
			{
				$this->tpl->setCurrentBlock("thumbnail");
				$this->tpl->setVariable("IMG_THUMB", $target);
				$this->tpl->parseCurrentBlock();
			}
		}
		else if ($a_set["type"] == "incl")
		{
			$this->tpl->setCurrentBlock("thumbnail");
			$this->tpl->setVariable("IMG_THUMB",
				ilUtil::getImagePath("icon_pg.svg"));
			$this->tpl->parseCurrentBlock();
		}

		// allow editing of media objects
		if ($this->parent_obj->mode != "getObject" && $a_set["type"] == "mob")
		{					
			// output edit link
			$this->tpl->setCurrentBlock("edit");
			$ilCtrl->setParameter($this->parent_obj, "clip_item_id", $a_set["id"]);
			$ilCtrl->setParameterByClass("ilObjMediaObjectGUI", "clip_item_id", $a_set["id"]);
			$this->tpl->setVariable("EDIT_LINK",
				$ilCtrl->getLinkTargetByClass("ilObjMediaObjectGUI", "edit",
					array("ilEditClipboardGUI")));
			$this->tpl->setVariable("TEXT_OBJECT", $a_set["title"].
				" [".$a_set["id"]."]");
			$this->tpl->parseCurrentBlock();
		}
		else		// just list elements for selection
		{
			$this->tpl->setCurrentBlock("show");
			$this->tpl->setVariable("TEXT_OBJECT2", $a_set["title"].
				" [".$a_set["id"]."]");
			$this->tpl->parseCurrentBlock();
		}
		
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
		if ($a_set["type"] == "mob")
		{
			$this->tpl->setVariable("MEDIA_INFO",
				ilObjMediaObjectGUI::_getMediaInfoHTML($mob));
		}
		$this->tpl->setVariable("CHECKBOX_ID", $a_set["type"].":".$a_set["id"]);
	}

}
?>
