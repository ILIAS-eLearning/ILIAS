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

require_once("./Services/COPage/classes/class.ilPCResources.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCResourcesGUI
*
* User Interface for Resources Component Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCResourcesGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCResourcesGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id)
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id);
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
	* Insert new resources component form.
	*/
	function insert()
	{
		$this->edit(true);
	}

	/**
	* Edit resources form.
	*/
	function edit($a_insert = false)
	{
		global $ilCtrl, $tpl, $lng, $objDefinition;
		
		$this->displayValidationError();
		
		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		if ($a_insert)
		{
			$form->setTitle($this->lng->txt("cont_insert_resources"));
		}
		else
		{
			$form->setTitle($this->lng->txt("cont_update_resources"));
		}
		
		// type selection
		$type_prop = new ilRadioGroupInputGUI($this->lng->txt("cont_type"),
			"type");
		$obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
		$obj_type = ilObject::_lookupType($obj_id);
		$sub_objs = $objDefinition->getGroupedRepositoryObjectTypes($obj_type);
		$types = array();
		foreach($sub_objs as $k => $so)
		{
			$types[$k] = $this->lng->txt("objs_".$k);
		}
		foreach($types as $k => $type)
		{
			$option = new ilRadioOption($type, $k, "");
			$type_prop->addOption($option);
		}
		$selected = ($a_insert)
			? ""
			: $this->content_obj->getResourceListType();
		$type_prop->setValue($selected);
		$form->addItem($type_prop);
		
		// save/cancel buttons
		if ($a_insert)
		{
			$form->addCommandButton("create_resources", $lng->txt("save"));
			$form->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
		else
		{
			$form->addCommandButton("update_resources", $lng->txt("save"));
			$form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
		$html = $form->getHTML();
		$tpl->setContent($html);
		return $ret;

	}


	/**
	* Create new Resources Component.
	*/
	function create()
	{
		$this->content_obj = new ilPCResources($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id);
		$this->content_obj->setResourceListType($_POST["type"]);
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
	* Update Resources Component.
	*/
	function update()
	{
		$this->content_obj->setResourceListType($_POST["type"]);
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
