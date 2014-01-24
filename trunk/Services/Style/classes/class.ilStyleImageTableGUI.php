<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for style editor (image list)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesStyle
*/
class ilStyleImageTableGUI extends ilTable2GUI
{
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_style_obj)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $rbacsystem;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setTitle($lng->txt("sty_images"));
		$this->style_obj = $a_style_obj;
		
		$this->addColumn("", "", "1");	// checkbox
		$this->addColumn($this->lng->txt("thumbnail"), "", "1");
		$this->addColumn($this->lng->txt("file"), "", "33%");
		$this->addColumn($this->lng->txt("sty_width_height"), "", "33%");
		$this->addColumn($this->lng->txt("size"), "", "33%");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.style_image_row.html", "Services/Style");
		$this->setSelectAllCheckbox("file");
		$this->getItems();

		// action commands
		if ($rbacsystem->checkAccess("write", (int) $_GET["ref_id"]))
		{
			$this->addMultiCommand("deleteImage", $lng->txt("delete"));
		}
		
		//$this->addMultiCommand("editLink", $lng->txt("cont_set_link"));
		//$this->addCommandButton("addImage", $this->lng->txt("sty_add_image"));
		
		$this->setEnableTitle(true);
	}

	/**
	* Get items of current folder
	*/
	function getItems()
	{
		$this->setData($this->style_obj->getImages());
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilAccess;

		$thumbfile = $this->style_obj->getThumbnailsDirectory()."/".$a_set["entry"];
		if (is_file($thumbfile))
		{
			$this->tpl->setCurrentBlock("thumbnail");
			$this->tpl->setVariable("IMG_ALT", $a_set["entry"]);
			$this->tpl->setVariable("IMG_SRC", $thumbfile);
			$this->tpl->parseCurrentBlock();
		}
		$image_file = $this->style_obj->getImagesDirectory()."/".$a_set["entry"];
		$image_size = @getimagesize($image_file);
		{
			if ($image_size[0] > 0 && $image_size[1] > 0)
			{
				$this->tpl->setVariable("VAL_WIDTH_HEIGHT",
					$image_size[0]."px x ".$image_size[1]."px");
			}
		}
		
		$this->tpl->setVariable("VAL_FILENAME", $a_set["entry"]);
		$this->tpl->setVariable("VAL_SIZE", $a_set["size"]);
		$this->tpl->setVariable("FILE", $a_set["entry"]);
	}
}
?>
