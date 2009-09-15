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

		$this->parent_obj = $a_parent_gui;
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
			$this->setMode(self::SOURCE_SELECTION);
	
			$ilCtrl->setParameter($this,'new_type',$this->getType());
			$ilCtrl->setParameterByClass(get_class($this->parent_obj), 'new_type', $this->getType());
			$ilCtrl->setReturnByClass(get_class($this->parent_obj), 'create');
		}
		else
		{
			$this->setMode(self::TARGET_SELECTION);
			$ilCtrl->setReturnByClass(get_class($this->parent_obj),'');
			
			$this->setType(
				ilObject::_lookupType(ilObject::_lookupObjId($this->getSource()))
			);
		}
	}
	
	/**
	 * Init copy from repository/search list commands
	 * @return 
	 */
	protected function initTargetSelection()
	{
		global $ilCtrl;

		$this->setMode(self::TARGET_SELECTION);
		$this->setSource((int) $_GET['source_id']);
		$this->setType(
			ilObject::_lookupType(ilObject::_lookupObjId($this->getSource()))
		);
		
		$ilCtrl->setReturnByClass(get_class($this->parent_obj),'');
		
		$this->showTargetSelectionTree();
	}
	
	/**
	 * Show target selection
	 * @return 
	 */
	public function showTargetSelectionTree()
	{
		global $ilTabs, $ilToolbar, $ilCtrl, $tree, $tpl, $objDefinition;
	
		$this->tpl = $tpl;
		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.paste_into_multiple_objects.html');
		
		ilUtil::sendInfo($this->lng->txt('msg_copy_clipboard'));
		
		include_once 'classes/class.ilPasteIntoMultipleItemsExplorer.php';
		$exp = new ilPasteIntoMultipleItemsExplorer(
			ilPasteIntoMultipleItemsExplorer::SEL_TYPE_RADIO,
			'repository.php?cmd=goto', 'paste_copy_repexpand');
		$exp->setExpandTarget($ilCtrl->getLinkTarget($this, 'showTargetSelectionTree'));
		$exp->setTargetGet('ref_id');
		$exp->setPostVar('target');
		$exp->setCheckedItems(array((int) $_POST['target']));
		
		// Filter to container
		foreach(array('cat','root','crs','grp','fold') as $container)
		{
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
			$this->tpl->setVariable('TXT_SUBMIT', $this->lng->txt('obj_'.$this->getType().'_duplicate'));
		}
		
		$ilToolbar->addButton($this->lng->txt('back'), $ilCtrl->getLinkTarget($this,'cancel'));
	}
	
	/**
	 * Save target selection
	 * @return 
	 */
	protected function saveTarget()
	{
		global $objDefinition;
		
		if(isset($_POST['target']))
		{
			$this->setTarget((int) $_REQUEST['target']);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->showTargetSelectionTree();
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
	 * @return 
	 */
	protected function cancel()
	{
		global $ilCtrl;
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
		$query_parser->setMinWordLength(1);
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
		
		$back_cmd = $this->getMode() == self::SOURCE_SELECTION  ? 'searchSource' : 'showTargetSelectionTree';
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
		include_once('classes/class.ilLink.php');
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		
		global $ilAccess,$ilErr,$rbacsystem,$ilUser,$ilCtrl;
		
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

		ilUtil::sendSuccess($this->lng->txt("object_duplicated"),true);
		ilUtil::redirect(ilLink::_getLink($new_obj->getRefId()));
	}
	
	/**
	 * Copy a container
	 * @return 
	 */
	protected function copyContainer()
	{
		global $ilLog;
		
		include_once('classes/class.ilLink.php');
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		
		global $ilAccess,$ilErr,$rbacsystem,$tree,$ilUser,$ilCtrl;
		
	 	if(!$rbacsystem->checkAccess('create', $this->getTarget(),$this->getType()))
	 	{
	 		ilUtil::sendFailure($this->lng->txt('permission_denied'),true);
			$ilCtrl->returnToParent($this);
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
		
		// Check if copy is in progress
		if ($result == $this->getTarget())
		{
			ilUtil::sendInfo($this->lng->txt("object_copy_in_progress"),true);
			ilUtil::redirect('repository.php?ref_id='.$ref_id);
		} 
		else 
		{
			ilUtil::sendSuccess($this->lng->txt("object_duplicated"),true);
			ilUtil::redirect('repository.php?ref_id='.$result);
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
		
		if(!$this->sourceExists())
		{
			return false;
		}
		
		$this->unsetSession();

		$this->initFormSearch();
		$tpl->setVariable($a_tplvar,$this->form->getHTML());
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
		$this->form->setTableWidth('60%');
		$ilCtrl->setParameter($this,'new_type',$this->getType());
		$ilCtrl->setParameter($this, 'copy_mode', self::SOURCE_SELECTION);
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