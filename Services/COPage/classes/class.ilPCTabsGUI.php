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

require_once("./Services/COPage/classes/class.ilPCTabs.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCTabsGUI
*
* User Interface for Tabbed Content
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTabsGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCTabsGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
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
	* Insert new tabs
	*/
	function insert()
	{
		$this->edit(true);
	}

	/**
	* Insert tabs form.
	*/
	function edit($a_insert = false)
	{
		global $ilCtrl, $tpl, $lng;

		$this->displayValidationError();
		
		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		if ($a_insert)
		{
			$form->setTitle($this->lng->txt("cont_insert_tabs"));
		}
		else
		{
			$form->setTitle($this->lng->txt("cont_update_tabs"));
		}
		
		// tabs type
		$type_prop = new ilSelectInputGUI($this->lng->txt("cont_type"),
			"type");
		$types = array("HorizontalTabs" => $this->lng->txt("cont_tabs_hor_tabs"),
			"Accordion" => $this->lng->txt("cont_tabs_accordion"));
		$selected = ($a_insert)
			? ""
			: $this->content_obj->getTabType();
		$type_prop->setValue($selected);
		$type_prop->setOptions($types);
		$form->addItem($type_prop);
		
		// number of initial tabs
		if ($a_insert)
		{
			$nr_prop = new ilSelectInputGUI($this->lng->txt("cont_number_of_tabs"),
				"nr");
			$nrs = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 
				7 => 7, 8 => 8, 9 => 9, 10 => 10);
			$nr_prop->setOptions($nrs);
			$form->addItem($nr_prop);
		}
		else
		{
			$captions = $this->content_obj->getCaptions();
			$i = 0;
			foreach($captions as $caption)
			{
				$cap_prop[$i] = new ilTextInputGUI($this->lng->txt("cont_caption")." ".($i + 1),
					"caption[$i]");
				$cap_prop[$i]->setValue($caption);
				$form->addItem($cap_prop[$i]);
				$i++;
			}
		}
		
		// save/cancel buttons
		if ($a_insert)
		{
			$form->addCommandButton("create_section", $lng->txt("save"));
			$form->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
		else
		{
			$form->addCommandButton("update_section", $lng->txt("save"));
			$form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
		$html = $form->getHTML();
		$tpl->setContent($html);
		return $ret;
	}


	/**
	* Create new tabs in dom and update page in db
	*/
	function create()
	{
		$this->content_obj = new ilPCTabs($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
		$this->content_obj->addItems($_POST["nr"]);
		$this->content_obj->setTabType($_POST["type"]);
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->insert();
		}
	}

	/**
	* Save tabs properties in db and return to page edit screen
	*/
	function update()
	{
		$this->content_obj->setTabType(ilUtil::stripSlashes($_POST["type"]));
		if (is_array($_POST["caption"]))
		{
			$caption = array();
			foreach($_POST["caption"] as $k => $v)
			{
				$caption[$k] = ilUtil::stripSlashes($v);
			}
			$this->content_obj->setCaptions($caption);
		}
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->pg_obj->addHierIDs();
			$this->edit();
		}
	}
}
?>
