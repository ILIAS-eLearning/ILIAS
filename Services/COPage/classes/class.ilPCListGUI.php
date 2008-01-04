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
	function ilPCListGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id)
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
	* insert new list form
	*/
	function insert()
	{
		// new list form (list item number)
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.list_new.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_list"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->displayValidationError();

		for($i=1; $i<=10; $i++)
		{
			$nr[$i] = $i;
		}

		// select fields for number of columns
		$this->tpl->setVariable("TXT_ORDER", $this->lng->txt("cont_order"));
		$order = array("Unordered" => $this->lng->txt("cont_Unordered"),
			"Number" => $this->lng->txt("cont_Number"),
			"Roman" => $this->lng->txt("cont_Roman"),
			"roman" => $this->lng->txt("cont_roman"),
			"Alphabetic" => $this->lng->txt("cont_Alphabetic"),
			"alphabetic" => $this->lng->txt("cont_alphabetic"));
		$select_order = ilUtil::formSelect ("","list_order",$order,false,true);
		$this->tpl->setVariable("SELECT_ORDER", $select_order);
		$this->tpl->setVariable("TXT_NR_ITEMS", $this->lng->txt("cont_nr_items"));
		$select_items = ilUtil::formSelect ("2","nr_items",$nr,false,true);
		$this->tpl->setVariable("SELECT_NR_ITEMS", $select_items);

		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "create_list");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", "cancelCreate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

	}


	/**
	* create new table in dom and update page in db
	*/
	function create()
	{
		$this->content_obj = new ilPCList($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id);
		$this->content_obj->addItems($_POST["nr_items"]);
		$this->content_obj->setOrderType($_POST["list_order"]);
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
	* edit properties form
	*/
	function edit()
	{
		// add paragraph edit template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.list_properties.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_list_properties"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->displayValidationError();

		// content is in utf-8, todo: set globally
		//header('Content-type: text/html; charset=UTF-8');

		// list
		$this->tpl->setVariable("TXT_LIST", $this->lng->txt("cont_list_properties"));

		$this->tpl->setVariable("TXT_ORDER", $this->lng->txt("cont_order"));
		$order = array("Unordered" => $this->lng->txt("cont_Unordered"),
			"Number" => $this->lng->txt("cont_Number"),
			"Roman" => $this->lng->txt("cont_Roman"),
			"roman" => $this->lng->txt("cont_roman"),
			"Alphabetic" => $this->lng->txt("cont_Alphabetic"),
			"alphabetic" => $this->lng->txt("cont_alphabetic"));
		$select_order = ilUtil::formSelect ("","list_order",$order,false,true);
		$this->tpl->setVariable("SELECT_ORDER", $select_order);

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", "cancelUpdate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

	}


	/**
	* save table properties in db and return to page edit screen
	*/
	function saveProperties()
	{
		$this->content_obj->setOrderType($_POST["list_order"]);
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
