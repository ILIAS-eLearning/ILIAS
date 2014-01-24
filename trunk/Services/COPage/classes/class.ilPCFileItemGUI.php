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
		global $lng;
		
		if ($_FILES["file"]["name"] == "")
		{
			$_GET["subCmd"] = "-";
			ilUtil::sendFailure($lng->txt("upload_error_file_not_found"));
			return false;
		}
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj = new ilObjFile();
		$fileObj->setType("file");
		$fileObj->setTitle($_FILES["file"]["name"]);
		$fileObj->setDescription("");
		$fileObj->setFileName($_FILES["file"]["name"]);
		$fileObj->setFileType($_FILES["file"]["type"]);
		$fileObj->setFileSize($_FILES["file"]["size"]);
		$fileObj->setMode("filelist");
		$fileObj->create();
		$fileObj->raiseUploadError(false);
		// upload file to filesystem
		$fileObj->createDirectory();
		$fileObj->getUploadFile($_FILES["file"]["tmp_name"],
			$_FILES["file"]["name"]);

		$this->file_object =& $fileObj;
		return true;
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
		if ($_GET["subCmd"] == "insertFromWorkspace")
		{
			$_SESSION["cont_file_insert"] = "insertFromWorkspace";
		}
		if (($_GET["subCmd"] == "") && $_SESSION["cont_file_insert"] != "")
		{
			$_GET["subCmd"] = $_SESSION["cont_file_insert"];
		}

		switch ($_GET["subCmd"])
		{
			case "insertFromWorkspace":
				$this->insertFromWorkspace("newItemAfter");
				break;
			
			case "insertFromRepository":
				$this->insertFromRepository("newItemAfter");
				break;
				
			case "selectFile":
				$this->insertNewItemAfter($_GET["file_ref_id"]);
				break;
				
			default:
				$this->setTabs("newItemAfter");
				$ilTabs->setSubTabActive("cont_new_file");
		
				$this->displayValidationError();
				$form = $this->initAddFileForm(false);
				$this->tpl->setContent($form->getHTML());
break;

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
	 * Init add file form
	 */
	public function initAddFileForm($a_before = true)
	{
		global $lng, $ilCtrl, $ilUser;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		// file
		$fi = new ilFileInputGUI($lng->txt("file"), "file");
		$fi->setRequired(true);
		$form->addItem($fi);
		
		if ($a_before)
		{
			$form->addCommandButton("insertNewItemBefore", $lng->txt("save"));
		}
		else
		{
			$form->addCommandButton("insertNewItemAfter", $lng->txt("save"));
		}
		$form->addCommandButton("cancelAddFile", $lng->txt("cancel"));
		
		$form->setTitle($lng->txt("cont_insert_file_item"));

		$form->setFormAction($ilCtrl->getFormAction($this));
	 
		return $form;
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
	* Insert file from personal workspace
	*/
	function insertFromWorkspace($a_cmd = "insert")
	{
		global $ilTabs, $tree, $ilCtrl, $tpl, $ilUser;

		$this->setTabs($a_cmd);
		$ilTabs->setSubTabActive("cont_file_from_workspace");
		
		// get ws tree
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($ilUser->getId());
		
		// get access handler
		include_once("./Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php");
		$acc_handler = new ilWorkspaceAccessHandler($tree);
		
		// get es explorer
		include_once("./Services/PersonalWorkspace/classes/class.ilWorkspaceExplorer.php");
		$exp = new ilWorkspaceExplorer(ilWorkspaceExplorer::SEL_TYPE_RADIO, '', 
			'filelist_wspexpand', $tree, $acc_handler);
		$exp->setTargetGet('fl_wsp_id');
		$exp->setFiltered(false);
		$exp->removeAllFormItemTypes();
		
		// select link 
		$exp->setTypeClickable("file");
		$ilCtrl->setParameter($this, "subCmd", "selectFile");
		$exp->setCustomLinkTarget($ilCtrl->getLinkTarget($this, $a_cmd));
		
		// filter
		$exp->setFiltered(true);
		$exp->setFilterMode(IL_FM_POSITIVE);
		$exp->addFilter("wsrt");
		$exp->addFilter("wfld");
		$exp->addFilter("file");
	
		// expand link
		$ilCtrl->setParameter($this, "subCmd", "insertFromWorkspace");
		$exp->setParamsGet($ilCtrl->getParameterArray($this, $a_cmd));		

		if($_GET['filelist_wspexpand'] == '')
		{
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET['filelist_wspexpand'];
		}
		$exp->setExpand($expanded);
		$exp->setOutput(0);
		
		$tpl->setContent($exp->getOutput());
	}

	/**
	* insert new file item after another item
	*/
	function insertNewItemAfter($a_file_ref_id = 0)
	{
		global $ilUser;
		
		$res = true;
		if(isset($_GET["fl_wsp_id"]))
		{
			// we need the object id for the instance
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			$tree = new ilWorkspaceTree($ilUser->getId());			
			$node = $tree->getNodeData($_GET["fl_wsp_id"]);		
			
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$this->file_object = new ilObjFile($node["obj_id"], false);
		}
		else if ($a_file_ref_id == 0)
		{
			$res = $this->newFileItem();
		}
		else
		{
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$this->file_object = new ilObjFile($a_file_ref_id);
		}
		if ($res)
		{
			$this->content_obj->newItemAfter($this->file_object->getId(),
				$this->file_object->getTitle(), $this->file_object->getFileType());
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->ctrl->returnToParent($this, "jump".$this->hier_id);
			}
		}
		
		$_GET["subCmd"] = "-";
		$this->newItemAfter();
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
		if ($_GET["subCmd"] == "insertFromWorkspace")
		{
			$_SESSION["cont_file_insert"] = "insertFromWorkspace";
		}
		if (($_GET["subCmd"] == "") && $_SESSION["cont_file_insert"] != "")
		{
			$_GET["subCmd"] = $_SESSION["cont_file_insert"];
		}

		switch ($_GET["subCmd"])
		{
			case "insertFromWorkspace":
				$this->insertFromWorkspace("newItemBefore");
				break;
			
			case "insertFromRepository":
				$this->insertFromRepository("newItemBefore");
				break;
				
			case "selectFile":
				$this->insertNewItemBefore($_GET["file_ref_id"]);
				break;
				
			default:
				$this->setTabs("newItemBefore");
				$ilTabs->setSubTabActive("cont_new_file");
		
				$this->displayValidationError();
				$form = $this->initAddFileForm(true);
				$this->tpl->setContent($form->getHTML());
break;
				
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
		global $ilUser;
		
		$res = true;
		if(isset($_GET["fl_wsp_id"]))
		{
			// we need the object id for the instance
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			$tree = new ilWorkspaceTree($ilUser->getId());			
			$node = $tree->getNodeData($_GET["fl_wsp_id"]);		
			
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$this->file_object = new ilObjFile($node["obj_id"], false);
		}
		else if ($a_file_ref_id == 0)
		{
			$res = $this->newFileItem();
		}
		else
		{
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$this->file_object = new ilObjFile($a_file_ref_id);
		}
		if ($res)
		{
			$this->content_obj->newItemBefore($this->file_object->getId(),
				$this->file_object->getTitle(), $this->file_object->getFileType());
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->ctrl->returnToParent($this, "jump".$this->hier_id);
			}
		}

		$_GET["subCmd"] = "-";
		$this->newItemBefore();
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
		global $ilTabs, $ilCtrl, $ilSetting;

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
			
			if(!$ilSetting->get("disable_personal_workspace") &&
				!$ilSetting->get("disable_wsp_files"))
			{
				$ilCtrl->setParameter($this, "subCmd", "insertFromWorkspace");
				$ilTabs->addSubTabTarget("cont_file_from_workspace",
					$ilCtrl->getLinkTarget($this, $a_cmd), $a_cmd);
				$ilCtrl->setParameter($this, "subCmd", "");
			}
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

	/**
	 * Cancel adding a file
	 */
	function cancelAddFile()
	{
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}
}
?>
