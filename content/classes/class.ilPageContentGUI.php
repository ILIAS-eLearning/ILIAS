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

require_once("./content/classes/class.ilPageContent.php");

/**
* User Interface for Editing of Page Content Objects (Paragraphs, Tables, ...)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilPageContentGUI
{
	var $content_obj;
	var $ilias;
	var $tpl;
	var $lng;
	var $lm_obj;
	var $pg_obj;
	var $hier_id;
	var $dom;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageContentGUI(&$a_lm_obj, &$a_pg_obj, &$a_content_obj, $a_hier_id)
	{
		global $ilias, $tpl, $lng;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->lm_obj =& $a_lm_obj;
		$this->pg_obj =& $a_pg_obj;
		$this->content_obj =& $a_content_obj;
		$this->hier_id = $a_hier_id;
		$this->dom =& $a_pg_obj->getDom();
	}


	function delete()
	{
		$this->pg_obj->deleteContent($this->hier_id);
		//$this->pg_obj->update();
		header("location: lm_edit.php?cmd=viewWysiwyg&lm_id=".$this->lm_obj->getId()."&obj_id=".
			$this->pg_obj->getId());
	}

	function moveAfter()
	{
		// check if a target is selected
		if(!isset($_POST["target"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// check if only one target is selected
		if(count($_POST["target"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("only_one_target"),$this->ilias->error_obj->MESSAGE);
		}

		// check if target is within source
		if($this->hier_id == substr($_POST["target"][0], 0, strlen($this->hier_id)))
		{
			$this->ilias->raiseError($this->lng->txt("cont_target_within_source"),$this->ilias->error_obj->MESSAGE);
		}
//echo "PCGUItarget:".$_POST["target"][0]."<br>";
		// move
		$this->pg_obj->moveContentAfter($this->hier_id, $_POST["target"][0]);
		header("location: lm_edit.php?cmd=viewWysiwyg&lm_id=".$this->lm_obj->getId()."&obj_id=".
			$this->pg_obj->getId());
	}

	function moveBefore()
	{
		// check if a target is selected
		if(!isset($_POST["target"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// check if target is within source
		if(count($_POST["target"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("only_one_target"),$this->ilias->error_obj->MESSAGE);
		}

		// check if target is within source
		if($this->hier_id == substr($_POST["target"][0], 0, strlen($this->hier_id)))
		{
			$this->ilias->raiseError($this->lng->txt("cont_target_within_source"),$this->ilias->error_obj->MESSAGE);
		}

		// move
		$this->pg_obj->moveContentBefore($this->hier_id, $_POST["target"][0]);
		header("location: lm_edit.php?cmd=viewWysiwyg&lm_id=".$this->lm_obj->getId()."&obj_id=".
			$this->pg_obj->getId());
	}

}
?>
