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

require_once("./Services/COPage/classes/class.ilPCMap.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCMapGUI
*
* User Interface for Map Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCMapGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCMapGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	* Insert new map form.
	*/
	function insert()
	{
		global $tpl;
		
		$this->displayValidationError();
		$this->initForm("create");
		$tpl->setContent($this->form->getHTML());
	}

	/**
	* Edit map form.
	*/
	function edit($a_insert = false)
	{
		global $ilCtrl, $tpl, $lng;
		
		$this->displayValidationError();
		$this->initForm("update");
		$this->getValues();
		$tpl->setContent($this->form->getHTML());

		return $ret;
	}

	/**
	* Get values from object into form
	*/
	function getValues()
	{
		$values = array();
		
		$values["location"]["latitude"] = $this->content_obj->getLatitude();
		$values["location"]["longitude"] = $this->content_obj->getLongitude();
		$values["location"]["zoom"] = $this->content_obj->getZoom();
		$values["width"] = $this->content_obj->getWidth();
		$values["height"] = $this->content_obj->getHeight();
		$values["caption"] = $this->content_obj->handleCaptionFormOutput($this->content_obj->getCaption());
		$values["horizontal_align"] = $this->content_obj->getHorizontalAlign();
		
		$this->form->setValuesByArray($values);
	}
	
	/**
	* Init map creation/update form
	*/
	function initForm($a_mode)
	{
		global $ilCtrl, $lng;
		
		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($ilCtrl->getFormAction($this));
		if ($a_mode == "create")
		{
			$this->form->setTitle($this->lng->txt("cont_insert_map"));
		}
		else
		{
			$this->form->setTitle($this->lng->txt("cont_update_map"));
		}
		
		// location
		$loc_prop = new ilLocationInputGUI($this->lng->txt("cont_location"),
			"location");
		$loc_prop->setRequired(true);
		$this->form->addItem($loc_prop);
		
		// width
		$width_prop = new ilNumberInputGUI($this->lng->txt("cont_width"),
			"width");
		$width_prop->setSize(4);
		$width_prop->setMaxLength(4);
		$width_prop->setRequired(true);
		$width_prop->setMinValue(250);
		$this->form->addItem($width_prop);
		
		// height
		$height_prop = new ilNumberInputGUI($this->lng->txt("cont_height"),
			"height");
		$height_prop->setSize(4);
		$height_prop->setMaxLength(4);
		$height_prop->setRequired(true);
		$height_prop->setMinValue(200);
		$this->form->addItem($height_prop);

		// horizonal align
		$align_prop = new ilSelectInputGUI($this->lng->txt("cont_align"),
			"horizontal_align");
		$options = array(
			"Left" => $lng->txt("cont_left"),
			"Center" => $lng->txt("cont_center"),
			"Right" => $lng->txt("cont_right"),
			"LeftFloat" => $lng->txt("cont_left_float"),
			"RightFloat" => $lng->txt("cont_right_float"));
		$align_prop->setOptions($options);
		$this->form->addItem($align_prop);
		
		// caption
		$caption_prop = new ilTextAreaInputGUI($this->lng->txt("cont_caption"),
			"caption");
		$this->form->addItem($caption_prop);

		// save/cancel buttons
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("create_map", $lng->txt("save"));
			$this->form->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
		else
		{
			$this->form->addCommandButton("update_map", $lng->txt("save"));
			$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
		//$html = $form->getHTML();
	}

	/**
	* Create new Map.
	*/
	function create()
	{
		global $tpl;
		
		$this->initForm("create");
		if ($this->form->checkInput())
		{
			$this->content_obj = new ilPCMap($this->getPage());
			$location = $this->form->getInput("location");
			$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
			$this->content_obj->setLatitude($location["latitude"]);
			$this->content_obj->setLongitude($location["longitude"]);
			$this->content_obj->setZoom($location["zoom"]);
			$this->content_obj->setLayout($this->form->getInput("width"),
				$this->form->getInput("height"),
				$this->form->getInput("horizontal_align"));
			$this->content_obj->setCaption(
				$this->content_obj->handleCaptionInput($this->form->getInput("caption")));
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->ctrl->returnToParent($this, "jump".$this->hier_id);
				return;
			}
		}
		$this->displayValidationError();
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	* Update Map.
	*/
	function update()
	{
		$this->initForm("update");
		if ($this->form->checkInput())
		{
			$location = $this->form->getInput("location");
			$this->content_obj->setLatitude($location["latitude"]);
			$this->content_obj->setLongitude($location["longitude"]);
			$this->content_obj->setZoom($location["zoom"]);
			$this->content_obj->setLayout($this->form->getInput("width"),
				$this->form->getInput("height"),
				$this->form->getInput("horizontal_align"));
			$this->content_obj->setCaption(
				$this->content_obj->handleCaptionInput($this->form->getInput("caption")));
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->ctrl->returnToParent($this, "jump".$this->hier_id);
				return;
			}
		}
		$this->displayValidationError();
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHTML());
	}
}
?>
