<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for the workflow of copying objects
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilObjectCopyGUI: 
 *
 * @ingroup ServicesObject
 */
class ilObjectCopyGUI
{
	const SOURCE_SELECTION = 1;
	const TARGET_SELECTION = 2;
	const SEARCH_SOURCE = 3;
	
	private $mode = 0;
	
	private $lng;
	
	private $parent_obj = null;
	
	private $type = '';
	private $source = 0;
	private $target = 0;


	/**
	 * Constructor
	 * @return 
	 */
	public function __construct($a_parent_gui)
	{
		global $ilCtrl,$lng;
		
		$this->lng = $lng;
		$this->lng->loadLanguageModule('search');
		$this->lng->loadLanguageModule('obj');

		$this->parent_obj = $a_parent_gui;

		// this parameter may be filled in "manage" view
		$ilCtrl->saveParameter($this, array("source_ids"));
	}
	
	/**
	 * Control class handling
	 * @return 
	 */
	public function executeCommand()
	{
		global $ilCtrl;
		
		$this->init();

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		

		switch($next_class)
		{
			default:
				$this->$cmd();
				break;
		}
	}
	
	/**
	 * Init return, mode
	 * @return 
	 */
	protected function init()
	{
		global $ilCtrl;
		
		if($_REQUEST['new_type'])
		{
			$this->setMode(self::SEARCH_SOURCE);
	
			$ilCtrl->setParameter($this,'new_type',$this->getType());
			$ilCtrl->setParameterByClass(get_class($this->parent_obj), 'new_type', $this->getType());
			$ilCtrl->setParameterByClass(get_class($this->parent_obj), 'cpfl', 1);
			$ilCtrl->setReturnByClass(get_class($this->parent_obj), 'create');
		}
		elseif($_REQUEST['selectMode'] == self::SOURCE_SELECTION)
		{
			$this->setMode(self::SOURCE_SELECTION);

			$ilCtrl->setParameterByClass(get_class($this->parent_obj), 'selectMode', self::SOURCE_SELECTION);
			$this->setTarget((int) $_GET['ref_id']);
			$ilCtrl->setReturnByClass(get_class($this->parent_obj), '');
		}
		else
		{
			$this->setMode(self::TARGET_SELECTION);
			$ilCtrl->setReturnByClass(get_class($this->parent_obj),'');

			if ($_GET["source_ids"] == "")
			{
				$this->setType(
					ilObject::_lookupType(ilObject::_lookupObjId($this->getSource()))
				);
			}
		}
	}
	
	/**
	 * Init copy from repository/search list commands
	 * @return 
	 */
	protected function initTargetSelection()
	{
		global $ilCtrl, $tree;
		
		// empty session on init
		$_SESSION['paste_copy_repexpand'] = array();
		
		// copy opened nodes from repository explorer		
		$_SESSION['paste_copy_repexpand'] = is_array($_SESSION['repexpand']) ? $_SESSION['repexpand'] : array();


		$this->setMode(self::TARGET_SELECTION);
		$this->setTarget(0);

		// note that source_id is empty, if source_ids are given
		if ($_GET['source_id'] > 0)
		{
			// open current position
			$path = $tree->getPathId((int)$_GET['source_id']);
			foreach((array)$path as $node_id)
			{
				if(!in_array($node_id, $_SESSION['paste_copy_repexpand']))
					$_SESSION['paste_copy_repexpand'][] = $node_id;
			}

			$this->setSource((int) $_GET['source_id']);

			$this->setType(
				ilObject::_lookupType(ilObject::_lookupObjId($this->getSource()))
			);
		}

		$ilCtrl->setReturnByClass(get_class($this->parent_obj),'');
		
		$this->showTargetSelectionTree();
	}
	
	/**
	 * Init source selection
	 * @return 
	 */
	protected function initSourceSelection()
	{
		global $ilCtrl,$tree;

		// empty session on init
		$_SESSION['paste_copy_repexpand'] = array();

		// copy opened nodes from repository explorer
		$_SESSION['paste_copy_repexpand'] = is_array($_SESSION['repexpand']) ? $_SESSION['repexpand'] : array();

		$this->setMode(self::SOURCE_SELECTION);
		$this->setSource(0);
		$this->setTarget((int) $_GET['ref_id']);

		// open current position
		$path = $tree->getPathId($this->getTarget());
		foreach((array) $path as $node_id)
		{
			if(!in_array($node_id, $_SESSION['paste_copy_repexpand']))
				$_SESSION['paste_copy_repexpand'][] = $node_id;
		}
		
		$ilCtrl->setReturnByClass(get_class($this->parent_obj),'');
		$this->showSourceSelectionTree();	
	}
	
	/**
	 * Show target selection
	 */
	public function showTargetSelectionTree()
	{
		global $ilTabs, $ilToolbar, $ilCtrl, $tree, $tpl, $objDefinition, $lng;
	
		$this->tpl = $tpl;

		if($objDefinition->isContainer($this->getType()))
		{
			ilUtil::sendInfo($this->lng->txt('msg_copy_clipboard_container'));
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('msg_copy_clipboard'));
		}

		//
		include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");
		$exp = new ilRepositorySelectorExplorerGUI($this, "showTargetSelectionTree");
		$exp->setTypeWhiteList(array("root", "cat", "grp", "crs", "fold"));
		$exp->setSelectMode("target", false);
		if ($exp->handleCommand())
		{
			return;
		}
		$output = $exp->getHTML();

		// toolbars
		$t = new ilToolbarGUI();
		$t->setFormAction($ilCtrl->getFormAction($this, "saveTarget"));
		if($objDefinition->isContainer($this->getType()))
		{
			$t->addFormButton($lng->txt("btn_next"), "saveTarget");
		}
		else
		{
			$t->addFormButton($lng->txt("paste"), "saveTarget");
		}
		$t->addSeparator();
		$t->addFormButton($lng->txt("obj_insert_into_clipboard"), "keepObjectsInClipboard");
		$t->addFormButton($lng->txt("cancel"), "cancel");
		$t->setCloseFormTag(false);
		$t->setLeadingImage(ilUtil::getImagePath("arrow_upright.svg"), " ");
		$output = $t->getHTML().$output;
		$t->setLeadingImage(ilUtil::getImagePath("arrow_downright.svg"), " ");
		$t->setCloseFormTag(true);
		$t->setOpenFormTag(false);
		$output.= "<br />".$t->getHTML();

		$this->tpl->setContent($output);

	return;

		// old implementation

		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.paste_into_multiple_objects.html',
			"Services/Object");

		include_once './Services/Object/classes/class.ilPasteIntoMultipleItemsExplorer.php';
		$exp = new ilPasteIntoMultipleItemsExplorer(
			ilPasteIntoMultipleItemsExplorer::SEL_TYPE_RADIO,
			'ilias.php?baseClass=ilRepositoryGUI&amp;cmd=goto', 'paste_copy_repexpand');
		
		// Target selection should check for create permission
		$required_perm = 'visible';
		$create_perm = 'create_'.ilObject::_lookupType($this->getSource(), true);
		if($create_perm)
		{
			$required_perm .= (','.$create_perm);
		}
		$exp->setRequiredFormItemPermission($required_perm);
		$exp->setExpandTarget($ilCtrl->getLinkTarget($this, 'showTargetSelectionTree'));
		$exp->setTargetGet('ref_id');
		$exp->setPostVar('target');
		$exp->highlightNode($_GET['ref_id']);
		$exp->setCheckedItems(array((int) $_POST['target']));
		
		// Filter to container
		foreach(array('cat','root','crs','grp','fold') as $container)
		{
			/*
			if($this->getType() == 'crs' and $container == 'crs')
			{
				continue;
			}
			*/
			$sub = $objDefinition->getSubObjects($container);
			if(!isset($sub[$this->getType()]))
			{
				$exp->removeFormItemForType($container);
			}
		}

		if($_GET['paste_copy_repexpand'] == '')
		{
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET['paste_copy_repexpand'];
		}
		
		$this->tpl->setVariable('FORM_TARGET', '_self');
		$this->tpl->setVariable('FORM_ACTION', $ilCtrl->getFormAction($this, 'copySelection'));

		$exp->setExpand($expanded);
		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setVariable('OBJECT_TREE', $output);
		
		$this->tpl->setVariable('CMD_SUBMIT', 'saveTarget');

		if($objDefinition->isContainer($this->getType()))
		{
			$this->tpl->setVariable('TXT_SUBMIT',$this->lng->txt('btn_next'));			
		}
		else
		{
			if(!$objDefinition->isPlugin($this->getType()))
			{
				$submit = $this->lng->txt('obj_'.$this->getType().'_duplicate');
			}	
			else
			{
				// get plugin instance
				include_once "Services/Component/classes/class.ilPlugin.php";
				$plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj",
					ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $this->getType()));
				$submit = $plugin->txt('obj_'.$this->getType().'_duplicate');
			}
			$this->tpl->setVariable('TXT_SUBMIT', $submit);
		}
		
		$ilToolbar->addButton($this->lng->txt('cancel'), $ilCtrl->getLinkTarget($this,'cancel'));
	}
	
	/**
	 * Show target selection
	 */
	public function showSourceSelectionTree()
	{
		global $ilTabs, $ilToolbar, $ilCtrl, $tree, $tpl, $objDefinition;
	
		$this->tpl = $tpl;
		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.paste_into_multiple_objects.html',
			"Services/Object");
		
		ilUtil::sendInfo($this->lng->txt('msg_copy_clipboard_source'));
		include_once './Services/Object/classes/class.ilPasteIntoMultipleItemsExplorer.php';
		$exp = new ilPasteIntoMultipleItemsExplorer(
			ilPasteIntoMultipleItemsExplorer::SEL_TYPE_RADIO,
			'ilias.php?baseClass=ilRepositoryGUI&amp;cmd=goto', 'paste_copy_repexpand');
		$exp->setRequiredFormItemPermission('visible,read,copy');

		$ilCtrl->setParameter($this, 'selectMode', self::SOURCE_SELECTION);
		$exp->setExpandTarget($ilCtrl->getLinkTarget($this, 'showSourceSelectionTree'));
		$exp->setTargetGet('ref_id');
		$exp->setPostVar('source');
		$exp->setCheckedItems(array($this->getSource()));
		#$exp->setNotSelectableItems(array($this->getTarget()));
		
		// Filter to container
		foreach(array('cat','root','grp','fold') as $container)
		{
			$exp->removeFormItemForType($container);
		}
		
		
		if($_GET['paste_copy_repexpand'] == '')
		{
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET['paste_copy_repexpand'];
		}
		
		$this->tpl->setVariable('FORM_TARGET', '_self');
		$this->tpl->setVariable('FORM_ACTION', $ilCtrl->getFormAction($this, 'copySelection'));

		$exp->setExpand($expanded);
		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setVariable('OBJECT_TREE', $output);
		
		$this->tpl->setVariable('CMD_SUBMIT', 'saveSource');
		$this->tpl->setVariable('TXT_SUBMIT', $this->lng->txt('btn_next'));
		
		$ilToolbar->addButton($this->lng->txt('cancel'), $ilCtrl->getLinkTarget($this,'cancel'));
	}

	/**
	 * Save target selection
	 * @return 
	 */
	protected function saveTarget()
	{
		global $objDefinition, $tree;


		if(isset($_REQUEST['target']))
		{
			$this->setTarget((int) $_REQUEST['target']);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->showTargetSelectionTree();
			return false;	
		}

		if($_GET["source_ids"] == "" && $objDefinition->isContainer($this->getType()))
		{
			// check, if object should be copied into itself
			$is_child = array();
			if ($tree->isGrandChild($this->getSource(), $this->getTarget()))
			{
				$is_child[] = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getSource()));
			}
			if ($this->getSource() == $this->getTarget())
			{
				$is_child[] = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getSource()));
			}
			if (count($is_child) > 0)
			{
				ilUtil::sendFailure($this->lng->txt("msg_not_in_itself")." ".implode(',',$is_child));
				$this->showTargetSelectionTree();
				return false;
			}

			$this->showItemSelection();
		}
		else
		{
			if ($_GET["source_ids"] == "")
			{
				$this->copySingleObject();
			}
			else
			{
				$source_ids = explode("_", $_GET["source_ids"]);
				$this->copyMultipleNonContainer($source_ids);
			}
		}
	}

	/**
	 * set copy mode
	 * @param int $a_mode
	 * @return 
	 */
	public function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}
	
	/**
	 * get copy mode
	 * @return 
	 */
	public function getMode()
	{
		return $this->mode;
	}
	
	/**
	 * Get parent gui object
	 * @return object	parent gui
	 */
	public function getParentObject()
	{
		return $this->parent_obj;
	}

	/**
	 * Returns $type.
	 *
	 * @see ilObjectCopyGUI::$type
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Sets $type.
	 *
	 * @param object $type
	 * @see ilObjectCopyGUI::$type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * Set source id
	 * @param int $a_source_id
	 * @return 
	 */
	public function setSource($a_source_id)
	{
		$_SESSION['copy_source'] = $a_source_id;
	}
	
	/**
	 * Get source id
	 * @return 
	 */
	public function getSource()
	{
		if ($_GET["source_ids"] != "")
		{
			return "";
		}
		return $_SESSION['copy_source'];
	}
	
	/**
	 * Set target id
	 * @param int $a_target
	 * @return 
	 */
	public function setTarget($a_target)
	{
		$_SESSION['copy_target'] = $a_target;
	}
	
	/**
	 * Get copy target
	 * @return 
	 */
	public function getTarget()
	{
		return $_SESSION['copy_target'];
	}
	
	/**
	 * Cancel workflow
	 */
	protected function cancel()
	{
		global $ilCtrl;
		$ilCtrl->returnToParent($this);
	}

	/**
	 * Keep objects in clipboard
	 */
	function keepObjectsInClipboard()
	{
		global $ilCtrl;
		$_SESSION['clipboard']['cmd'] = "copy";
		if ($_GET["source_ids"] == "")
		{
			$_SESSION['clipboard']['ref_ids'] = array((int) $this->getSource());
		}
		else
		{
			$_SESSION['clipboard']['ref_ids'] = explode("_", $_GET["source_ids"]);
		}
		$ilCtrl->returnToParent($this);
	}

	
	/**
	 * Search source
	 * @return 
	 */
	protected function searchSource()
	{
		global $tree,$ilObjDataCache,$lng,$ilCtrl,$tpl;
		
		if(isset($_POST['tit']))
		{
			ilUtil::sendInfo($this->lng->txt('wizard_search_list'));
			$_SESSION['source_query'] = $_POST['tit'];
		}
		else
		{
			$_POST['tit'] = $_SESSION['source_query'];
		}

		$this->initFormSearch();
		$this->form->setValuesByPost();		
		
		if(!$this->form->checkInput())
		{
			ilUtil::sendFailure($lng->txt('msg_no_search_string'),true);
			$ilCtrl->returnToParent($this);
			return false;
		}
		
		include_once './Services/Search/classes/class.ilQueryParser.php';
		$query_parser = new ilQueryParser($this->form->getInput('tit'));
		$query_parser->setMinWordLength(1,true);
		$query_parser->setCombination(QP_COMBINATION_AND);
		$query_parser->parse();
		if(!$query_parser->validate())
		{
			ilUtil::sendFailure($query_parser->getMessage(),true);
			$ilCtrl->returnToParent($this);
		}

		// only like search since fulltext does not support search with less than 3 characters
		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search = new ilLikeObjectSearch($query_parser);
		$object_search->setFilter(array($_REQUEST['new_type']));
		$res = $object_search->performSearch();
		$res->setRequiredPermission('copy');
		$res->filter(ROOT_FOLDER_ID,true);
		
		if(!count($results = $res->getResultsByObjId()))
		{
			ilUtil::sendFailure($this->lng->txt('search_no_match'),true);
			$ilCtrl->returnToParent($this);
		}
	
		include_once './Services/Object/classes/class.ilObjectCopySearchResultTableGUI.php';
		$table = new ilObjectCopySearchResultTableGUI($this,'searchSource',$_REQUEST['new_type']);
		$table->setSelectedReference($this->getSource());
		$table->parseSearchResults($results);
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * select source object
	 * @return 
	 */
	protected function saveSource()
	{
		global $objDefinition;
		
		if(isset($_POST['source']))
		{
			$this->setSource($_POST['source']);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->searchSource();
			return false;	
		}
		
		if($objDefinition->isContainer($this->getType()))
		{
			$this->showItemSelection();
		}
		else
		{
			$this->copySingleObject();
		}
	}
	
	
	
	/**
	 * 
	 * @return 
	 */
	protected function showItemSelection()
	{
		global $tpl;
		
		if(!$this->getSource())
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->searchSource();
			return false;
		}
		
		ilUtil::sendInfo($this->lng->txt($this->getType().'_copy_threads_info'));
		include_once './Services/Object/classes/class.ilObjectCopySelectionTableGUI.php';
		
		$tpl->addJavaScript('./Services/CopyWizard/js/ilContainer.js');
		$tpl->setVariable('BODY_ATTRIBUTES','onload="ilDisableChilds(\'cmd\');"');

		switch($this->getMode())
		{
			case self::SOURCE_SELECTION:
				$back_cmd = 'showSourceSelectionTree';
				break;

			case self::TARGET_SELECTION:
				$back_cmd = 'showTargetSelectionTree';
				break;

			case self::SEARCH_SOURCE:
				$back_cmd = 'searchSource';
				break;
		}

		$table = new ilObjectCopySelectionTableGUI($this,'showItemSelection',$this->getType(),$back_cmd);
		$table->parseSource($this->getSource());
		
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * Start cloning a single (not container) object
	 * @return 
	 */
	protected function copySingleObject()
	{
		include_once('./Services/Link/classes/class.ilLink.php');
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		
		global $ilAccess,$ilErr,$rbacsystem,$ilUser,$ilCtrl,$rbacreview;

		// Source defined
		if(!$this->getSource())
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$ilCtrl->returnToParent($this);
		}

		$this->copyMultipleNonContainer(array($this->getSource()));

		return;

	// old implementation



		// Create permission
	 	if(!$rbacsystem->checkAccess('create', $this->getTarget(), $this->getType()))
	 	{
	 		ilUtil::sendFailure($this->lng->txt('permission_denied'),true);
			$ilCtrl->returnToParent($this);
	 	}
		// Source defined
		if(!$this->getSource())
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$ilCtrl->returnToParent($this);
		}
		// Copy permission
		if(!$ilAccess->checkAccess('copy','',$this->getSource()))
		{
	 		ilUtil::sendFailure($this->lng->txt('permission_denied'),true);
			$ilCtrl->returnToParent($this);
		}
		
		// Save wizard options
		$copy_id = ilCopyWizardOptions::_allocateCopyId();
		$wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
		$wizard_options->saveOwner($ilUser->getId());
		$wizard_options->saveRoot((int) $this->getSource());
		
		/*
		$options = $_POST['cp_options'] ? $_POST['cp_options'] : array();
		foreach($options as $source_id => $option)
		{
			$wizard_options->addEntry($source_id,$option);
		}
		*/
		
		$wizard_options->read();
		
		$orig = ilObjectFactory::getInstanceByRefId((int) $this->getSource());
		$new_obj = $orig->cloneObject($this->getTarget(),$copy_id);
		
		// Delete wizard options
		$wizard_options->deleteAll();

		// rbac log
		include_once "Services/AccessControl/classes/class.ilRbacLog.php";
		if(ilRbacLog::isActive())
		{
			$rbac_log_roles = $rbacreview->getParentRoleIds($new_obj->getRefId(), false);
			$rbac_log = ilRbacLog::gatherFaPa($new_obj->getRefId(), array_keys($rbac_log_roles), true);
			ilRbacLog::add(ilRbacLog::COPY_OBJECT, $new_obj->getRefId(), $rbac_log, (int)$this->getSource());
		}

		ilUtil::sendSuccess($this->lng->txt("object_duplicated"),true);
		ilUtil::redirect(ilLink::_getLink($new_obj->getRefId()));
	}
	
	/**
	 * Copy multiple non container
	 *
	 * @param array $a_sources array of source ref ids
	 */
	function copyMultipleNonContainer($a_sources)
	{
		global $ilAccess,$objDefinition,$rbacsystem,$ilUser,$ilCtrl,$rbacreview;


		include_once('./Services/Link/classes/class.ilLink.php');
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');

		// check permissions
		foreach ($a_sources as $source_ref_id)
		{
			$source_type = ilObject::_lookupType($source_ref_id, true);

			// Create permission
			if(!$rbacsystem->checkAccess('create', $this->getTarget(), $source_type))
			{
				ilUtil::sendFailure($this->lng->txt('permission_denied'),true);
				$ilCtrl->returnToParent($this);
			}

			// Copy permission
			if(!$ilAccess->checkAccess('copy','',$source_ref_id))
			{
				ilUtil::sendFailure($this->lng->txt('permission_denied'),true);
				$ilCtrl->returnToParent($this);
			}

			// check that these objects are really not containers
			if($objDefinition->isContainer($source_type))
			{
				ilUtil::sendFailure($this->lng->txt('cntr_container_only_on_their_own'),true);
				$ilCtrl->returnToParent($this);
			}
		}

		reset($a_sources);

		// clone
		foreach ($a_sources as $source_ref_id)
		{
			// Save wizard options
			$copy_id = ilCopyWizardOptions::_allocateCopyId();
			$wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
			$wizard_options->saveOwner($ilUser->getId());
			$wizard_options->saveRoot((int) $source_ref_id);

			$wizard_options->read();

			$orig = ilObjectFactory::getInstanceByRefId((int) $source_ref_id);
			$new_obj = $orig->cloneObject($this->getTarget(),$copy_id);

			// Delete wizard options
			$wizard_options->deleteAll();

			// rbac log
			include_once "Services/AccessControl/classes/class.ilRbacLog.php";
			if(ilRbacLog::isActive())
			{
				$rbac_log_roles = $rbacreview->getParentRoleIds($new_obj->getRefId(), false);
				$rbac_log = ilRbacLog::gatherFaPa($new_obj->getRefId(), array_keys($rbac_log_roles), true);
				ilRbacLog::add(ilRbacLog::COPY_OBJECT, $new_obj->getRefId(), $rbac_log, (int)$source_ref_id);
			}
		}

		unset($_SESSION["clipboard"]["ref_ids"]);
		unset($_SESSION["clipboard"]["cmd"]);

		if (count($a_sources) == 1)
		{
			ilUtil::sendSuccess($this->lng->txt("object_duplicated"),true);
			ilUtil::redirect(ilLink::_getLink($new_obj->getRefId()));
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt("objects_duplicated"),true);
			ilUtil::redirect(ilLink::_getLink($this->getTarget()));
		}

	}
	
	
	/**
	 * Copy a container
	 * @return 
	 */
	protected function copyContainer()
	{
		global $ilLog, $ilCtrl;
		
		include_once('./Services/Link/classes/class.ilLink.php');
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		
		global $ilAccess,$ilErr,$rbacsystem,$tree,$ilUser,$ilCtrl;
		
		// Workaround for course in course copy

		$target_type = ilObject::_lookupType(ilObject::_lookupObjId($this->getTarget()));
		$source_type = ilObject::_lookupType(ilObject::_lookupObjId($this->getSource()));
		
		if($target_type != $source_type or $target_type != 'crs')
		{
		 	if(!$rbacsystem->checkAccess('create', $this->getTarget(),$this->getType()))
		 	{
		 		ilUtil::sendFailure($this->lng->txt('permission_denied'),true);
				$ilCtrl->returnToParent($this);
		 	}
		}
		if(!$this->getSource())
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$ilCtrl->returnToParent($this);
			return false;
		}

		$options = $_POST['cp_options'] ? $_POST['cp_options'] : array();
		$orig = ilObjectFactory::getInstanceByRefId($this->getSource());
		$result = $orig->cloneAllObject($_COOKIE['PHPSESSID'], $_COOKIE['ilClientId'], $this->getType(), $this->getTarget(), $this->getSource(), $options);


		unset($_SESSION["clipboard"]["ref_ids"]);
		unset($_SESSION["clipboard"]["cmd"]);

		// Check if copy is in progress
		if ($result == $this->getTarget())
		{
			ilUtil::sendInfo($this->lng->txt("object_copy_in_progress"),true);
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id",
				$this->getTarget());
			$ilCtrl->redirectByClass("ilrepositorygui", "");
		} 
		else 
		{
			ilUtil::sendSuccess($this->lng->txt("object_duplicated"),true);
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id",
				$result);
			$ilCtrl->redirectByClass("ilrepositorygui", "");
		}	
	}
	
	
	
	/**
	 * Show init screen
	 * Normally shown below the create and import form when creating a new object
	 * 
	 * @param string $a_tplvar The tpl variable to fill 
	 * @return 
	 */
	public function showSourceSearch($a_tplvar)
	{
		global $tpl;
		
		// Disabled for performance
		#if(!$this->sourceExists())
		#{
		#	return false;
		#}

		$this->unsetSession();
		$this->initFormSearch();

		if($a_tplvar)
		{
			$tpl->setVariable($a_tplvar,$this->form->getHTML());
		}
		else
		{
			return $this->form;
		}
	}
	
	
	/**
	 * Check if there is any source object
	 * @return bool
	 */
	protected function sourceExists()
	{
		global $ilUser;

		return (bool) ilUtil::_getObjectsByOperations($this->getType(),'copy',$ilUser->getId(),1);
	}
	
	/**
	 * Init search form
	 * @return 
	 */
	protected function initFormSearch()
	{
		global $lng,$ilCtrl;
		
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->form = new ilPropertyFormGUI();
		$this->form->setTableWidth('600px');
		$ilCtrl->setParameter($this,'new_type',$this->getType());
		#$ilCtrl->setParameter($this, 'cp_mode', self::SOURCE_SELECTION);
		$this->form->setFormAction($ilCtrl->getFormAction($this));
		$this->form->setTitle($lng->txt($this->getType().'_copy'));
		
		$this->form->addCommandButton('searchSource', $lng->txt('btn_next'));
		
		$tit = new ilTextInputGUI($lng->txt('title'),'tit');
		$tit->setSize(40);
		$tit->setMaxLength(70);
		$tit->setRequired(true);
		$tit->setInfo($lng->txt('wizard_title_info'));
		$this->form->addItem($tit);
	}
	
	/**
	 * Unset session variables
	 * @return 
	 */
	protected function unsetSession()
	{
		unset($_SESSION['source_query']);
		$this->setSource(0);
	}
}
?>