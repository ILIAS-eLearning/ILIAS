<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";

/**
* Class ilObjWorkspaceFolderGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjWorkspaceFolderGUI: ilCommonActionDispatcherGUI, ilObjectOwnershipManagementGUI
*
* @extends ilObject2GUI
*/
class ilObjWorkspaceFolderGUI extends ilObject2GUI
{
	/**
	 * @var ilHelpGUI
	 */
	protected $help;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var ilWorkspaceFolderUserSettings
	 */
	protected $user_folder_settings;

	/**
	 * @var int
	 */
	protected $requested_sortation;

    /**
     * @var ilLogger
     */
	protected $wsp_log;

	/**
	 * Constructor
	 */
	function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		global $DIC;
		parent::__construct($a_id, $a_id_type, $a_parent_node_id);

		$this->lng = $DIC->language();
		$this->help = $DIC["ilHelp"];
		$this->tpl = $DIC["tpl"];
		$this->user = $DIC->user();
		$this->tabs = $DIC->tabs();
		$this->ctrl = $DIC->ctrl();
		$this->ui = $DIC->ui();

        $this->wsp_log = ilLoggerFactory::getLogger("pwsp");

		$this->user_folder_settings = new ilWorkspaceFolderUserSettings($this->user->getId(),
			new ilWorkspaceFolderUserSettingsRepository($this->user->getId()));

		$this->requested_sortation = (int) $_GET["sortation"];

		$this->lng->loadLanguageModule("cntr");
	}

	function getType()
	{
		return "wfld";
	}

	function setTabs($a_show_settings = true)
	{
		$lng = $this->lng;
		$ilHelp = $this->help;

		$ilHelp->setScreenIdComponent("wfld");
		
		$this->ctrl->setParameter($this,"wsp_id",$this->node_id);

		$this->tabs_gui->addTab("wsp", $lng->txt("wsp_tab_personal"), 
			$this->ctrl->getLinkTarget($this, ""));
		
		$this->ctrl->setParameterByClass("ilObjWorkspaceRootFolderGUI", "wsp_id", 
			$this->getAccessHandler()->getTree()->getRootId());
		
		$this->tabs_gui->addTab("share", $lng->txt("wsp_tab_shared"), 
			$this->ctrl->getLinkTargetByClass("ilObjWorkspaceRootFolderGUI", "shareFilter"));
		
		$this->tabs_gui->addTab("ownership", $lng->txt("wsp_tab_ownership"), 
			$this->ctrl->getLinkTargetByClass(array("ilObjWorkspaceRootFolderGUI", "ilObjectOwnershipManagementGUI"), "listObjects"));		
		
		if(!$this->ctrl->getNextClass($this))
		{

			if(stristr($this->ctrl->getCmd(), "share"))
			{
				$this->tabs_gui->activateTab("share");
			}		
			else
			{
				$this->tabs_gui->activateTab("wsp");
				$this->addContentSubTabs($a_show_settings);
			}
		}
	}

	/**
	 * @return bool
	 */
	function isActiveAdministrationPanel()
	{
		return (bool) $_SESSION["il_wsp_admin_panel"];
	}

	/**
	 * @param bool $active
	 * @return bool
	 */
	function setAdministrationPanel(bool $active)
	{
		return $_SESSION["il_wsp_admin_panel"] = $active;
	}

	/**
	 * Add content subtabs
	 */
	protected function addContentSubTabs($a_show_settings)
	{
		$tabs = $this->tabs;
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		if ($this->checkPermissionBool("read"))
		{
			$tabs->addSubTab("content", $lng->txt("view"), $ctrl->getLinkTarget($this, "disableAdminPanel"));
			$tabs->addSubTab("manage", $lng->txt("cntr_manage"), $ctrl->getLinkTarget($this, "enableAdminPanel"));
		}

		if ($this->checkPermissionBool("write") && $a_show_settings)
		{
			$this->tabs_gui->addSubTab("settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "edit"));
		}

		if ($this->isActiveAdministrationPanel()) {
			$tabs->activateSubTab("manage");
		} else {
			$tabs->activateSubTab("content");
		}
	}

	/**
	 * Enable admin panel
	 */
	protected function enableAdminPanel()
	{
		$this->setAdministrationPanel(true);
		$this->ctrl->redirect($this, "");
	}

	/**
	 * Disable admin panel
	 */
	protected function disableAdminPanel()
	{
		$this->setAdministrationPanel(false);
		$this->ctrl->redirect($this, "");
	}

	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilobjectownershipmanagementgui":
				$this->prepareOutput();
				$this->tabs_gui->activateTab("ownership");
				include_once("Services/Object/classes/class.ilObjectOwnershipManagementGUI.php");
				$gui = new ilObjectOwnershipManagementGUI();
				$this->ctrl->forwardCommand($gui);
				break;
			
			default:
				$this->prepareOutput();						
				if($this->type != "wsrt")
				{
					$this->addHeaderAction();
				}				
				if(!$cmd)
				{
					$cmd = "render";
				}
				$this->$cmd();
				break;
		}
		
		return true;
	}
	
	protected function initCreationForms($a_new_type)
	{
		$forms = array(
			self::CFORM_NEW => $this->initCreateForm($a_new_type)
			);

		return $forms;
	}

	/**
	* Render folder
	*/
	function render()
	{
		$tpl = $this->tpl;
		$ilUser = $this->user;
		$ilTabs = $this->tabs;
		$ilCtrl = $this->ctrl;

		//$this->addContentSubTabs();
		$this->showAdministrationPanel();

		unset($_SESSION['clipboard']['wsp2repo']);
		
		// add new item
		include_once "Services/Object/classes/class.ilObjectAddNewItemGUI.php";
		$gui = new ilObjectAddNewItemGUI($this->node_id);
		$gui->setMode(ilObjectDefinition::MODE_WORKSPACE);
		$gui->setCreationUrl($ilCtrl->getLinkTarget($this, "create"));
		$gui->render();
	
		include_once "Services/Object/classes/class.ilObjectListGUI.php";
		ilObjectListGUI::prepareJsLinks("",
			$this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "ilnotegui"), "", "", true, false), 
			$this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "iltagginggui"), "", "", true, false));
		
		include_once "Modules/WorkspaceFolder/classes/class.ilWorkspaceContentGUI.php";
		$gui = new ilWorkspaceContentGUI($this,
			$this->node_id,
			$this->isActiveAdministrationPanel(),
			$this->getAccessHandler(), $this->ui, $this->lng, $this->user, $this->objDefinition, $this->ctrl,
			$this->user_folder_settings);
		$tpl->setContent($gui->render());

		include_once("./Services/PersonalWorkspace/classes/class.ilWorkspaceExplorerGUI.php");
		$exp = new ilWorkspaceExplorerGUI($ilUser->getId(), $this, "render", $this, "", "wsp_id");
		$exp->setTypeWhiteList(array("wsrt", "wfld"));
		$exp->setSelectableTypes(array("wsrt", "wfld"));
		$exp->setLinkToNodeClass(true);
		$exp->setActivateHighlighting(true);
		if ($exp->handleCommand())
		{
			return;
		}
		$left = $exp->getHTML();

		$tpl->setLeftNavContent($left);
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
	 * Get requested item ids
	 *
	 * @return array
	 */
	protected function getRequestItemIds()
	{
		if (is_string($_REQUEST["item_ref_id"]) && $_REQUEST["item_ref_id"] != "")
		{
			return [(int) $_REQUEST["item_ref_id"]];
		}
		else if (is_array($_POST["id"]))
		{
			return array_map(function ($i) {
				return (int) $i;
			}, $_POST["id"]);
		}
		return [];
	}
	
	
	
	/**
	 * Move node preparation
	 *
	 * cut object(s) out from a container and write the information to clipboard
	 */
	function cut()
	{
		$item_ids = $this->getRequestItemIds();
		if (count($item_ids) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this);
		}

		//$current_node = $_REQUEST["item_ref_id"];
		//$parent_node = $this->tree->getParentId($current_node);

		// on cancel or fail we return to parent node
		//$this->ctrl->setParameter($this, "wsp_id", $parent_node);

		// check permission
		$no_cut = array();
		$repo_switch_allowed = true;
		foreach ($item_ids as $item_id)
		{
			foreach ($this->tree->getSubTree($this->tree->getNodeData($item_id)) as $node)
			{
			    if (ilObject::_lookupType($node["obj_id"]) != "file") {
                    $repo_switch_allowed = false;
                }
				if (!$this->checkPermissionBool("delete", "", "", $node["wsp_id"]))
				{
					$obj = ilObjectFactory::getInstanceByObjId($node["obj_id"]);
					$no_cut[$node["wsp_id"]] = $obj->getTitle();
					unset($obj);
				}
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
		foreach((array)$this->tree->getPathId($this->node_id) as $node_id)
		{
			$_SESSION['paste_cut_wspexpand'][] = $node_id;
		}

		// remember source node
		$_SESSION['clipboard']['source_ids'] = $item_ids;
		$_SESSION['clipboard']['cmd'] = 'cut';

		return $this->showMoveIntoObjectTree($repo_switch_allowed);
	}
		
	/**
	 * Move node preparation (to workspace)
	 *
	 * cut object(s) out from a container and write the information to clipboard
	 */
	function cut_for_repository()
	{
		$_SESSION['clipboard']['wsp2repo'] = true;		
		$this->cut();		
	}
	
	/**
	 * Move node preparation (to workspace)
	 *
	 * cut object(s) out from a container and write the information to clipboard
	 */
	function cut_for_workspace()
	{
		$_SESSION['clipboard']['wsp2repo'] = false;
		$this->cut();
	}

	/**
	 * Copy node preparation
	 *
	 * cioy object(s) out from a container and write the information to clipboard
	 */
	function copy()
	{
		$ilUser = $this->user;

		$item_ids = $this->getRequestItemIds();
		if (count($item_ids) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this);
		}

		// on cancel or fail we return to parent node
		$this->ctrl->setParameter($this, "wsp_id", $this->node_id);

        $repo_switch_allowed = true;
		foreach ($item_ids as $item_id)
		{
		    $node = $this->tree->getNodeData($item_id);
            if (ilObject::_lookupType($node["obj_id"]) != "file") {
                $repo_switch_allowed = false;
            }
            $current_node = $item_id;
			$owner = $this->tree->lookupOwner($current_node);
			if ($owner == $ilUser->getId())
			{
				// open current position
				// using the explorer session storage directly is basically a hack
				// as we do not use setExpanded() [see below]
				$_SESSION['paste_copy_wspexpand'] = array();
				foreach ((array)$this->tree->getPathId($item_id) as $node_id)
				{
					$_SESSION['paste_copy_wspexpand'][] = $node_id;
				}
			} else
			{
				// see copyShared()
				ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
				$this->ctrl->redirect($this);
			}
		}

		// remember source node
		$_SESSION['clipboard']['source_ids'] = $item_ids;
		$_SESSION['clipboard']['cmd'] = 'copy';

		return $this->showMoveIntoObjectTree($repo_switch_allowed);
	}
	
	function copyShared()
	{				
		if (!$_REQUEST["item_ref_id"])
		{
			$this->ctrl->redirect($this, "share");
		}
		
		$current_node = $_REQUEST["item_ref_id"];
		$handler = $this->getAccessHandler();
		
		// see ilSharedRessourceGUI::hasAccess()		
		if($handler->checkAccess("read", "", $current_node))
		{
			// remember source node
			$_SESSION['clipboard']['source_id'] = $current_node;
			$_SESSION['clipboard']['cmd'] = 'copy';
			$_SESSION['clipboard']['shared'] = true;

			return $this->showMoveIntoObjectTree();
		}
		else
		{
			$perms = $handler->getPermissions($current_node);
			if(in_array(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, $perms))
			{
				return $this->passwordForm($current_node);
			}
		}
		
		ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
		$this->ctrl->redirect($this, "share");
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
	 * Copy node preparation (to repository)
	 *
	 * copy object(s) out from a container and write the information to clipboard
	 */
	function copy_to_workspace()
	{
		$_SESSION['clipboard']['wsp2repo'] = false;
		$this->copy();
	}

	/**
	 * Move node: select target (via explorer)
	 */
	function showMoveIntoObjectTree($repo_switch_allowed = false)
	{
		$ilTabs = $this->tabs;
		$tree = $this->tree;
		
		$ilTabs->clearTargets();

		if(!$_SESSION['clipboard']['shared'])
		{
			$ilTabs->setBackTarget($this->lng->txt('back'),
				$this->ctrl->getLinkTarget($this));
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt('back'),
				$this->ctrl->getLinkTarget($this, 'share'));
		}
		
		$mode = $_SESSION['clipboard']['cmd'];		

		ilUtil::sendInfo($this->lng->txt('msg_'.$mode.'_clipboard'));
		
		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content',
			'tpl.paste_into_multiple_objects.html', "Services/Object");
		
		// move/copy in personal workspace
		if(!$_SESSION['clipboard']['wsp2repo'])
		{
			include_once("./Services/PersonalWorkspace/classes/class.ilWorkspaceExplorerGUI.php");
			$exp = new ilWorkspaceExplorerGUI($this->user->getId(), $this, "showMoveIntoObjectTree", $this, "");
			$exp->setTypeWhiteList(array("wsrt", "wfld"));
			$exp->setSelectableTypes(array("wsrt", "wfld"));
			$exp->setSelectMode("node", false);
			if ($exp->handleCommand())
			{
				return;
			}
			$this->tpl->setVariable('OBJECT_TREE', $exp->getHTML());

			// switch to repo?
            if ($repo_switch_allowed) {
                $switch_cmd = ($mode == "cut")
                    ? "cut_for_repository"
                    : "copy_to_repository";
                $this->tpl->setCurrentBlock("switch_button");
                $this->tpl->setVariable('CMD_SWITCH', $switch_cmd);
                $this->tpl->setVariable('TXT_SWITCH', $this->lng->txt('wsp_switch_to_repo_tree'));
                $this->tpl->parseCurrentBlock();

                foreach ($this->getRequestItemIds() as $id) {
                    $this->tpl->setCurrentBlock("hidden");
                    $this->tpl->setVariable('VALUE', $id);
                    $this->tpl->parseCurrentBlock();
                }
            }
		}
		// move/copy to repository
		else
		{
			require_once './Services/Object/classes/class.ilPasteIntoMultipleItemsExplorer.php';
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
			$exp->setCheckedItems(array((int)$_POST['node']));
			$exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showMoveIntoObjectTree'));
			$exp->setPostVar('node');
			$exp->setExpand($expanded);
			$exp->setOutput(0);
			$this->tpl->setVariable('OBJECT_TREE', $exp->getOutput());

            if (in_array($mode, ["copy", "cut"])) {
                $switch_cmd = ($mode == "cut")
                    ? "cut_for_workspace"
                    : "copy_to_workspace";
                $this->tpl->setCurrentBlock("switch_button");
                $this->tpl->setVariable('CMD_SWITCH', $switch_cmd);
                $this->tpl->setVariable('TXT_SWITCH', $this->lng->txt('wsp_switch_to_wsp_tree'));
                $this->tpl->parseCurrentBlock();

                foreach ($this->getRequestItemIds() as $id) {
                    $this->tpl->setCurrentBlock("hidden");
                    $this->tpl->setVariable('VALUE', $id);
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
		

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
		$ilUser = $this->user;
		
		$mode = $_SESSION['clipboard']['cmd'];
		$source_node_ids = $_SESSION['clipboard']['source_ids'];
		$target_node_id = $_REQUEST['node'];

		if(!is_array($source_node_ids) || count($source_node_ids) == 0)
		{
			ilUtil::sendFailure($this->lng->txt('select_at_least_one_object'), true);
			$this->ctrl->redirect($this);
		}
		if(!$target_node_id)
		{
			ilUtil::sendFailure($this->lng->txt('select_at_least_one_object'), true);
			$this->ctrl->redirect($this, "showMoveIntoObjectTree");
		}

		if(!$_SESSION['clipboard']['wsp2repo'])
		{
			$target_obj_id = $this->tree->lookupObjectId($target_node_id);
		}
		else
		{
			$target_obj_id = ilObject::_lookupObjId($target_node_id);
		}
		$target_object = ilObjectFactory::getInstanceByObjId($target_obj_id);

		$fail = array();
		foreach ($source_node_ids as $source_node_id)
		{
			// object instances
			$source_obj_id = $this->tree->lookupObjectId($source_node_id);
			$source_object = ilObjectFactory::getInstanceByObjId($source_obj_id);


			// sanity checks
			if ($source_node_id == $target_node_id)
			{
				$fail[] = sprintf($this->lng->txt('msg_obj_exists_in_folder'),
					$source_object->getTitle(), $target_object->getTitle());
			}

			if (!in_array($source_object->getType(), array_keys($target_object->getPossibleSubObjects())))
			{
				$fail[] = sprintf($this->lng->txt('msg_obj_may_not_contain_objects_of_type'),
					$target_object->getTitle(), $source_object->getType());
			}

			// if object is shared permission to copy has been checked above
			$owner = $this->tree->lookupOwner($source_node_id);
			if ($mode == "copy" && $ilUser->getId() == $owner && !$this->checkPermissionBool('copy', '', '', $source_node_id))
			{
				$fail[] = $this->lng->txt('permission_denied');
			}

			if (!$_SESSION['clipboard']['wsp2repo'])
			{
				if ($mode == "cut" && $this->tree->isGrandChild($source_node_id, $target_node_id))
				{
					$fail[] = sprintf($this->lng->txt('msg_paste_object_not_in_itself'),
						$source_object->getTitle());
				}
			}

			if ($_SESSION['clipboard']['wsp2repo'] == true)        // see #22959
			{
				global $ilAccess;
				if (!$ilAccess->checkAccess("create", "", $target_node_id, $source_object->getType()))
				{
					$fail[] = sprintf($this->lng->txt('msg_no_perm_paste_object_in_folder'),
						$source_object->getTitle(), $target_object->getTitle());
				}
			} else
			{
				if (!$this->checkPermissionBool('create', '', $source_object->getType(), $target_node_id))
				{
					$fail[] = sprintf($this->lng->txt('msg_no_perm_paste_object_in_folder'),
						$source_object->getTitle(), $target_object->getTitle());
				}
			}
		}

		if(sizeof($fail))
		{
			ilUtil::sendFailure(implode("<br />", $fail), true);
			$this->ctrl->redirect($this);
		}


		foreach ($source_node_ids as $source_node_id)
		{
		    $node_data = $this->tree->getNodeData($source_node_id);
            $source_object = ilObjectFactory::getInstanceByObjId($node_data["obj_id"]);

			// move the node
			if ($mode == "cut")
			{
				if (!$_SESSION['clipboard']['wsp2repo'])
				{
					$this->tree->moveTree($source_node_id, $target_node_id);
				} else
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
			} // copy the node
			else if ($mode == "copy")
			{
				include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
				$copy_id = ilCopyWizardOptions::_allocateCopyId();
				$wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
				$this->wsp_log->debug("Copy ID: ".$copy_id.", Source Node: ".$source_node_id
                    .", source object: ".$source_object->getId());
				if (!$_SESSION['clipboard']['wsp2repo'])
				{
					$wizard_options->disableTreeCopy();
				}
				$wizard_options->saveOwner($ilUser->getId());
				$wizard_options->saveRoot($source_node_id);
				$wizard_options->read();

				$new_obj = $source_object->cloneObject($target_node_id, $copy_id);
				// insert into workspace tree
				if ($new_obj && !$_SESSION['clipboard']['wsp2repo'])
				{
                    $this->wsp_log->debug("New Obj ID: ".$new_obj->getId());
					$new_obj_node_id = $this->tree->insertObject($target_node_id, $new_obj->getId());
					$this->getAccessHandler()->setPermissions($target_node_id, $new_obj_node_id);
				}

				$wizard_options->deleteAll();
			}
		}
		
		// redirect to target if not repository
		if(!$_SESSION['clipboard']['wsp2repo'])
		{
			$redirect_node = $target_node_id;
		}
		else
		{
			// reload current folder
			$redirect_node = $this->node_id;
		}
		
		unset($_SESSION['clipboard']['cmd']);
		unset($_SESSION['clipboard']['source_ids']);
		unset($_SESSION['clipboard']['wsp2repo']);
		unset($_SESSION['clipboard']['shared']);
		
		// #17746
		if($mode == 'cut')
		{
			ilUtil::sendSuccess($this->lng->txt('msg_cut_copied'), true);
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt('msg_cloned'), true);
		}
		
		$this->ctrl->setParameter($this, "wsp_id", $redirect_node);
		$this->ctrl->redirect($this);		 
	}
	
	function shareFilter()
	{
		$this->share(false);
	}
	
	function share($a_load_data = true)
	{
		$tpl = $this->tpl;
	
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceShareTableGUI.php";
		$tbl = new ilWorkspaceShareTableGUI($this, "share", $this->getAccessHandler(), $this->node_id, $a_load_data);		
		$tpl->setContent($tbl->getHTML());
	}
	
	function applyShareFilter()
	{
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceShareTableGUI.php";
		$tbl = new ilWorkspaceShareTableGUI($this, "share", $this->getAccessHandler(), $this->node_id);		
		$tbl->resetOffset();
		$tbl->writeFilterToSession();
		
		$this->share();
	}
	
	function resetShareFilter()
	{
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceShareTableGUI.php";
		$tbl = new ilWorkspaceShareTableGUI($this, "share", $this->getAccessHandler(), $this->node_id);		
		$tbl->resetOffset();
		$tbl->resetFilter();
		
		$this->shareFilter();
	}
	
	protected function passwordForm($a_node_id, $form = null)
	{
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ilTabs = $this->tabs;
		
		$tpl->setTitle($lng->txt("wsp_password_protected_resource"));
		$tpl->setDescription($lng->txt("wsp_password_protected_resource_info"));
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$this->ctrl->getLinkTarget($this, "share"));
		
		if(!$form)
		{							
			$form = $this->initPasswordForm($a_node_id);
		}
	
		$tpl->setContent($form->getHTML());		
	}
	
	protected function initPasswordForm($a_node_id)
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
						
		$this->ctrl->setParameter($this, "item_ref_id", $a_node_id);				
		
		$object_data = $this->getAccessHandler()->getObjectDataFromNode($a_node_id);
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "checkPassword"));
		$form->setTitle($lng->txt("wsp_password_for").": ".$object_data["title"]);
		
		$password = new ilPasswordInputGUI($lng->txt("password"), "password");
		$password->setRetype(false);
		$password->setRequired(true);
		$password->setSkipSyntaxCheck(true);
		$form->addItem($password);
		
		$form->addCommandButton("checkPassword", $lng->txt("submit"));
		$form->addCommandButton("share", $lng->txt("cancel"));
		
		return $form;
	}
	
	protected function checkPassword()
	{
		$lng = $this->lng;
		
		$node_id = $_REQUEST["item_ref_id"];
		if(!$node_id)
		{
			$this->ctrl->redirect($this, "share");
		}
		 
		$form = $this->initPasswordForm($node_id);
		if($form->checkInput())
		{							
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
			$password = ilWorkspaceAccessHandler::getSharedNodePassword($node_id);
			$input = md5($form->getInput("password"));		
			if($input == $password)
			{						
				// we save password and start over
				ilWorkspaceAccessHandler::keepSharedSessionPassword($node_id, $input);		
				
				$this->ctrl->setParameter($this, "item_ref_id", $node_id);
				$this->ctrl->redirect($this, "copyShared");	
			}
			else
			{
				$item = $form->getItemByPostVar("password");
				$item->setAlert($lng->txt("wsp_invalid_password"));
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
			}						
		}		
		
		$form->setValuesByPost();
		$this->passwordForm($node_id, $form);
	}
	
	/**
	 * Deep link
	 * 
	 * @param string $a_target 
	 */
	public static function _goto($a_target)
	{
		$id = explode("_", $a_target);
		
		$_GET["baseClass"] = "ilsharedresourceGUI";	
		$_GET["wsp_id"] = $id[0];		
		include("ilias.php");
		exit;
	}

	/**
	 * Entry point for awareness tool
	 */
	function listSharedResourcesOfOtherUser()
	{
		$ilCtrl = $this->ctrl;

		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceShareTableGUI.php";
		$tbl = new ilWorkspaceShareTableGUI($this, "share", $this->getAccessHandler(), $this->node_id);
		$tbl->resetOffset();
		$tbl->resetFilter();
		$_POST["user"] = $_GET["user"];
		$tbl->writeFilterToSession();
		$this->share();
	}

	/**
	 * Display delete confirmation form (workspace specific)
	 *
	 * This should probably be moved elsewhere as done with RepUtil
	 */
	protected function deleteConfirmation()
	{
		global $DIC;

		$tpl = $DIC["tpl"];
		$lng = $DIC["lng"];

		$item_ids = $this->getRequestItemIds();

		if (count($item_ids) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "");
		}

		// on cancel or fail we return to parent node
		//$parent_node = $this->tree->getParentId($node_id);
		//$this->ctrl->setParameter($this, "wsp_id", $parent_node);

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($lng->txt("info_delete_sure")."<br/>".
			$lng->txt("info_delete_warning_no_trash"));

		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($lng->txt("cancel"), "cancelDeletion");
		$cgui->setConfirm($lng->txt("confirm"), "confirmedDelete");

		foreach ($item_ids as $node_id)
		{
			$children = $this->tree->getSubTree($this->tree->getNodeData($node_id));
			foreach($children as $child)
			{
				$node_id = $child["wsp_id"];
				$obj_id = $this->tree->lookupObjectId($node_id);
				$type = ilObject::_lookupType($obj_id);
				$title = call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'), $obj_id);

				// if anything fails, abort the whole process
				if(!$this->checkPermissionBool("delete", "", "", $node_id))
				{
					ilUtil::sendFailure($lng->txt("msg_no_perm_delete")." ".$title, true);
					$this->ctrl->redirect($this);
				}

				$cgui->addItem("id[]", $node_id, $title,
					ilObject::_getIcon($obj_id, "small", $type),
					$lng->txt("icon")." ".$lng->txt("obj_".$type));
			}
		}

		$tpl->setContent($cgui->getHTML());
	}

	/**
	 *
	 *
	 * @param
	 * @return
	 */
	public function cancelDeletion()
	{
		unset($_SESSION['clipboard']['cmd']);
		unset($_SESSION['clipboard']['source_ids']);
		unset($_SESSION['clipboard']['wsp2repo']);
		unset($_SESSION['clipboard']['shared']);
		parent::cancelDelete();
	}


	//
	// admin panel
	//

	/**
	 * show administration panel
	 */
	function showAdministrationPanel()
	{
		global $DIC;

		$ilAccess = $this->access;
		$lng = $this->lng;

		$main_tpl = $DIC->ui()->mainTemplate();

		$lng->loadLanguageModule('cntr');

		if ($_SESSION["wsp_clipboard"])
		{
			// #11545
			$main_tpl->setPageFormAction($this->ctrl->getFormAction($this));

			include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
			$toolbar = new ilToolbarGUI();
			$this->ctrl->setParameter($this, "type", "");
			$this->ctrl->setParameter($this, "item_ref_id", "");

			$toolbar->addFormButton(
				$this->lng->txt('paste_clipboard_items'),
				'paste'
			);

			$toolbar->addFormButton(
				$this->lng->txt('clear_clipboard'),
				'clear'
			);

			$main_tpl->addAdminPanelToolbar($toolbar, true, false);
		}
		else if ($this->isActiveAdministrationPanel())
		{
			// #11545
			$main_tpl->setPageFormAction($this->ctrl->getFormAction($this));

			include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
			$toolbar = new ilToolbarGUI();
			$this->ctrl->setParameter($this, "type", "");
			$this->ctrl->setParameter($this, "item_ref_id", "");

//			if (!$_SESSION["clipboard"])
//			{
			if ($this->object->gotItems($this->node_id))
			{
				$toolbar->setLeadingImage(
					ilUtil::getImagePath("arrow_upright.svg"),
					$lng->txt("actions")
				);
				$toolbar->addFormButton(
					$this->lng->txt('delete_selected_items'),
					'delete'
				);
				$toolbar->addFormButton(
					$this->lng->txt('move_selected_items'),
					'cut'
				);
				$toolbar->addFormButton(
					$this->lng->txt('copy_selected_items'),
					'copy'
				);
				$toolbar->addFormButton(
					$this->lng->txt('download_selected_items'),
					'download'
				);
				// add download button if multi download enabled

				//@todo download
				/*
				$folder_set = new ilSetting("fold");
				if ($folder_set->get("enable_multi_download") == true)
				{
					$toolbar->addSeparator();

					if(!$folder_set->get("bgtask_download", 0))
					{
						$toolbar->addFormButton(
							$this->lng->txt('download_selected_items'),
							'download'
						);
					}
					else
					{

						$url =  $this->ctrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjfoldergui", "ilbackgroundtaskhub"), "", "", true, false);
						$main_tpl->addJavaScript("Services/BackgroundTask/js/BgTask.js");
						$main_tpl->addOnLoadCode("il.BgTask.initMultiForm('ilFolderDownloadBackgroundTaskHandler');");
						$main_tpl->addOnLoadCode('il.BgTask.setAjax("'.$url.'");');

						include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
						$button = ilSubmitButton::getInstance();
						$button->setCaption("download_selected_items");
						$button->addCSSClass("ilbgtasksubmit");
						$button->setCommand("download");
						$toolbar->addButtonInstance($button);
					}
				}*/
			}

			$main_tpl->addAdminPanelToolbar(
				$toolbar,
				($this->object->gotItems($this->node_id) && !$_SESSION["wsp_clipboard"]) ? true : false,
				($this->object->gotItems($this->node_id) && !$_SESSION["wsp_clipboard"]) ? true : false
			);

			// form action needed, see http://www.ilias.de/mantis/view.php?id=9630
			if ($this->object->gotItems($this->node_id))
			{
				$main_tpl->setPageFormAction($this->ctrl->getFormAction($this));
			}
		}
	}


	/**
	 * Set sortation
	 */
	protected function setSortation()
	{
		$this->user_folder_settings->updateSortation($this->object->getId(), $this->requested_sortation);
		$this->ctrl->redirect($this, "");
	}

	function download()
	{
		// This variable determines whether the task has been initiated by a folder's action drop-down to prevent a folder
		// duplicate inside the zip.
		$initiated_by_folder_action = false;

		if ($_GET["item_ref_id"] != "")
		{
			$_POST["id"] = array($_GET["item_ref_id"]);
		}

		if (!isset($_POST["id"]))
		{
			/*$object = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
			$object_type = $object->getType();
			if($object_type == "fold")
			{
				$_POST["id"] = array($_GET['ref_id']);
				$initiated_by_folder_action = true;
			}
			else
			{
				$ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
			}
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);*/
			$this->ctrl->redirect($this, "");
		}

		$download_job = new ilDownloadWorkspaceFolderBackgroundTask($GLOBALS['DIC']->user()->getId(), $_POST["id"], $initiated_by_folder_action);

		$download_job->setBucketTitle($this->getBucketTitle());
		if($download_job->run())
		{
			ilUtil::sendSuccess($this->lng->txt('msg_bt_download_started'),true);
		}
		$this->ctrl->redirect($this);
	}

	public function getBucketTitle()
	{
		return $bucket_title = ilUtil::getAsciiFilename($this->object->getTitle());
	}

}

?>