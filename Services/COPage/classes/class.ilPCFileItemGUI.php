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

require_once("./Services/COPage/classes/class.ilPCListItem.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCFileItemGUI
*
* Handles user commands on items of file lists
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $I$
*
* @ingroup ServicesCOPage
*/
class ilPCFileItemGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCFileItemGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
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
	* insert new file item
	*/
	function newFileItem()
	{
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj = new ilObjFile();
		$fileObj->setType("file");
		$fileObj->setTitle($_FILES["Fobject"]["name"]["file"]);
		$fileObj->setDescription("");
		$fileObj->setFileName($_FILES["Fobject"]["name"]["file"]);
		$fileObj->setFileType($_FILES["Fobject"]["type"]["file"]);
		$fileObj->setFileSize($_FILES["Fobject"]["size"]["file"]);
		$fileObj->setMode("filelist");
		$fileObj->create();
		$fileObj->raiseUploadError(false);
		// upload file to filesystem
		$fileObj->createDirectory();
		$fileObj->getUploadFile($_FILES["Fobject"]["tmp_name"]["file"],
			$_FILES["Fobject"]["name"]["file"]);

		$this->file_object =& $fileObj;
	}


	/**
	* insert new list item after current one
	*/
	function newItemAfter()
	{
		global $ilTabs;
		
		if ($_GET["subCmd"] == "insertNew")
		{
			$_SESSION["cont_file_insert"] = "insertNew";
		}
		if ($_GET["subCmd"] == "insertFromRepository")
		{
			$_SESSION["cont_file_insert"] = "insertFromRepository";
		}
		if (($_GET["subCmd"] == "") && $_SESSION["cont_file_insert"] != "")
		{
			$_GET["subCmd"] = $_SESSION["cont_file_insert"];
		}

		switch ($_GET["subCmd"])
		{
			case "insertFromRepository":
				$this->insertFromRepository("newItemAfter");
				break;
				
			case "selectFile":
				$this->insertNewItemAfter($_GET["file_ref_id"]);
				break;
				
			default:
				$this->setTabs("newItemAfter");
				$ilTabs->setSubTabActive("cont_new_file");
		
				// new file list form
				$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.file_item_edit.html", "Services/COPage");
				$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_file_item"));
				$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		
				$this->displayValidationError();
		
				// file
				$this->tpl->setVariable("TXT_FILE", $this->lng->txt("file"));
		
				$this->tpl->parseCurrentBlock();
		
				// operations
				$this->tpl->setCurrentBlock("commands");
				$this->tpl->setVariable("BTN_NAME", "insertNewItemAfter");
				$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
				$this->tpl->parseCurrentBlock();
				break;
		}

	}

	/**
	* Insert file from repository
	*/
	function insertFromRepository($a_cmd)
	{
		global $ilTabs, $tree, $ilCtrl, $tpl;
		
		$this->setTabs($a_cmd);
		$ilTabs->setSubTabActive("cont_file_from_repository");
		
		include_once "./Services/COPage/classes/class.ilFileSelectorGUI.php";

		$exp = new ilFileSelectorGUI($this->ctrl->getLinkTarget($this, $a_cmd),
			"ilpcfileitemgui");

		if ($_GET["expand"] == "")
		{
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET["expand"];
		}
		$exp->setExpand($expanded);

		$exp->setTargetGet("sel_id");
		//$this->ctrl->setParameter($this, "target_type", $a_type);
		$ilCtrl->setParameter($this, "subCmd", "insertFromRepository");
		$exp->setParamsGet($this->ctrl->getParameterArray($this, $a_cmd));
		
		// filter
		$exp->setFiltered(true);
		$exp->setFilterMode(IL_FM_POSITIVE);
		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->addFilter("fold");
		$exp->addFilter("crs");
		$exp->addFilter("file");

		$sel_types = array('file');

		$exp->setOutput(0);

		$tpl->setContent($exp->getOutput());
	}

	/**
	* insert new file item after another item
	*/
	function insertNewItemAfter($a_file_ref_id = 0)
	{
		if ($a_file_ref_id == 0)
		{
			$this->newFileItem();
		}
		else
		{
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$this->file_object = new ilObjFile($a_file_ref_id);
		}
		$this->content_obj->newItemAfter($this->file_object->getId(),
			$this->file_object->getFileName(), $this->file_object->getFileType());
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$_GET["subCmd"] = "-";
			$this->newItemAfter();
		}
	}

	/**
	* insert new list item before current one
	*/
	function newItemBefore()
	{
		global $ilTabs;
		
		if ($_GET["subCmd"] == "insertNew")
		{
			$_SESSION["cont_file_insert"] = "insertNew";
		}
		if ($_GET["subCmd"] == "insertFromRepository")
		{
			$_SESSION["cont_file_insert"] = "insertFromRepository";
		}
		if (($_GET["subCmd"] == "") && $_SESSION["cont_file_insert"] != "")
		{
			$_GET["subCmd"] = $_SESSION["cont_file_insert"];
		}

		switch ($_GET["subCmd"])
		{
			case "insertFromRepository":
				$this->insertFromRepository("newItemBefore");
				break;
				
			case "selectFile":
				$this->insertNewItemBefore($_GET["file_ref_id"]);
				break;
				
			default:
				$this->setTabs("newItemBefore");
				$ilTabs->setSubTabActive("cont_new_file");
		
				// new file list form
				$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.file_item_edit.html", "Services/COPage");
				$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_file_item"));
				$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		
				$this->displayValidationError();
		
				// file
				$this->tpl->setVariable("TXT_FILE", $this->lng->txt("file"));
		
				$this->tpl->parseCurrentBlock();
		
				// operations
				$this->tpl->setCurrentBlock("commands");
				$this->tpl->setVariable("BTN_NAME", "insertNewItemBefore");
				$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
				$this->tpl->parseCurrentBlock();
				break;
		}

	}

	/**
	* insert new list item before current one
	*/
	function insertNewItemBefore($a_file_ref_id = 0)
	{
		if ($a_file_ref_id == 0)
		{
			$this->newFileItem();
		}
		else
		{
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$this->file_object = new ilObjFile($a_file_ref_id);
		}
		$this->content_obj->newItemBefore($this->file_object->getId(),
			$this->file_object->getFileName(), $this->file_object->getFileType());
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$_GET["subCmd"] = "-";
			$this->newItemBefore();
		}
	}

	/**
	* delete a list item
	*/
	function deleteItem()
	{
		$this->content_obj->deleteItem();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* output tabs
	*/
	function setTabs($a_cmd = "")
	{
		global $ilTabs, $ilCtrl;

		$ilTabs->addTarget("cont_back",
			$this->ctrl->getParentReturn($this), "",
			"");
			
		if ($a_cmd != "")
		{
			$ilCtrl->setParameter($this, "subCmd", "insertNew");
			$ilTabs->addSubTabTarget("cont_new_file",
				$ilCtrl->getLinkTarget($this, $a_cmd), $a_cmd);
	
			$ilCtrl->setParameter($this, "subCmd", "insertFromRepository");
			$ilTabs->addSubTabTarget("cont_file_from_repository",
				$ilCtrl->getLinkTarget($this, $a_cmd), $a_cmd);
			$ilCtrl->setParameter($this, "subCmd", "");
		}
	}

	/**
	* move list item down
	*/
	function moveItemDown()
	{
		$this->content_obj->moveItemDown();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* move list item up
	*/
	function moveItemUp()
	{
		$this->content_obj->moveItemUp();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}


}
?>
