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

		if ($this->getAccessHandler()->checkAccess('read', '', $this->node_id))
		{
			$this->tabs_gui->addTab("content",
				$lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		}

		if ($this->getAccessHandler()->checkAccess('write', '', $this->node_id))
		{
			$this->tabs_gui->addTab("id_edit",
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
		$tpl->setLeftContent($exp->getOutput());
	}

	/**
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
			if (!$this->getAccessHandler()->checkAccess("delete", "", $node["wsp_id"]))
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

	function showMoveIntoObjectTree()
	{
		global $ilTabs;

		$ilTabs->clearTargets();

		$ilTabs->setBackTarget($this->lng->txt('back'),
			$this->ctrl->getLinkTarget($this));
		
		ilUtil::sendInfo($this->lng->txt('msg_cut_clipboard'));
		
		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content',
			'tpl.paste_into_multiple_objects.html');

		require_once 'Services/PersonalWorkspace/classes/class.ilWorkspaceExplorer.php';
		$exp = new ilWorkspaceExplorer(ilWorkspaceExplorer::SEL_TYPE_RADIO, '', 
			'paste_cut_wspexpand', $this->tree, $this->getAccessHandler());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showMoveIntoObjectTree'));
		$exp->setTargetGet('wsp_id');
		$exp->setPostVar('node');
		$exp->setCheckedItems(array((int)$_POST['node']));

		if($_GET['paste_cut_wspexpand'] == '')
		{
			// not really used as session is already set [see above]
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET['paste_cut_wspexpand'];
		}
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

	function performPasteIntoMultipleObjects()
	{
		global $objDefinition;
		
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
		$target_obj_id = $this->tree->lookupObjectId($target_node_id);
		$target_object = ilObjectFactory::getInstanceByObjId($target_obj_id);


		// sanity checks

		$fail = array();

		if(!$this->getAccessHandler()->checkAccess('create', '', $target_node_id, $source_object->getType()))
		{
			$fail[] = sprintf($this->lng->txt('msg_no_perm_paste_object_in_folder'),
				$source_object->getTitle(), $target_object->getTitle());
		}

		if($source_node_id == $target_node_id)
		{
			$fail[] = sprintf($this->lng->txt('msg_obj_exists_in_folder'),
				$source_object->getTitle(), $target_object->getTitle());
		}

		if($this->tree->isGrandChild($source_node_id, $target_node_id))
		{
			$fail[] = sprintf($this->lng->txt('msg_paste_object_not_in_itself'),
				$source_object->getTitle());
		}
		 

		if(!in_array($source_object->getType(), array_keys($objDefinition->getSubObjects($target_object->getType()))))
		{
			$fail[] = sprintf($this->lng->txt('msg_obj_may_not_contain_objects_of_type'),
					$target_object->getTitle(), $source_object->getType());
		}

		if(sizeof($fail))
		{
			ilUtil::sendFailure(implode("<br />", $fail), true);
			$this->ctrl->redirect($this);
		}


		// move the node
		
		unset($_SESSION['clipboard']['source_id']);
		$this->tree->moveTree($source_node_id, $target_node_id);
	
		ilUtil::sendSuccess($this->lng->txt('msg_cut_copied'), true);
		$this->ctrl->setParameter($this, "wsp_id", $source_node_id);
		$this->ctrl->redirect($this);
	}
}

?>