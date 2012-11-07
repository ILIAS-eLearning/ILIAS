<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';

/* 
 * Class for ECS node and directory mapping settings
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $ID$
 *
 * @ingroup ServicesWebServicesECS
 * @ilCtrl_isCalledBy ilECSMappingSettingsGUI: ilECSSettingsGUI
 */
class ilECSMappingSettingsGUI
{
	const TAB_DIRECTORY = 1;
	const TAB_COURSE = 2;

	private $container = null;
	private $server = null;
	private $mid = null;

	protected $lng = null;
	protected $ctrl = null;

	/**
	 * Constructor
	 * @param ilObjectGUI $settingsContainer
	 */
	public function __construct($settingsContainer, $server_id, $mid)
	{
		global $lng,$ilCtrl;

		$this->container = $settingsContainer;
		$this->server = ilECSSetting::getInstanceByServerId($server_id);
		$this->mid = $mid;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('ecs');
		$this->ctrl = $ilCtrl;
	}

	/**
	 * Get container object
	 * @return ilObjectGUI
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 *
	 * @return ilECSSetting
	 */
	public function getServer()
	{
		return $this->server;
	}

	/**
	 * Get mid
	 * @return int Get mid
	 */
	public function getMid()
	{
		return $this->mid;
	}
	
	/**
	 * ilCtrl executeCommand
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$GLOBALS['tpl']->setTitle($this->lng->txt('ecs_campus_connect_title'));

		$this->ctrl->saveParameter($this,'server_id');
		$this->ctrl->saveParameter($this,'mid');
		$this->ctrl->saveParameter($this,'tid');

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->setTabs();
		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "cStart";
				}
				$this->$cmd();
				break;
		}

		$GLOBALS['tpl']->setTitle($this->getServer()->getTitle());
		$GLOBALS['tpl']->setDescription('');

		return true;
	}

	/**
	 * return to parent container
	 */
	public function cancel()
	{
		$GLOBALS['ilCtrl']->returnToParent($this);
	}

	/**
	 * Goto default page
	 * @return <type>
	 */
	protected function cStart()
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
		if(!ilECSNodeMappingSettings::getInstance()->isEnabled() or 0)
		{
			return $this->cSettings();
		}
		return $this->cSettings();
	}

	/**
	 * Goto default page
	 * @return <type>
	 */
	protected function dStart()
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
		if(ilECSNodeMappingSettings::getInstance()->isDirectoryMappingEnabled())
		{
			return $this->dTrees();
		}
		return $this->dSettings();
	}

	/**
	 * Show course allocation
	 * @global ilTabsGUI $ilTabs
	 * @return bool
	 */
	protected function cSettings()
	{
		global $ilTabs;
		
		$ilTabs->activateTab('ecs_crs_allocation');

		$form = $this->initFormCSettings();

		$GLOBALS['tpl']->setContent($form->getHTML());

		return true;
	}

	/**
	 * Init settings form
	 */
	protected function initFormCSettings()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('settings'));

		// add default container
		$imp = new ilCustomInputGUI($this->lng->txt('ecs_import_id'),'import_id');
		$imp->setRequired(true);

		$tpl = new ilTemplate('tpl.ecs_import_id_form.html',true,true,'Services/WebServices/ECS');
		$tpl->setVariable('SIZE',5);
		$tpl->setVariable('MAXLENGTH',11);
		$tpl->setVariable('POST_VAR','import_id');
#		$tpl->setVariable('PROPERTY_VALUE',$this->rule->getContainerId());

		#if($this->settings->getImportId())
		{
		#	$tpl->setVariable('COMPLETE_PATH',$this->buildPath($this->rule->getContainerId()));
		}

		$imp->setHTML($tpl->get());
		$imp->setInfo($this->lng->txt('ecs_import_id_info'));
		$form->addItem($imp);

		// individual course allocation
		$check = new ilCheckboxInputGUI($this->lng->txt('ecs_individual_alloc'), 'individual');
		$check = new ilCheckboxInputGUI('Individual Allocation', 'individual');
		#$check->setInfo($this->lng->txt('ecs_individual_alloc'));
		$form->addItem($check);

		#$form->addCommandButton('cUpdateSettings',$this->lng->txt('save'));
		$form->addCommandButton('cSettings',$this->lng->txt('save'));
		$form->addCommandButton('cSettings', $this->lng->txt('cancel'));

		return $form;
	}

	/**
	 * Show directory allocation
	 * @global ilTabsGUI $ilTabs
	 */
	protected function dSettings()
	{
		global $ilTabs;

		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
		$this->setSubTabs(self::TAB_DIRECTORY);
		$ilTabs->activateTab('ecs_dir_allocation');
		$ilTabs->activateSubTab('dSettings');

		$form = $this->initFormDSettings();

		$GLOBALS['tpl']->setContent($form->getHTML());



		return true;
	}

	/**
	 * Update node mapping settings
	 */
	protected function dUpdateSettings()
	{
		global $ilCtrl;

		$form = $this->initFormDSettings();
		if($form->checkInput())
		{
			include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
			ilECSNodeMappingSettings::getInstance()->enableDirectoryMapping((bool) $form->getInput('active'));
			ilECSNodeMappingSettings::getInstance()->enableEmptyContainerCreation(!$form->getInput('empty'));
			ilECSNodeMappingSettings::getInstance()->update();
			ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('err_check_input'),true);
			$form->setValuesByPost();
		}
		$ilCtrl->redirect($this,'dSettings');
	}

	/**
	 *
	 */
	protected function initFormDSettings()
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('general_settings'));

		$active = new ilCheckboxInputGUI($this->lng->txt('ecs_node_mapping_activate'), 'active');
		$active->setChecked(ilECSNodeMappingSettings::getInstance()->isDirectoryMappingEnabled());
		$form->addItem($active);

		$create_empty = new ilCheckboxInputGUI($this->lng->txt('ecs_node_mapping_create_empty'), 'empty');
		$create_empty->setChecked(!ilECSNodeMappingSettings::getInstance()->isEmptyContainerCreationEnabled());
		$create_empty->setInfo($this->lng->txt('ecs_node_mapping_create_empty_info'));
		$form->addItem($create_empty);

		$form->addCommandButton('dUpdateSettings',$this->lng->txt('save'));
		$form->addCommandButton('cancel', $this->lng->txt('cancel'));

		return $form;
	}

	/**
	 * Show directory trees
	 */
	protected function dTrees()
	{
		$this->setSubTabs(self::TAB_DIRECTORY);
		$GLOBALS['ilTabs']->activateSubTab('dTrees');
		$GLOBALS['ilTabs']->activateTab('ecs_dir_allocation');

		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingTreeTableGUI.php';

		$dtreeTable = new ilECSNodeMappingTreeTableGUI(
			$this->getServer()->getServerId(),
			$this->getMid(),
			$this,
			'dtree');

		/*
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSTreeReader.php';
		$tree_reader = new ilECSTreeReader($this->getServer()->getServerId(), $this->getMid());
		try
		{
			$tree_reader->read();
		}
		catch(ilECSConnectorException $e)
		{
			ilUtil::sendFailure($e->getMessage());
		}
		*/

		$dtreeTable->parse();
		$GLOBALS['tpl']->setContent($dtreeTable->getHTML());
		return true;
	}

	/**
	 * Delete tree settings
	 */
	protected function dConfirmDeleteTree()
	{
		$this->setSubTabs(self::TAB_DIRECTORY);
		$GLOBALS['ilTabs']->activateSubTab('dTrees');
		$GLOBALS['ilTabs']->activateTab('ecs_dir_allocation');

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';

		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($this->lng->txt('ecs_confirm_delete_tree'));

		$confirm->addItem(
			'tid',
			(int) $_REQUEST['tid'],
			ilECSCmsData::lookupTitle(
				$this->getServer()->getServerId(),
				$this->getMid(),
				(int) $_REQUEST['tid']
			)
		);
		$confirm->setConfirm($this->lng->txt('delete'), 'dDeleteTree');
		$confirm->setCancel($this->lng->txt('cancel'), 'dTrees');

		$GLOBALS['tpl']->setContent($confirm->getHTML());
	}

	/**
	 * Delete tree
	 */
	protected function dDeleteTree()
	{
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';

		$GLOBALS['ilLog']->write('Deleting tree');

		$tree = new ilECSCmsTree((int) $_REQUEST['tid']);
		$tree->deleteTree($tree->getNodeData(ilECSCmsTree::lookupRootId((int) $_REQUEST['tid'])));

		$data = new ilECSCmsData();
		$data->setServerId($this->getServer()->getServerId());
		$data->setMid($this->getMid());
		$data->setTreeId((int) $_REQUEST['tid']);
		$data->deleteTree();


		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
		ilECSNodeMappingAssignments::deleteMappings(
			$this->getServer()->getServerId(),
			$this->getMid(),
			(int) $_REQUEST['tid']
		);

		ilUtil::sendSuccess($this->lng->txt('ecs_cms_tree_deleted'),true);
		$this->ctrl->redirect($this,'dTrees');
	}

	/**
	 * Edit directory tree assignments
	 */
	protected function dEditTree(ilPropertyFormGUI $form = null)
	{
		$GLOBALS['tpl']->addBlockFile('ADM_CONTENT','adm_content','tpl.ecs_edit_tree.html','Services/WebServices/ECS');

		$this->ctrl->saveParameter($this,'cid');

		$GLOBALS['ilTabs']->clearTargets();
		$GLOBALS['ilTabs']->setBack2Target(
			$this->lng->txt('ecs_back_settings'),
			$this->ctrl->getLinkTarget($this,'cancel')
		);
		$GLOBALS['ilTabs']->setBackTarget(
			$this->lng->txt('ecs_cms_dir_tree'),
			$this->ctrl->getLinkTarget($this,'dTrees')
		);
		
		$GLOBALS['tpl']->setVariable('LEGEND',$GLOBALS['lng']->txt('ecs_status_legend'));
		$GLOBALS['tpl']->setVariable('PENDING_UNMAPPED',$GLOBALS['lng']->txt('ecs_status_pending_unmapped'));
		$GLOBALS['tpl']->setVariable('PENDING_UNMAPPED_DISCON',$GLOBALS['lng']->txt('ecs_status_pending_unmapped_discon'));
		$GLOBALS['tpl']->setVariable('PENDING_UNMAPPED_NONDISCON',$GLOBALS['lng']->txt('ecs_status_pending_unmapped_nondiscon'));
		$GLOBALS['tpl']->setVariable('MAPPED',$GLOBALS['lng']->txt('ecs_status_mapped'));
		$GLOBALS['tpl']->setVariable('DELETED',$GLOBALS['lng']->txt('ecs_status_deleted'));

		$form = $this->dInitFormTreeSettings($form);
		$GLOBALS['tpl']->setVariable('GENERAL_FORM',$form->getHTML());
		$GLOBALS['tpl']->setVariable('TFORM_ACTION',$this->ctrl->getFormAction($this,'dEditTree'));

		$explorer = $this->dShowLocalExplorer();
		$this->dShowCmsExplorer($explorer);
	}

	/**
	 * Init form settings
	 */
	protected function dInitFormTreeSettings(ilPropertyFormGUI $form = null)
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingUtils.php';
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';

		if($form instanceof ilPropertyFormGUI)
		{
			return $form;
		}

		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignment.php';
		$assignment = new ilECSNodeMappingAssignment(
			$this->getServer()->getServerId(),
			$this->getMid(),
			(int) $_REQUEST['tid'],
			0
		);

		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this,'dEditTree'));
		$form->setTitle($this->lng->txt('general_settings'));
		$form->addCommandButton('dUpdateTreeSettings', $this->lng->txt('save'));
		$form->addCommandButton('dTrees', $this->lng->txt('cancel'));
		$form->setTableWidth('30%');

		// CMS id (readonly)
		$cmsid = new ilNumberInputGUI($this->lng->txt('ecs_cms_id'), 'cmsid');
		$cmsid->setValue(
			ilECSCmsTree::lookupRootId((int) $_REQUEST['tid'])
		);
		$cmsid->setDisabled(true);
		$cmsid->setSize(7);
		$cmsid->setMaxLength(12);
		$form->addItem($cmsid);


		$mapping_status = ilECSMappingUtils::lookupMappingStatus(
			$this->getServer()->getServerId(),
			$this->getMid(),
			(int) $_REQUEST['tid']);
		$mapping_advanced = ($mapping_status != ilECSMappingUtils::MAPPED_MANUAL ? true : false);

		// Status (readonly)
		$status = new ilNonEditableValueGUI($this->lng->txt('status'), '');
		$status->setValue(ilECSMappingUtils::mappingStatusToString($mapping_status));
		$form->addItem($status);

		// title update
		$title = new ilCheckboxInputGUI($this->lng->txt('ecs_title_updates'), 'title');
		$title->setValue(1);
		$title->setChecked($assignment->isTitleUpdateEnabled());
		#$title->setInfo($this->lng->txt('ecs_title_update_info'));
		$form->addItem($title);


		$position = new ilCheckboxInputGUI($this->lng->txt('ecs_position_updates'), 'position');
		$position->setDisabled(!$mapping_advanced);
		$position->setChecked($mapping_advanced && $assignment->isPositionUpdateEnabled());
		$position->setValue(1);
		#$position->setInfo($this->lng->txt('ecs_position_update_info'));
		$form->addItem($position);

		$tree = new ilCheckboxInputGUI($this->lng->txt('ecs_tree_updates'), 'tree');
		$tree->setDisabled(!$mapping_advanced);
		$tree->setChecked($mapping_advanced && $assignment->isTreeUpdateEnabled());
		$tree->setValue(1);
		#$tree->setInfo($this->lng->txt('ecs_tree_update_info'));
		$form->addItem($tree);

		return $form;
	}

	/**
	 *
	 * @return boolean Update global settings
	 */
	protected function dUpdateTreeSettings()
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignment.php';
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
		$assignment = new ilECSNodeMappingAssignment(
			$this->getServer()->getServerId(),
			$this->getMid(),
			(int) $_REQUEST['tid'],
			0
		);
		$assignment->setRefId(0);
		$assignment->setObjId(0);

		$form = $this->dInitFormTreeSettings();
		if($form->checkInput())
		{
			$assignment->enableTitleUpdate($form->getInput('title'));
			$assignment->enableTreeUpdate($form->getInput('tree'));
			$assignment->enablePositionUpdate($form->getInput('position'));
			$assignment->update();

			ilUtil::sendSuccess($this->lng->txt('settings_saved',true));
			$this->ctrl->redirect($this,'dEditTree');
		}

		$form->setValuesByPost();
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->dEditTree($form);
		return true;
	}
	
	/**
	 * Synchronize Tree
	 */
	protected function dSynchronizeTree()
	{
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTreeSynchronizer.php';
		$sync = new ilECSCmsTreeSynchronizer(
				$this->getServer(),
				$this->mid,
				(int) $_REQUEST['tid']
			);
		$sync->sync();
		ilUtil::sendSuccess($this->lng->txt('ecs_cms_tree_synchronized'),true);
		$this->ctrl->redirect($this,'dTrees');
	}

	/**
	 * Show local explorer
	 */
	protected function dShowLocalExplorer()
	{
		global $tree;

		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingLocalExplorer.php';
		$explorer = new ilECSNodeMappingLocalExplorer($this->ctrl->getLinkTarget($this,'dEditTree'));
		$explorer->setPostVar('lnodes[]');

		$lnodes = (array) $_REQUEST['lnodes'];
		$checked_node = array_pop($lnodes);
		if((int) $_REQUEST['lid'])
		{
			$checked_node = (int) $_REQUEST['lid'];
		}

		if($checked_node)
		{
			$explorer->setCheckedItems(array($checked_node));
		}
		else
		{
			$explorer->setCheckedItems(array(ROOT_FOLDER_ID));
		}
		$explorer->setTargetGet('lref_id');
		$explorer->setSessionExpandVariable('lexpand');
		$explorer->setExpand((int) $_GET['lexpand']);
		$explorer->setExpandTarget($this->ctrl->getLinkTarget($this,'dEditTree'));
		$explorer->setOutput(0);
		$GLOBALS['tpl']->setVariable('LOCAL_EXPLORER',$explorer->getOutput());

		return $explorer;
	}

	/**
	 * Show cms explorer
	 */
	protected function dShowCmsExplorer(ilExplorer $localExplorer)
	{
		global $tree;

		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingCmsExplorer.php';
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';

		$explorer = new ilECSNodeMappingCmsExplorer(
			$this->ctrl->getLinkTarget($this,'dEditTree'),
			$this->getServer()->getServerId(),
			$this->getMid(),
			(int) $_REQUEST['tid']
		);
		$explorer->setRoot(ilECSCmsTree::lookupRootId((int) $_REQUEST['tid']));
		$explorer->setTree(
			new ilECSCmsTree(
				(int) $_REQUEST['tid']
			)
		);
		$explorer->setPostVar('rnodes[]');

		// Read checked items from mapping of checked items in local explorer
		$active_node = $tree->getRootId();
		foreach($localExplorer->getCheckedItems() as $ref_id)
		{
			$explorer->setCheckedItems(
				ilECSNodeMappingAssignments::lookupMappedItemsForRefId(
					$this->getServer()->getServerId(),
					$this->getMid(),
					(int) $_REQUEST['tid'],
					$ref_id
				)
			);
			$active_node = $ref_id;
		}


		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
		$cmsTree = new ilECSCmsTree((int) $_REQUEST['tid']);
		foreach(ilECSNodeMappingAssignments::lookupAssignmentsByRefId(
			$this->getServer()->getServerId(),
			$this->getMid(),
			(int) $_REQUEST['tid'],
			$active_node
			) as $cs_id)
		{
			foreach($cmsTree->getPathId($cs_id) as $path_id)
			{
				#$explorer->setExpand($path_id);
			}
		}

		$explorer->setTargetGet('rref_id');
		$explorer->setSessionExpandVariable('rexpand');

		#if((int) $_REQUEST['rexpand'])
		{
			$explorer->setExpand((int) $_GET['rexpand']);
		}
		$explorer->setExpandTarget($this->ctrl->getLinkTarget($this,'dEditTree'));
		$explorer->setOutput(0);
		$GLOBALS['tpl']->setVariable('REMOTE_EXPLORER',$explorer->getOutput());

	}

	/**
	 * Init tree
	 * @return
	 */
	protected function dInitEditTree()
	{
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
		ilECSCmsData::updateStatus(
			$this->getServer()->getServerId(),
			$this->getMid(),
			(int) $_REQUEST['tid']
		);
		return $this->dEditTree();
	}


	/**
	 * Do mapping
	 */
	protected function dMap()
	{
		if(!$_POST['lnodes'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$this->ctrl->redirect($this,'dEditTree');
		}

		$ref_id = end($_POST['lnodes']);

		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
		ilECSNodeMappingAssignments::deleteDisconnectableMappings(
			$this->getServer()->getServerId(),
			$this->getMid(),
			(int) $_REQUEST['tid'],
			$ref_id
		);


		$nodes = (array) $_POST['rnodes'];
		$nodes = (array) array_reverse($nodes);

		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignment.php';
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
		foreach($nodes as $cms_id)
		{
			$assignment = new ilECSNodeMappingAssignment(
				$this->getServer()->getServerId(),
				$this->getMid(),
				(int) $_REQUEST['tid'],
				(int) $cms_id
			);
			$assignment->setRefId($ref_id);
			$assignment->setObjId(ilObject::_lookupObjId($ref_id));
			$assignment->enablePositionUpdate(false);
			$assignment->enableTreeUpdate(false);
			$assignment->enableTitleUpdate(ilECSNodeMappingAssignments::lookupDefaultTitleUpdate(
				$this->getServer()->getServerId(),
				$this->getMid(),
				(int) $_REQUEST['tid']
			));
			$assignment->update();

			// Delete subitems mappings for cms subtree
			$cmsTree = new ilECSCmsTree((int) $_REQUEST['tid']);
			$childs = $cmsTree->getSubTreeIds($cms_id);

			ilECSNodeMappingAssignments::deleteMappingsByCsId(
				$this->getServer()->getServerId(),
				$this->getMid(),
				(int) $_REQUEST['tid'],
				$childs
			);

		}

		ilECSCmsData::updateStatus(
			$this->getServer()->getServerId(),
			$this->getMid(),
			(int) $_REQUEST['tid']
		);

		// Save parameter cid
		$this->ctrl->setParameter($this,'lid',(int) $ref_id);

		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'dEditTree');
	}

	/**
	 * Show directory trees
	 */
	protected function dMappingOverview()
	{
		$this->setSubTabs(self::TAB_DIRECTORY);
		$GLOBALS['ilTabs']->activateSubTab('dMappingOverview');
		$GLOBALS['ilTabs']->activateTab('ecs_dir_allocation');
	}

	/**
	 * Set tabs
	 * @global ilTabsGUI $ilTabs
	 */
	protected function setTabs()
	{
		global $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget(
			$this->lng->txt('ecs_back_settings'),
			$this->ctrl->getParentReturn($this)
		);
		$ilTabs->addTab(
			'ecs_dir_allocation',
			$this->lng->txt('ecs_dir_alloc'),
			$this->ctrl->getLinkTarget($this,'dSettings')
		);
		$ilTabs->addTab(
			'ecs_crs_allocation',
			$this->lng->txt('ecs_crs_alloc'),
			$this->ctrl->getLinkTarget($this,'cSettings')
		);
	}

	/**
	 * Set Sub tabs
	 * @global ilTabsGUI $ilTabs
	 * @param string $a_tab 
	 */
	protected function setSubTabs($a_tab)
	{
		global $ilTabs;

		if($a_tab == self::TAB_DIRECTORY)
		{
			$ilTabs->addSubTab(
				'dMappingOverview',
				$this->lng->txt('ecs_cc_mapping_overview'),
				$this->ctrl->getLinkTarget($this,'dMappingOverview')
			);
			$ilTabs->addSubTab(
				'dTrees',
				$this->lng->txt('ecs_cms_dir_tree'),
				$this->ctrl->getLinkTarget($this,'dTrees')
			);
			$ilTabs->addSubTab(
				'dSettings',
				$this->lng->txt('settings'),
				$this->ctrl->getLinkTarget($this,'dSettings')
			);
		}
	}
}
?>
