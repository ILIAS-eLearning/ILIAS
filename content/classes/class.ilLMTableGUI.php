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

require_once("./content/classes/class.ilLMTable.php");
require_once("./content/classes/class.ilPageContentGUI.php");

/**
* Class ilLMTableGUI
*
* User Interface for Table Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilLMTableGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilLMTableGUI(&$a_lm_obj, &$a_pg_obj, &$a_content_obj, $a_hier_id)
	{
		parent::ilPageContentGUI($a_lm_obj, $a_pg_obj, $a_content_obj, $a_hier_id);
	}

	function edit()
	{
	}


	// command suffix maybe "_child"
	function insert($a_command_suffix = "")
	{

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "create".$a_command_suffix);	//--
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	function insert_child()
	{
		$this->insert("_child");
	}


	function update()
	{
		$this->pg_obj->update();
		header("location: lm_edit.php?cmd=viewWysiwyg&lm_id=".$this->lm_obj->getId()."&obj_id=".
			$this->pg_obj->getId());
		exit;

	}

	function create()
	{
		/*
		$new_par = new il($this->dom);
		$new_par->createNode();
		$new_par->setText($new_par->input2xml($_POST["par_content"]));
		*/
		//$this->pg_obj->insertContent($new_par, $this->hier_id, IL_INSERT_AFTER);
		header("location: lm_edit.php?cmd=viewWysiwyg&lm_id=".$this->lm_obj->getId()."&obj_id=".
			$this->pg_obj->getId());
	}

	/**
	* create table as first child of a container (e.g. a TableData Element)
	*/
	function create_child()
	{
		/*
		$new_par = new ilParagraph($this->dom);
		$new_par->createNode();
		$new_par->setText($new_par->input2xml($_POST["par_content"]));
		$this->pg_obj->insertContent($new_par, $this->hier_id, IL_INSERT_CHILD);*/

		header("location: lm_edit.php?cmd=viewWysiwyg&lm_id=".$this->lm_obj->getId()."&obj_id=".
			$this->pg_obj->getId());
	}

}
?>
