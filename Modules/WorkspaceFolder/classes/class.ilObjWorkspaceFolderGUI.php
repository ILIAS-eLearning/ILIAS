<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";

/**
* Class ilObjWorkspaceFolderGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjWorkspaceFolderGUI: ilInfoScreenGUI, ilPermissionGUI, 
*
* @extends ilObject2GUI
*/
class ilObjWorkspaceFolderGUI extends ilObject2GUI
{
	function getType()
	{
		return "wfld";
	}

	function setTabs()
	{
		global $lng, $ilUser;

		$this->ctrl->setParameter($this,"wsp_id",$this->node_id);

		if ($this->checkPermissionBool("read"))
		{
			$this->tabs_gui->addTab("content",
				$lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		}

		if ($this->checkPermissionBool("write"))
		{
			$this->tabs_gui->addTab("settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "edit"));
		}
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$this->prepareOutput();
				if(!$cmd)
				{
					$cmd = "render";
				}
				$this->$cmd();
				break;
		}

		return true;
	}

	/**
	* Render root folder
	*/
	function render()
	{
		global $tpl, $ilUser, $ilTabs;

		$ilTabs->activateTab("content");
		
		include_once "Modules/WorkspaceFolder/classes/class.ilObjWorkspaceFolderTableGUI.php";
		$table = new ilObjWorkspaceFolderTableGUI($this, "render", $this->node_id);
		$tpl->setContent($table->getHTML());

		include_once "Modules/WorkspaceFolder/classes/class.ilWorkspaceFolderExplorer.php";
		$exp = new ilWorkspaceFolderExplorer($this->ctrl->getLinkTarget($this), $ilUser->getId());

		$left = "";

		// level up
		$parent = $exp->getParentNode($this->node_id);
		if($parent)
		{
			$this->ctrl->setParameter($this, "wsp_id", $parent);
			$left = "<div class=\"small\" style=\"margin:5px\">[<a href=\"".
				$this->ctrl->getLinkTarget($this)."\">..</a>]</div>";
			$this->ctrl->setParameter($this, "wsp_id", $this->node_id);
		}

		// sub-folders
		if($exp->hasFolders($this->node_id))
		{
			$exp->setTargetGet("wsp_id");
			$exp->setSessionExpandVariable('wspexpand');
			$exp->setExpand($this->node_id);
			$exp->setExpandTarget($this->ctrl->getLinkTarget($this));

			if ($_GET["wspexpand"] != "")
			{
				$exp->setExpand($_GET["wspexpand"]);
			}

			$exp->highlightNode($this->node_id);
			$exp->setOutput(0);
		
			$left .= $exp->getOutput();
		}

		$tpl->setLeftContent($left);
	}

	/**
	 * Move node preparation
	 *
	 * cut object(s) out from a container and write the information to clipboard
	 */
	function cut()
	{
		if (!$_REQUEST["item_ref_id"])
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this);
		}

		$current_node = $_REQUEST["item_ref_id"];
		$parent_node = $this->tree->getParentId($current_node);

		// on cancel or fail we return to parent node
		$this->ctrl->setParameter($this, "wsp_id", $parent_node);

		// check permission
		$no_cut = array();
		foreach ($this->tree->getSubTree($this->tree->getNodeData($current_node)) as $node)
		{
			if (!$this->checkPermissionBool("delete", "", "", $node["wsp_id"]))
			{
				$obj = ilObjectFactory::getInstanceByObjId($node["obj_id"]);
				$no_cut[$node["wsp_id"]] = $obj->getTitle();
				unset($obj);
			}
		}
		if (count($no_cut))
		{
			ilUtil::sendFailure($this->lng->txt("msg_no_perm_cut")." ".implode(',', $no_cut), true);
			$this->ctrl->redirect($this);
		}

		// open current position
		// using the explorer session storage directly is basically a hack
		// as we do not use setExpanded() [see below]
		$_SESSION['paste_cut_wspexpand'] = array();
		foreach((array)$this->tree->getPathId($parent_node) as $node_id)
		{
			$_SESSION['paste_cut_wspexpand'][] = $node_id;
		}

		// remember source node
		$_SESSION['clipboard']['source_id'] = $current_node;

		return $this->showMoveIntoObjectTree();
	}
		
	/**
	 * Move node preparation (to repository)
	 *
	 * cut object(s) out from a container and write the information to clipboard
	 */
	function cut_for_repository()
	{
		$_SESSION['clipboard']['wsp2repo'] = true;		
		$this->cut();		
	}

	/**
	 * Move node: select target (via explorer)
	 */
	function showMoveIntoObjectTree()
	{
		global $ilTabs, $tree;

		$ilTabs->clearTargets();

		$ilTabs->setBackTarget($this->lng->txt('back'),
			$this->ctrl->getLinkTarget($this));
		
		ilUtil::sendInfo($this->lng->txt('msg_cut_clipboard'));
		
		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content',
			'tpl.paste_into_multiple_objects.html');
		
		// move in personal workspace
		if(!$_SESSION['clipboard']['wsp2repo'])
		{
			require_once 'Services/PersonalWorkspace/classes/class.ilWorkspaceExplorer.php';
			$exp = new ilWorkspaceExplorer(ilWorkspaceExplorer::SEL_TYPE_RADIO, '', 
				'paste_cut_wspexpand', $this->tree, $this->getAccessHandler());
			$exp->setTargetGet('wsp_id');

			if($_GET['paste_cut_wspexpand'] == '')
			{
				// not really used as session is already set [see above]
				$expanded = $this->tree->readRootId();
			}
			else
			{
				$expanded = $_GET['paste_cut_wspexpand'];
			}
			
		}
		// move to repository
		else
		{
			require_once 'classes/class.ilPasteIntoMultipleItemsExplorer.php';
			$exp = new ilPasteIntoMultipleItemsExplorer(ilPasteIntoMultipleItemsExplorer::SEL_TYPE_RADIO, 
				'', 'paste_cut_repexpand');	
			$exp->setTargetGet('ref_id');				
			
			if($_GET['paste_cut_repexpand'] == '')
			{
				$expanded = $tree->readRootId();
			}
			else
			{
				$expanded = $_GET['paste_cut_repexpand'];
			}
		}
		
		$exp->setCheckedItems(array((int)$_POST['node']));
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showMoveIntoObjectTree'));
		$exp->setPostVar('node');
		$exp->setExpand($expanded);
		$exp->setOutput(0);
					
		$this->tpl->setVariable('OBJECT_TREE', $exp->getOutput());
		unset($exp);

		$this->tpl->setVariable('FORM_TARGET', '_top');
		$this->tpl->setVariable('FORM_ACTION',
			$this->ctrl->getFormAction($this, 'performPasteIntoMultipleObjects'));

		$this->tpl->setVariable('CMD_SUBMIT', 'performPasteIntoMultipleObjects');
		$this->tpl->setVariable('TXT_SUBMIT', $this->lng->txt('paste'));
	}

	/**
	 * Move node: target has been selected, execute
	 */
	function performPasteIntoMultipleObjects()
	{
		global $objDefinition, $tree, $ilAccess;
		
		$source_node_id = $_SESSION['clipboard']['source_id'];
		$target_node_id = $_REQUEST['node'];

		if(!$source_node_id)
		{
			ilUtil::sendFailure($this->lng->txt('select_at_least_one_object'), true);
			$this->ctrl->redirect($this);
		}
		if(!$target_node_id)
		{
			ilUtil::sendFailure($this->lng->txt('select_at_least_one_object'), true);
			$this->ctrl->redirect($this, "showMoveIntoObjectTree");
		}

		// object instances
		$source_obj_id = $this->tree->lookupObjectId($source_node_id);
		$source_parent_id = $this->tree->getParentId($source_node_id);
		$source_object = ilObjectFactory::getInstanceByObjId($source_obj_id);
		
		if(!$_SESSION['clipboard']['wsp2repo'])
		{		
			$target_obj_id = $this->tree->lookupObjectId($target_node_id);
		}
		else
		{
			$target_obj_id = ilObject::_lookupObjId($target_node_id);
		}
		$target_object = ilObjectFactory::getInstanceByObjId($target_obj_id);


		// sanity checks

		$fail = array();

		if($source_node_id == $target_node_id)
		{
			$fail[] = sprintf($this->lng->txt('msg_obj_exists_in_folder'),
				$source_object->getTitle(), $target_object->getTitle());
		}

		if(!in_array($source_object->getType(), array_keys($objDefinition->getSubObjects($target_object->getType()))))
		{
			$fail[] = sprintf($this->lng->txt('msg_obj_may_not_contain_objects_of_type'),
					$target_object->getTitle(), $source_object->getType());
		}

		if(!$_SESSION['clipboard']['wsp2repo'])
		{
			if($this->tree->isGrandChild($source_node_id, $target_node_id))
			{
				$fail[] = sprintf($this->lng->txt('msg_paste_object_not_in_itself'),
					$source_object->getTitle());
			}
			
			if(!$this->checkPermissionBool('create', '', $source_object->getType(), $target_node_id))
			{
				$fail[] = sprintf($this->lng->txt('msg_no_perm_paste_object_in_folder'),
					$source_object->getTitle(), $target_object->getTitle());
			}
		}
		else
		{
			if(!$ilAccess->checkAccess('create', '', $target_node_id, $source_object->getType()))
			{
				$fail[] = sprintf($this->lng->txt('msg_no_perm_paste_object_in_folder'),
					$source_object->getTitle(), $target_object->getTitle());
			}
		}

		if(sizeof($fail))
		{
			ilUtil::sendFailure(implode("<br />", $fail), true);
			$this->ctrl->redirect($this);
		}


		// move the node
		
		if(!$_SESSION['clipboard']['wsp2repo'])
		{
			$this->tree->moveTree($source_node_id, $target_node_id);
		}
		else
		{
			$parent_id = $this->tree->getParentId($source_node_id);
			
			// remove from personal workspace
			$this->getAccessHandler()->removePermission($source_node_id);
			$this->tree->deleteReference($source_node_id);
			$source_node = $this->tree->getNodeData($source_node_id);
			$this->tree->deleteTree($source_node);			
							
			// add to repository
			$source_object->createReference();
			$source_object->putInTree($target_node_id);
			$source_object->setPermissions($target_node_id);
			
			$source_node_id = $parent_id;
		}
	
		unset($_SESSION['clipboard']['source_id']);
		unset($_SESSION['clipboard']['wsp2repo']);
		
		ilUtil::sendSuccess($this->lng->txt('msg_cut_copied'), true);
		$this->ctrl->setParameter($this, "wsp_id", $source_node_id);
		$this->ctrl->redirect($this);		 
	}
}

?>