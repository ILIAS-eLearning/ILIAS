<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* GUI class for personal workspace
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
*
* @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjWorkspaceRootFolderGUI, ilObjWorkspaceFolderGUI
* @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjectCopyGUI, ilObjFileGUI, ilObjBlogGUI
* @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjTestVerificationGUI, ilObjExerciseVerificationGUI
* @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjLinkResourceGUI, ilNoteGUI
*
* @ingroup ServicesPersonalWorkspace
*/
class ilPersonalWorkspaceGUI
{
	protected $tree; // [ilTree]
	protected $node_id; // [int]
	
	/**
	 * constructor
	 */
	public function __construct()
	{
		global $ilCtrl, $lng;

		$lng->loadLanguageModule("wsp");

		$this->initTree();

		$ilCtrl->saveParameter($this, "wsp_id");

		$this->node_id = $_REQUEST["wsp_id"];
		if(!$this->node_id)
		{
			$this->node_id = $this->tree->getRootId();
		}
	}
	
	/**
	 * execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl, $lng, $objDefinition, $tpl;

		$ilCtrl->setReturn($this, "render");		
		$cmd = $ilCtrl->getCmd();

		// new type
		if($_REQUEST["new_type"])
		{
			$class_name = $objDefinition->getClassName($_REQUEST["new_type"]);
			$ilCtrl->setCmdClass("ilObj".$class_name."GUI");
		}

		// root node
		$next_class = $ilCtrl->getNextClass();		
		if(!$next_class)
		{
			$node = $this->tree->getNodeData($this->node_id);
			$next_class = "ilObj".$objDefinition->getClassName($node["type"])."GUI";
			$ilCtrl->setCmdClass($next_class);
		}
		// ajax 
		else if($next_class == "ilnotegui")
		{		
			$ilCtrl->saveParameter($this, "notes_ref_id");
			$ilCtrl->saveParameter($this, "notes_sub_id");
			
			$obj_id = $this->tree->lookupObjectId($_GET["notes_ref_id"]);
			
			include_once "Services/Notes/classes/class.ilNoteGUI.php";
			$note_gui = new ilNoteGUI($obj_id, (int)$_GET["notes_sub_id"]);
			$note_gui->enablePrivateNotes(true);
			$note_gui->enablePublicNotes(true);			
			$ilCtrl->forwardCommand($note_gui);		
			exit();
		}
		
		//  if we do this here the object can still change the title and locator
		$this->renderLocator();
		$this->renderTitle();
		
		if(($cmd == "" || $cmd == "render" || $cmd == "view") && !$_REQUEST["new_type"])
		{
			$this->renderToolbar();
		}
		
		// current node
		$class_path = $ilCtrl->lookupClassPath($next_class);
		include_once($class_path);
		$class_name = $ilCtrl->getClassForClasspath($class_path);
		if($_REQUEST["new_type"])
		{
			$gui = new $class_name(null, ilObject2GUI::WORKSPACE_NODE_ID, $this->node_id);
			$gui->setCreationMode();
		}
		else
		{
			$gui = new $class_name($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID, false);
		}
		$ilCtrl->forwardCommand($gui);		
		
		// prepare notes
		include_once("./Services/Notes/classes/class.ilNoteGUI.php");
		ilNoteGUI::initJavascript(
			$ilCtrl->getLinkTargetByClass(array("ilpersonalworkspacegui", "ilnotegui"), "", "", true, false));
				
		$tpl->setHeaderActionMenu($gui, $gui->getNotesSubId());
		
		$tpl->setLocator();
	}

	/**
	 * Init personal tree
	 */
	protected function initTree()
	{
		global $ilUser;

		$user_id = $ilUser->getId();

		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$this->tree = new ilWorkspaceTree($user_id);
		if(!$this->tree->readRootId())
		{
			// create (workspace) root folder
			$root = ilObjectFactory::getClassByType("wsrt");
			$root = new $root(null);
			$root->create();

			$root_id = $this->tree->createReference($root->getId());
			$this->tree->addTree($user_id, $root_id);
			$this->tree->setRootId($root_id);
		}
	}

	protected function renderTitle()
	{
		global $tpl, $lng, $ilTabs, $ilCtrl, $ilUser;
		
		$root = $this->tree->getNodeData($this->node_id);
		if($root["type"] == "wfld" || $root["type"] == "wsrt")
		{
			$title = $lng->txt("wsp_personal_workspace");
			$icon = ilUtil::getImagePath("icon_wsrt_b.gif");
			$tpl->setDescription($lng->txt("wsp_personal_workspace_description"));
		}
		else
		{
			// do not override existing back targets, e.g. public user profile gui
			if(!$ilTabs->back_target)
			{
				$owner = $this->tree->lookupOwner($this->node_id);
				// workspace
				if($owner == $ilUser->getId())
				{
					$parent = $this->tree->getParentNodeData($this->node_id);				
					if($parent["wsp_id"])
					{
						if($parent["type"] == "wsrt")
						{
							$class = "ilobjworkspacerootfoldergui";
						}
						else
						{
							$class = "ilobjworkspacefoldergui";
						}
						$ilCtrl->setParameterByClass($class, "wsp_id", $parent["wsp_id"]);
						$ilTabs->setBackTarget($lng->txt("back"),
							$ilCtrl->getLinkTargetByClass($class, ""));
					}
				}
				// "shared by others"
				else
				{
					$ilCtrl->setParameterByClass("ilobjworkspacerootfoldergui", "wsp_id", "");
					$ilCtrl->setParameterByClass("ilobjworkspacerootfoldergui", "user", $owner);
					$ilTabs->setBackTarget($lng->txt("back"),
						$ilCtrl->getLinkTargetByClass("ilobjworkspacerootfoldergui", "share"));
				}
				
			}
			
			$title = $root["title"];
			$icon = ilObject::_getIcon($root["obj_id"], "big");
		}
		$tpl->setTitle($title);
		$tpl->setTitleIcon($icon, $title);		
	}
	
	/**
	 * Render workspace toolbar (folder navigation, add subobject)
	 */
	protected function renderToolbar()
	{
		global $lng, $ilCtrl, $objDefinition, $tpl, $ilSetting;
		
		$settings_map = array("blog" => "blogs",
			"file" => "files",
			"tstv" => "certificates",
			"excv" => "certificates",
			"webr" => "links");

		$root = $this->tree->getNodeData($this->node_id);
		$subtypes = $objDefinition->getCreatableSubObjects($root["type"], ilObjectDefinition::MODE_WORKSPACE);
		if($subtypes)
		{
			// :TODO: permission checks?
			$subobj = array();
			foreach(array_keys($subtypes) as $type)
			{
				if(isset($settings_map[$type]) && $ilSetting->get("disable_wsp_".$settings_map[$type]))
				{
					continue;
				}
				
				$class = $objDefinition->getClassName($type);
				
				$subobj[] = array("value" => $type,
								  "title" => $lng->txt("wsp_type_".$type),
								  "img" => ilObject::_getIcon("", "tiny", $type),
								  "alt" => $lng->txt("wsp_type_".$type));
			}
			
			$subobj = ilUtil::sortArray($subobj, "title", 1);
			
			$lng->loadLanguageModule("cntr");
			$tpl->setCreationSelector($ilCtrl->getFormAction($this),
				$subobj, "create", $lng->txt("add"));
		}
	}

	/**
	 * Build locator for current node
	 */
	protected function renderLocator()
	{
		global $lng, $ilCtrl, $ilLocator, $tpl, $objDefinition;

		$ilLocator->clearItems();
		
		// we have no path if shared item
		$path = $this->tree->getPathFull($this->node_id);
		if($path)
		{
			foreach($path as $node)
			{
				$obj_class = "ilObj".$objDefinition->getClassName($node["type"])."GUI";

				$ilCtrl->setParameter($this, "wsp_id", $node["wsp_id"]);

				switch($node["type"])
				{			
					case "wsrt":
						$ilLocator->addItem($lng->txt("wsp_personal_workspace"), $ilCtrl->getLinkTargetByClass($obj_class, "render"));
						break;

					case "blog":
					case $objDefinition->isContainer($node["type"]):
						$ilLocator->addItem($node["title"], $ilCtrl->getLinkTargetByClass($obj_class, "render"));
						break;

					default:
						$ilLocator->addItem($node["title"], $ilCtrl->getLinkTargetByClass($obj_class, "edit"));
						break;
				}
			}
		}

		$ilCtrl->setParameter($this, "wsp_id", $this->node_id);
	}
}

?>