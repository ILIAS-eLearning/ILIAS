<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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


/**
* Class ilLMObject
*
* Base class for ilStructureObjects and ilPageObjects (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMObjectGUI
{
	var $ilias;
	var $tpl;
	var $lng;
	var $obj;
	var $ctrl;
	var $content_object;
	var $actions;


	/**
	* constructor
	*
	* @param	object		$a_content_obj		content object
	*/
	function ilLMObjectGUI(&$a_content_obj)
	{
		global $ilias, $tpl, $lng, $ilCtrl;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;
		$this->content_object =& $a_content_obj;
	}


	/**
	* build action array
	*
	* @param	array		$a_actions		action array (key = action key,
	*										value = action language string)
	* @access	private
	*/
	function setActions($a_actions = "")
	{
		if (is_array($a_actions))
		{
			foreach ($a_actions as $name => $lng)
			{
				$this->actions[$name] = array("name" => $name, "lng" => $lng);
			}
		}
		else
		{
			$this->actions = "";
		}
	}


	/**
	* get target frame for command (command is method name without "Object", e.g. "perm")
	* @param	string		$a_cmd			command
	* @param	string		$a_target_frame	default target frame (is returned, if no special
	*										target frame was set)
	* @access	public
	*/
	function getTargetFrame($a_cmd, $a_target_frame = "")
	{
		if ($this->target_frame[$a_cmd] != "")
		{
			return $this->target_frame[$a_cmd];
		}
		elseif (!empty($a_target_frame))
		{
			return "target=\"".$a_target_frame."\"";
		}
		else
		{
			return;
		}
	}

	/**
	* get a template blockfile
	* format: tpl.<objtype>_<command>.html
	*
	* @param	string	command
	* @param	string	object type definition
	* @access	public
 	*/
	function getTemplateFile($a_cmd,$a_type = "")
	{
		if (!$a_type)
		{
			$a_type = $_GET["type"];
		}

		$template = "tpl.".$a_type."_".$a_cmd.".html";

		if (!$this->tpl->fileExists($template))
		{
			$template = "tpl.obj_".$a_cmd.".html";
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", $template);
	}


	/**
	* structure / page object creation form
	*/
	function create()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		// fill in saved values in case of error
		$data = array();
		$data["fields"] = array();
		$data["fields"]["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
		$data["fields"]["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];

		$this->getTemplateFile("edit",$new_type);

		foreach ($data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);

			if ($this->prepare_output)
			{
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->ctrl->setParameter($this, "new_type", $new_type);
//echo "<br>lmobjectgui_formaction";
//echo ":".$this->ctrl->getFormAction($this, "save", "bla").":";
		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this, "save"));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

	}


	/**
	* put this object into content object tree
	*/
	function putInTree()
	{
		$tree = new ilTree($this->content_object->getId());
		$tree->setTableNames('lm_tree', 'lm_data');
		$tree->setTreeTablePK("lm_id");

		$parent_id = (!empty($_GET["obj_id"]))
			? $_GET["obj_id"]
			: $tree->getRootId();

		if (!empty($_GET["target"]))
		{
			$target = $_GET["target"];
		}
		else
		{
			// determine last child of current type
			$childs =& $tree->getChildsByType($parent_id, $this->obj->getType());
			if (count($childs) == 0)
			{
				$target = IL_FIRST_NODE;
			}
			else
			{
				$target = $childs[count($childs) - 1]["obj_id"];
			}
		}
		if (!$tree->isInTree($this->obj->getId()))
		{
			$tree->insertNode($this->obj->getId(), $parent_id, $target);
		}
	}


	/**
	* Confirm deletion screen (delete page or structure objects)
	*/
	function delete()
	{
		$this->setTabs();

		$cont_obj_gui =& new ilObjContentObjectGUI("",$this->content_object->getRefId(),
			true, false);
		$cont_obj_gui->delete($this->obj->getId());
	}


	/**
	* cancel deletion of page/structure objects
	*/
	function cancelDelete()
	{
		session_unregister("saved_post");
		$this->ctrl->redirect($this, $_GET["backcmd"]);
	}


	/**
	* page and structure object deletion
	*/
	function confirmedDelete()
	{
		$cont_obj_gui =& new ilObjContentObjectGUI("",$this->content_object->getRefId(),
			true, false);
		$cont_obj_gui->confirmedDelete($this->obj->getId());
		$this->ctrl->redirect($this, $_GET["backcmd"]);
	}


	/**
	* output a cell in object list
	*/
	function add_cell($val, $link = "")
	{
		if(!empty($link))
		{
			$this->tpl->setCurrentBlock("begin_link");
			$this->tpl->setVariable("LINK_TARGET", $link);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("end_link");
		}

		$this->tpl->setCurrentBlock("text");
		$this->tpl->setVariable("TEXT_CONTENT", $val);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("table_cell");
		$this->tpl->parseCurrentBlock();
	}


	/**
	* show possible action (form buttons)
	*
	* @access	public
	*/
	function showActions()
	{
		$notoperations = array();
		$operations = array();

		$operations = $this->actions;

		if (count($operations) > 0)
		{
			foreach ($operations as $val)
			{
				$this->tpl->setCurrentBlock("operation_btn");
				$this->tpl->setVariable("BTN_NAME", $val["name"]);
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("operation");
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* check the content object tree
	*/
	function checkTree()
	{
		$this->content_object->checkTree();
	}
}
?>
