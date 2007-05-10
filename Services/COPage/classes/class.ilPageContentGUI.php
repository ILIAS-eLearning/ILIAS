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

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* User Interface for Editing of Page Content Objects (Paragraphs, Tables, ...)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPageContentGUI
{
	var $content_obj;
	var $ilias;
	var $tpl;
	var $lng;
	var $ctrl;
	var $pg_obj;
	var $hier_id;
	var $dom;
	var $updated;
	var $target_script;
	var $return_location;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageContentGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id = 0)
	{
		global $ilias, $tpl, $lng, $ilCtrl;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->pg_obj =& $a_pg_obj;
		$this->ctrl =& $ilCtrl;

		$this->content_obj =& $a_content_obj;

		if($a_hier_id !== 0)
		{
			$this->hier_id = $a_hier_id;
			$this->dom =& $a_pg_obj->getDom();
		}
	}

	/*
	function setTargetScript($a_target_script)
	{
		$this->target_script = $a_target_script;
	}

	function getTargetScript()
	{
		return $this->target_script;
	}

	function setReturnLocation($a_location)
	{
		$this->return_location = $a_location;
	}

	function getReturnLocation()
	{
		return $this->return_location;
	}*/

	/**
	* get hierarchical id in dom object
	*/
	function getHierId()
	{
		return $this->hier_id;
	}

	/**
	* delete content element
	*/
	function delete()
	{
		$updated = $this->pg_obj->deleteContent($this->hier_id);
		if($updated !== true)
		{
			$_SESSION["il_pg_error"] = $updated;
		}
		else
		{
			unset($_SESSION["il_pg_error"]);
		}
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* move content element after another element
	*/
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

		// check whether target is allowed
		$curr_node =& $this->pg_obj->getContentNode($_POST["target"][0]);
		if (is_object($curr_node) && $curr_node->node_name() == "FileItem")
		{
			$this->ilias->raiseError($this->lng->txt("cont_operation_not_allowed"),$this->ilias->error_obj->MESSAGE);
		}

		// strip "c" "r" of table ids from hierarchical id
		$first_hier_character = substr($_POST["target"][0], 0, 1);
		if ($first_hier_character == "c" ||
			$first_hier_character == "r" ||
			$first_hier_character == "i")
		{
			$_POST["target"][0] = substr($_POST["target"][0], 1);
		}

		// move
		$updated = $this->pg_obj->moveContentAfter($this->hier_id, $_POST["target"][0]);
		if($updated !== true)
		{
			$_SESSION["il_pg_error"] = $updated;
		}
		else
		{
			unset($_SESSION["il_pg_error"]);
		}

		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* move content element before another element
	*/
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

		// check whether target is allowed
		$curr_node =& $this->pg_obj->getContentNode($_POST["target"][0]);
		if (is_object($curr_node) && $curr_node->node_name() == "FileItem")
		{
			$this->ilias->raiseError($this->lng->txt("cont_operation_not_allowed"),$this->ilias->error_obj->MESSAGE);
		}

		// strip "c" "r" of table ids from hierarchical id
		$first_hier_character = substr($_POST["target"][0], 0, 1);
		if ($first_hier_character == "c" ||
			$first_hier_character == "r" ||
			$first_hier_character == "i")
		{
			$_POST["target"][0] = substr($_POST["target"][0], 1);
		}

		// move
		$updated = $this->pg_obj->moveContentBefore($this->hier_id, $_POST["target"][0]);
		if($updated !== true)
		{
			$_SESSION["il_pg_error"] = $updated;
		}
		else
		{
			unset($_SESSION["il_pg_error"]);
		}
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}
	
	
	/**
	* split page to new page at specified position
	*/
	function splitPage()
	{
		global $ilErr;
		
		if ($this->pg_obj->getParentType() != "lm" &&
			$this->pg_obj->getParentType() != "dbk")
		{
			$ilErr->raiseError("Split method called for wrong parent type (".
			$this->pg_obj->getParentType().")", $ilErr->FATAL);
		}
		else
		{
			$lm_page =& ilLMPageObject::_splitPage($this->pg_obj->getId(),
				$this->pg_obj->getParentType(), $this->hier_id);
				
			// jump to new page
			$this->ctrl->setParameterByClass("illmpageobjectgui", "obj_id", $lm_page->getId());
			$this->ctrl->redirectByClass("illmpageobjectgui", "view");
		}
		
		$this->ctrl->returnToParent($this, "jump".($this->hier_id - 1));
	}

	/**
	* split page to next page at specified position
	*/
	function splitPageNext()
	{
		global $ilErr;
		
		if ($this->pg_obj->getParentType() != "lm" &&
			$this->pg_obj->getParentType() != "dbk")
		{
			$ilErr->raiseError("Split method called for wrong parent type (".
			$this->pg_obj->getParentType().")", $ilErr->FATAL);
		}
		else
		{
			$succ_id = ilLMPageObject::_splitPageNext($this->pg_obj->getId(),
				$this->pg_obj->getParentType(), $this->hier_id);
			
			// jump to successor page
			if ($succ_id > 0)
			{
				$this->ctrl->setParameterByClass("illmpageobjectgui", "obj_id", $succ_id);
				$this->ctrl->redirectByClass("illmpageobjectgui", "view");
			}

		}
		$this->ctrl->returnToParent($this, "jump".($this->hier_id - 1));
	}

	/**
	* display validation errors
	*/
	function displayValidationError()
	{
		if(is_array($this->updated))
		{
			$error_str = "<b>Validation Error(s):</b><br>";
			foreach ($this->updated as $error)
			{
				$err_mess = implode($error, " - ");
				if (!is_int(strpos($err_mess, ":0:")))
				{
					$error_str .= htmlentities($err_mess)."<br />";
				}
			}
			$this->tpl->setVariable("MESSAGE", $error_str);
		}
		else if($this->updated != "" && $this->updated !== true)
		{
			$this->tpl->setVariable("MESSAGE", "<b>Validation Error(s):</b><br />".
				$this->updated."<br />");
		}
	}
	
	/**
	* cancel creating page content
	*/
	function cancelCreate()
	{
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* cancel update
	*/
	function cancelUpdate()
	{
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}


	/**
	 * gui function
	 * set enabled if is not enabled and vice versa
	 */
	function deactivate() 
	{		
		$obj = & $this->content_obj;
		
	 	if ($obj->isEnabled ()) 
	 		$obj->disable ();
	 	else
	 		$obj->enable ();
	 	
	 	$updated = $this->pg_obj->update($this->hier_id);
		if($updated !== true)
		{
			$_SESSION["il_pg_error"] = $updated;
		}
		else
		{
			unset($_SESSION["il_pg_error"]);
		}
	
	 	$this->ctrl->returnToParent($this, "jump".$this->hier_id);	 	
	}

}
?>
