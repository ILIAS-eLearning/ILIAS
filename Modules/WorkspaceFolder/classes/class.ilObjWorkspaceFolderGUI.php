<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";

/**
* Class ilObjWorkspaceFolderGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjWorkspaceFolderGUI: 
*
* @extends ilObject2GUI
*/
class ilObjWorkspaceFolderGUI extends ilObject2GUI
{
	function getType()
	{
		return "wfld";
	}

	function setTabs($a_show_settings = true)
	{
		global $lng, $ilUser;

		$this->ctrl->setParameter($this,"wsp_id",$this->node_id);
		
		$this->tabs_gui->addTab("wsp", $lng->txt("wsp_tab_personal"), 
			$this->ctrl->getLinkTarget($this, ""));
		$this->tabs_gui->addTab("share", $lng->txt("wsp_tab_shared"), 
			$this->ctrl->getLinkTarget($this, "share"));
		
		if(stristr($this->ctrl->getCmd(), "share"))
		{
			$this->tabs_gui->activateTab("share");
		}
		else
		{
			$this->tabs_gui->activateTab("wsp");
			
			if($a_show_settings)
			{
				if ($this->checkPermissionBool("read"))
				{
					$this->tabs_gui->addSubTab("content",
						$lng->txt("content"),
						$this->ctrl->getLinkTarget($this, ""));
				}

				if ($this->checkPermissionBool("write"))
				{
					$this->tabs_gui->addSubTab("settings",
						$lng->txt("settings"),
						$this->ctrl->getLinkTarget($this, "edit"));
				}
			}
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
		
		unset($_SESSION['clipboard']['wsp2repo']);

		include_once "Modules/WorkspaceFolder/classes/class.ilObjWorkspaceFolderTableGUI.php";
		$table = new ilObjWorkspaceFolderTableGUI($this, "render", $this->node_id);
		$tpl->setContent($table->getHTML());

		include_once "Modules/WorkspaceFolder/classes/class.ilWorkspaceFolderExplorer.php";
		$exp = new ilWorkspaceFolderExplorer($this->ctrl->getLinkTarget($this), $ilUser->getId());

		if($this->node_id != $exp->getRoot())
		{
			$ilTabs->activateSubTab("content");
		}
		
		$left = "";

		// sub-folders
		if($this->node_id != $exp->getRoot() || $exp->hasFolders($this->node_id))
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
	
	function edit()
	{				
	    parent::edit();		
	  
		$this->tabs_gui->activateTab("wsp");
		$this->tabs_gui->activateSubTab("settings");
	}
	
	function update()
	{
		parent::update();
		
		$this->tabs_gui->activateTab("wsp");
		$this->tabs_gui->activateSubTab("settings");
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
		$_SESSION['clipboard']['cmd'] = 'cut';

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
	 * Copy node preparation
	 *
	 * cioy object(s) out from a container and write the information to clipboard
	 */
	function copy()
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

		// open current position
		// using the explorer session storage directly is basically a hack
		// as we do not use setExpanded() [see below]
		$_SESSION['paste_copy_wspexpand'] = array();
		foreach((array)$this->tree->getPathId($parent_node) as $node_id)
		{
			$_SESSION['paste_copy_wspexpand'][] = $node_id;
		}

		// remember source node
		$_SESSION['clipboard']['source_id'] = $current_node;
		$_SESSION['clipboard']['cmd'] = 'copy';

		return $this->showMoveIntoObjectTree();
	}
		
	/**
	 * Copy node preparation (to repository)
	 *
	 * copy object(s) out from a container and write the information to clipboard
	 */
	function copy_to_repository()
	{
		$_SESSION['clipboard']['wsp2repo'] = true;		
		$this->copy();		
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
		
		$mode = $_SESSION['clipboard']['cmd'];		

		ilUtil::sendInfo($this->lng->txt('msg_'.$mode.'_clipboard'));
		
		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content',
			'tpl.paste_into_multiple_objects.html');
		
		// move/copy in personal workspace
		if(!$_SESSION['clipboard']['wsp2repo'])
		{
			require_once 'Services/PersonalWorkspace/classes/class.ilWorkspaceExplorer.php';
			$exp = new ilWorkspaceExplorer(ilWorkspaceExplorer::SEL_TYPE_RADIO, '', 
				'paste_'.$mode.'_wspexpand', $this->tree, $this->getAccessHandler());
			$exp->setTargetGet('wsp_id');

			if($_GET['paste_'.$mode.'_wspexpand'] == '')
			{
				// not really used as session is already set [see above]
				$expanded = $this->tree->readRootId();
			}
			else
			{
				$expanded = $_GET['paste_'.$mode.'_wspexpand'];
			}
			
		}
		// move/copy to repository
		else
		{
			require_once 'classes/class.ilPasteIntoMultipleItemsExplorer.php';
			$exp = new ilPasteIntoMultipleItemsExplorer(ilPasteIntoMultipleItemsExplorer::SEL_TYPE_RADIO, 
				'', 'paste_'.$mode.'_repexpand');	
			$exp->setTargetGet('ref_id');				
			
			if($_GET['paste_'.$mode.'_repexpand'] == '')
			{
				$expanded = $tree->readRootId();
			}
			else
			{
				$expanded = $_GET['paste_'.$mode.'_repexpand'];
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
		global $objDefinition, $tree, $ilAccess, $ilUser;
		
		$mode = $_SESSION['clipboard']['cmd'];
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
			if($mode == "cut" && $this->tree->isGrandChild($source_node_id, $target_node_id))
			{
				$fail[] = sprintf($this->lng->txt('msg_paste_object_not_in_itself'),
					$source_object->getTitle());
			}
			
			if(!$this->checkPermissionBool('create', '', $source_object->getType(), $target_node_id))
			{
				$fail[] = sprintf($this->lng->txt('msg_no_perm_paste_object_in_folder'),
					$source_object->getTitle(), $target_object->getTitle());
			}
			
			if($mode == "copy" && !$this->checkPermissionBool('copy', '', '', $source_node_id))
			{
				$fail[] = $this->lng->txt('permission_denied');
			}
		}
		else
		{
			if($mode == "copy" && !$ilAccess->checkAccess('copy', '', $source_node_id))
			{
				$fail[] = $this->lng->txt('permission_denied');
			}
			
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
		if($mode == "cut")
		{		
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
		}
		// copy the node
		else if($mode == "copy")
		{
			include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');						
			$copy_id = ilCopyWizardOptions::_allocateCopyId();
			$wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
			$wizard_options->saveOwner($ilUser->getId());
			$wizard_options->saveRoot($source_node_id);						
			$wizard_options->read();
			
			$new_obj = $source_object->cloneObject($target_node_id, $copy_id, !$_SESSION['clipboard']['wsp2repo']);	
			
			// insert into workspace tree
			if($new_obj && !$_SESSION['clipboard']['wsp2repo'])
			{
				$new_obj_node_id = $this->tree->insertObject($target_node_id, $new_obj->getId());
				$this->getAccessHandler()->setPermissions($target_node_id, $new_obj_node_id);
			}
 
			$wizard_options->deleteAll();
		}
		
		unset($_SESSION['clipboard']['cmd']);
		unset($_SESSION['clipboard']['source_id']);
		unset($_SESSION['clipboard']['wsp2repo']);
		
		ilUtil::sendSuccess($this->lng->txt('msg_cut_copied'), true);
		$this->ctrl->setParameter($this, "wsp_id", $source_node_id);
		$this->ctrl->redirect($this);		 
	}
	
	function share()
	{
		global $tpl;
		
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceShareTableGUI.php";
		$tbl = new ilWorkspaceShareTableGUI($this, "share", $this->getAccessHandler());		
		$tpl->setContent($tbl->getHTML());					
	}
	
	function applyShareFilter()
	{
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceShareTableGUI.php";
		$tbl = new ilWorkspaceShareTableGUI($this, "share", $this->getAccessHandler());		
		$tbl->resetOffset();
		$tbl->writeFilterToSession();
		
		$this->share();
	}
	
	function resetShareFilter()
	{
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceShareTableGUI.php";
		$tbl = new ilWorkspaceShareTableGUI($this, "share", $this->getAccessHandler());		
		$tbl->resetOffset();
		$tbl->resetFilter();
		
		$this->share();
	}
}

?>