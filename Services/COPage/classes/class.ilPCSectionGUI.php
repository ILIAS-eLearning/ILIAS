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

require_once("./Services/COPage/classes/class.ilPCSection.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCSectionGUI
*
* User Interface for LM Section Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCSectionGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCSectionGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id)
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.section_new.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_section"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->displayValidationError();

		// select fields for number of columns
		$this->tpl->setVariable("TXT_CHARACTERISTIC", $this->lng->txt("cont_characteristic"));
		$order = array("ilc_Block" => $this->lng->txt("cont_sec_block"),
			"ilc_Example" => $this->lng->txt("cont_sec_example"),
			"ilc_Citation" => $this->lng->txt("cont_citation"),
			"ilc_Additional" => $this->lng->txt("cont_sec_additional"),
			"ilc_Special" => $this->lng->txt("cont_sec_special"),
			"ilc_Excursus" => $this->lng->txt("cont_sec_excursus"));
		$select_class = ilUtil::formSelect ("","characteristic",$order,false,true);
		$this->tpl->setVariable("SELECT_CHARACTERISTIC", $select_class);

		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "create_section");
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
		$this->content_obj = new ilPCSection($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id);
		$this->content_obj->setCharacteristic($_POST["characteristic"]);
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
/*
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
*/

	/**
	* save table properties in db and return to page edit screen
	*/
/*
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
*/
}
?>
