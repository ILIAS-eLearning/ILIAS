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


/**
* User Interface class for media pool objects
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @extends ilObjectGUI
* @package content
*/

require_once "classes/class.ilObjectGUI.php";
require_once "content/classes/class.ilObjMediaPool.php";

class ilObjMediaPoolGUI extends ilObjectGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjMediaPoolGUI($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = true)
	{
		global $lng;

		$this->type = "mep";
		$lng->loadLanguageModule("content");
		parent::ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		//$this->actions = $this->objDefinition->getActions("mep");
	}


	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff

		// always send a message
		sendInfo($this->lng->txt("object_added"),true);

		ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
	}


	/**
	* view object
	*
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem, $tree, $tpl;


		if (!$rbacsystem->checkAccess("visible,write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		if (!defined("ILIAS_MODULE"))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK","content/mep_edit.php?ref_id=".$this->object->getRefID());
			$this->tpl->setVariable("BTN_TARGET"," target=\"bottom\" ");
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("edit"));
			$this->tpl->parseCurrentBlock();
		}

		parent::editObject();
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		$cmd = $_GET["cmd"];
		if($cmd == "post")
		{
			$cmd = key($_POST["cmd"]);
		}
		if($cmd == "")
		{
			$cmd = "frameset";
		}

		$this->$cmd();
	}


	/**
	* output main frameset of media pool
	* left frame: explorer tree of folders
	* right frame: media pool content
	*/
	function frameset()
	{
		$this->tpl = new ilTemplate("tpl.mep_edit_frameset.html", false, false, "content");
		$this->tpl->setVariable("REF_ID",$this->ref_id);
		$this->tpl->show();
	}

	/**
	* output explorer tree with bookmark folders
	*/
	function explorer()
	{
		$this->tpl = new ilTemplate("tpl.main.html", true, true);

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		require_once ("content/classes/class.ilMediaPoolExplorer.php");
		$exp = new ilMediaPoolExplorer("mep_edit.php?cmd=listMedia&ref_id=".$this->object->getRefId(), $this->object);
		$exp->setTargetGet("obj_id");

		if ($_GET["mepexpand"] == "")
		{
			$mep_tree =& $this->object->getTree();
			$expanded = $mep_tree->readRootId();
		}
		else
		{
			$expanded = $_GET["mepexpand"];
		}

		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_folders"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->setVariable("ACTION", "mep_edit.php?cmd=explorer&ref_id=".$this->ref_id."&mepexpand=".$_GET["mepexpand"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show(false);

	}

}
?>
