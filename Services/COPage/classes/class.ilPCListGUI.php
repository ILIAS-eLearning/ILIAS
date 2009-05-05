<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("./Services/COPage/classes/class.ilPCList.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCListGUI
*
* User Interface for LM List Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCListGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCListGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
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
	* insert new list form
	*/
	function insert()
	{
		$this->displayValidationError();
		
		$this->initListForm("create");
		$this->tpl->setContent($this->form->getHTML());
	}


	/**
	* Save list
	*/
	public function create()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initListForm("create");
		if ($this->form->checkInput())
		{
			$this->content_obj = new ilPCList($this->dom);
			$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
			$this->content_obj->addItems($_POST["nr_items"]);
			$this->content_obj->setOrderType($_POST["list_order"]);
			$this->content_obj->setStartValue($_POST["start_value"]);
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->ctrl->returnToParent($this, "jump".$this->hier_id);
			}
		}
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
	/**
	* edit properties form
	*/
	function edit()
	{
		$this->displayValidationError();
		
		$this->initListForm("edit");
		$this->getValues();
		$this->tpl->setContent($this->form->getHTML());
	}

	/**
	* Save properties
	*/
	function saveProperties()
	{
		global $lng, $ilCtrl, $tpl;
		
		$this->initListForm("edit");
		if ($this->form->checkInput())
		{
			$this->content_obj->setOrderType($_POST["list_order"]);
			$this->content_obj->setStartValue($_POST["start_value"]);
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->ctrl->returnToParent($this, "jump".$this->hier_id);
			}
		}
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
	/**
	* Init list form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initListForm($a_mode = "edit")
	{
		global $lng;
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// type
		$options = array(
			"Unordered" => $this->lng->txt("cont_Unordered"),
			"Number" => $this->lng->txt("cont_number_std"),
			"Decimal" => $this->lng->txt("cont_decimal"),
			"Roman" => $this->lng->txt("cont_roman"),
			"roman" => $this->lng->txt("cont_roman_s"),
			"Alphabetic" => $this->lng->txt("cont_alphabetic"),
			"alphabetic" => $this->lng->txt("cont_alphabetic_s")
			);
		$si = new ilSelectInputGUI($this->lng->txt("cont_order"), "list_order");
		$si->setOptions($options);
		$this->form->addItem($si);
		
		// nr of items
		$options = array();
		if ($a_mode == "create")
		{
			for ($i=1; $i<=10; $i++)
			{
				$options[$i] = $i;
			}
			$si = new ilSelectInputGUI($this->lng->txt("cont_nr_items"), "nr_items");
			$si->setOptions($options);
			$si->setValue(2);
			$this->form->addItem($si);
		}
		
		// starting value
		$ni = new ilNumberInputGUI($this->lng->txt("cont_start_value"), "start_value");
		$ni->setMaxLength(3);
		$ni->setSize(3);
		$ni->setInfo($lng->txt("cont_start_value_info"));
		$this->form->addItem($ni);
	
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("create_list", $lng->txt("save"));
			$this->form->addCommandButton("cancelCreate", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("cont_insert_list"));
		}
		else
		{
			$this->form->addCommandButton("saveProperties", $lng->txt("save"));
			$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("cont_list_properties"));
		}
	                
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	}
	
	/**
	* Get current values for list from 
	*
	*/
	public function getValues()
	{
		$values = array();
	
		$values["list_order"] = $this->content_obj->getOrderType();
		$values["start_value"] = $this->content_obj->getStartValue();
	
		$this->form->setValuesByArray($values);
	}
}
?>
