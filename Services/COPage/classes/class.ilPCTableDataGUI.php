<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCTableData.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCTableDataGUI
*
* Handles user commands on table data elements (table cells)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTableDataGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCTableDataGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
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
	* insert new row after cell
	*/
	function newRowAfter()
	{
		$this->content_obj->newRowAfter();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* insert new row before cell
	*/
	function newRowBefore()
	{
		$this->content_obj->newRowBefore();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* delete a row
	*/
	function deleteRow()
	{
		$this->content_obj->deleteRow();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}


	/**
	* insert new col after cell
	*/
	function newColAfter()
	{
		$this->content_obj->newColAfter();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* insert new col before cell
	*/
	function newColBefore()
	{
		$this->content_obj->newColBefore();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* delete column
	*/
	function deleteCol()
	{
		$this->content_obj->deleteCol();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* move row down
	*/
	function moveRowDown()
	{
		$this->content_obj->moveRowDown();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* move list item up
	*/
	function moveRowUp()
	{
		$this->content_obj->moveRowUp();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* move column right
	*/
	function moveColRight()
	{
		$this->content_obj->moveColRight();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* move list item up
	*/
	function moveColLeft()
	{
		$this->content_obj->moveColLeft();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

}
?>
