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

require_once("./content/classes/class.ilLMTableData.php");
require_once("./content/classes/class.ilPageContentGUI.php");

/**
* Class ilLMTableDataGUI
*
* Handles user commands on table data elements (table cells)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilLMTableDataGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilLMTableDataGUI(&$a_lm_obj, &$a_pg_obj, &$a_content_obj, $a_hier_id)
	{
		parent::ilPageContentGUI($a_lm_obj, $a_pg_obj, $a_content_obj, $a_hier_id);
	}


	/**
	* insert new row after cell
	*/
	function newRowAfter()
	{
		$this->content_obj->newRowAfter();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}

	/**
	* insert new row before cell
	*/
	function newRowBefore()
	{
		$this->content_obj->newRowBefore();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}

	/**
	* delete a row
	*/
	function deleteRow()
	{
		$this->content_obj->deleteRow();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}


	/**
	* insert new col after cell
	*/
	function newColAfter()
	{
		$this->content_obj->newColAfter();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}

	/**
	* insert new col before cell
	*/
	function newColBefore()
	{
		$this->content_obj->newColBefore();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}

	/**
	* delete column
	*/
	function deleteCol()
	{
		$this->content_obj->deleteCol();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}


}
?>
