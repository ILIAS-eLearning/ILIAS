<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once('./Services/Object/classes/class.ilObjectGUI.php');

/** 
* 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesContainerReference 
*/
class ilContainerReferenceGUI extends ilObjectGUI
{
	const MAX_SELECTION_ENTRIES = 50;
	
	const MODE_CREATE = 1;
	const MODE_EDIT = 2;
	
	protected $existing_objects = array();

	/**
	 * Constructor
	 * @param
	 * @return
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $lng; 
		parent::__construct($a_data, $a_id,$a_call_by_reference,$a_prepare_output);
		
		$lng->loadLanguageModule('objref');
	}
	
	/**
	 * Add locator item
	 * @global type $ilLocator
	 */
	protected function addLocatorItems()
	{
		global $ilLocator;
		
		if($this->object instanceof ilObject)
		{
			$ilLocator->addItem($this->object->getPresentationTitle(),$this->ctrl->getLinkTarget($this));
		}
	}
	
	/**
	 * redirect to target 
	 * @param
	 * @return
	 */
	public function redirectObject()
	{
		global $ilCtrl;
		
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->object->getTargetRefId());
		$ilCtrl->redirectByClass("ilrepositorygui", "");
	}
	
	/**
	 * Create object 
	 * 
	 * @return void
	 */
	public function createObject()
	{
		global $ilUser,$ilAccess,$ilErr,$ilSetting;
		
		$new_type = $_REQUEST["new_type"];
		if(!$ilAccess->checkAccess("create_".$this->getReferenceType(),'',$_GET["ref_id"], $new_type))
		{
			$ilErr->raiseError($this->lng->txt("permission_denied"),$ilErr->MESSAGE);
		}

		$this->initTargetSelection(self::MODE_CREATE);
		ilUtil::sendInfo($this->lng->txt($this->getReferenceType().'_edit_info'));
	}
	
	
	/**
	 * save object
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function saveObject()
	{
		global $ilAccess;
		
		if(!(int) $_REQUEST['target_id'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->createObject();
			return false;	
		}
		if(!$ilAccess->checkAccess('visible','',(int) $_REQUEST['target_id']))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			$this->createObject();
			return false;	
		}
		
		parent::saveObject();		
	}
	
	protected function initCreateForm($a_new_type)
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		return $form;
	}	
	
	protected function afterSave(ilObject $a_new_object)
	{		
		$target_obj_id = ilObject::_lookupObjId((int) $_REQUEST['target_id']);
		$a_new_object->setTargetId($target_obj_id);
		$a_new_object->update();
		
		ilUtil::sendSuccess($this->lng->txt("object_added"), true);
		$this->ctrl->setParameter($this,'ref_id',$a_new_object->getRefId());
		$this->ctrl->redirect($this,'firstEdit');
	}
	
	/**
	 * show edit screen without info message
	 */
	protected function firstEditObject()
	{
		$this->editObject(false);
	}
	
	
	/**
	 * edit object
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function editObject($a_show_info_message = true)
	{
		global $ilUser,$ilSetting,$ilTabs;
		
		$ilTabs->setTabActive('edit');
		$ilTabs->addSubTab('edit',$this->lng->txt('objref_edit_ref'),$this->ctrl->getLinkTarget($this,'edit'));
		$ilTabs->addSubTab('editTitle',$this->lng->txt('objref_edit_title'),$this->ctrl->getLinkTarget($this,'editTitle'));
		$ilTabs->setTabActive('edit');
		$ilTabs->activateSubTab('edit');
		$this->initTargetSelection(self::MODE_EDIT);
		
		if($a_show_info_message)
		{
			ilUtil::sendInfo($this->lng->txt($this->getReferenceType().'_edit_info'));
		}
	}

	public function editReferenceObject()
	{
		$this->editObject();
	}
	
	/**
	 * edit title
	 */
	protected function editTitleObject(ilPropertyFormGUI $form = null)
	{
		global $ilTabs;
		
		$ilTabs->addSubTab('edit',$this->lng->txt('objref_edit_ref'),$this->ctrl->getLinkTarget($this,'edit'));
		$ilTabs->addSubTab('editTitle',$this->lng->txt('objref_edit_title'),$this->ctrl->getLinkTarget($this,'editTitle'));
		$ilTabs->setTabActive('edit');		
		$ilTabs->activateSubTab('editTitle');
		
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initFormTitle();
		}
		$GLOBALS['tpl']->setContent($form->getHTML());
	}
	
	/**
	 * Init title form
	 * @return ilPropertyFormGUI 
	 */
	protected function initFormTitle()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('objref_title_settings'));
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton('updateTitle', $this->lng->txt('save'));
		
		// title type 
		$ttype = new ilRadioGroupInputGUI($this->lng->txt('title'), 'title_type');
		$ttype->setValue($this->object->getTitleType());
		$reuse = new ilRadioOption($this->lng->txt('objref_reuse_title'));
		$reuse->setValue(ilContainerReference::TITLE_TYPE_REUSE);
		$ttype->addOption($reuse);
		
		$custom = new ilRadioOption($this->lng->txt('objref_custom_title'));
		$custom->setValue(ilContainerReference::TITLE_TYPE_CUSTOM);
		
		// title 
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		$title->setSize(min(40, ilObject::TITLE_LENGTH));
		$title->setMaxLength(ilObject::TITLE_LENGTH);
		$title->setRequired(true);
		$title->setValue($this->object->getTitle());
		$custom->addSubItem($title);
		
		$ttype->addOption($custom);
		
		$form->addItem($ttype);
		
		return $form;
	}
	
	/**
	 * update title
	 */
	protected function updateTitleObject()
	{
		$form = $this->initFormTitle();
		if($form->checkInput())
		{
			$this->object->setTitleType($form->getInput('title_type'));
			if($form->getInput('title_type') == ilContainerReference::TITLE_TYPE_CUSTOM)
			{
				$this->object->setTitle($form->getInput('title'));
			}
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt('settings_saved'));
			$this->ctrl->redirect($this,'editTitle');
		}
		$form->setValuesByPost();
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->editTitleObject($form);
	}
	
	/**
	 * update object
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateObject()
	{
		global $ilAccess;
		
		if(!(int) $_REQUEST['target_id'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->editObject();
			return false;	
		}
		if(!$ilAccess->checkAccess('visible','',(int) $_REQUEST['target_id']))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			$this->editObject();
			return false;	
		}
		$this->checkPermission('write');

		$target_obj_id = ilObject::_lookupObjId((int) $_REQUEST['target_id']);
		$this->object->setTargetId($target_obj_id);
		$this->object->update();
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->ctrl->redirect($this,'edit');
	}
	
	
	/**
	 * show selection of containers
	 *
	 * @access protected
	 * @return
	 */
	protected function showSelection()
	{
		$this->initFormSelection();
		$this->tpl->setContent($this->form->getHTML());
		return true;
	}
	
	/**
	 * init form selection
	 *
	 * @access protected
	 * @return
	 */
	protected function initFormSelection()
	{
		if(is_object($this->form))
		{
			return true;
		}
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		$this->ctrl->setParameter($this,'new_type',$this->getReferenceType());
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->setTitle($this->lng->txt($this->getReferenceType().'_new'));
		$this->form->setTitleIcon(ilUtil::getImagePath('icon_'.$this->getReferenceType().'.svg'));
		
		// Show selection
		$select = new ilSelectInputGUI($this->lng->txt('objs_'.$this->getTargetType()),'target_id');
		$select->setOptions(self::_prepareSelection($this->existing_objects,$this->getTargetType()));
		$select->setInfo($this->lng->txt($_POST['new_type'].'_edit_info'));
		$this->form->addItem($select);
		
		$this->form->addCommandButton('save',$this->lng->txt('save'));
		$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
	}
	
	/**
	 * init form selection
	 *
	 * @access protected
	 * @return
	 */
	protected function initFormEditSelection()
	{
		if(is_object($this->form))
		{
			return true;
		}
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->setTitle($this->lng->txt($this->getReferenceType().'_edit'));
		$this->form->setTitleIcon(ilUtil::getImagePath('icon_'.$this->getReferenceType().'.svg'));
		
		// Show selection
		$select = new ilSelectInputGUI($this->lng->txt('objs_'.$this->getTargetType()),'target_id');
		$select->setValue($this->object->getTargetRefId());
		$select->setOptions(self::_prepareSelection($this->existing_objects,$this->getTargetType()));
		$select->setInfo($this->lng->txt($this->object->getType().'_edit_info'));
		$this->form->addItem($select);		
		
		$this->form->addCommandButton('update',$this->lng->txt('save'));
		#$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
	}
	

	/**
	 * get target type
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getTargetType()
	{
		return $this->target_type;
	}
	
	/**
	 * get reference type
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getReferenceType()
	{
		return $this->reference_type;
	}
	
	/**
	 * Prepare selection of target objects
	 *
	 * @access public
	 * @static
	 *
	 * @param array int array of ref ids
	 */
	public static function _prepareSelection($a_ref_ids,$a_target_type)
	{
		global $ilDB,$lng;
		
		$query = "SELECT obj_data.title obj_title,path_data.title path_title,child FROM tree ".
			"JOIN object_reference obj_ref ON child = obj_ref.ref_id ".
			"JOIN object_data obj_data ON obj_ref.obj_id = obj_data.obj_id ".
			"JOIN object_reference path_ref ON parent = path_ref.ref_id ".
			"JOIN object_data path_data ON path_ref.obj_id = path_data.obj_id ".
			"WHERE ".$ilDB->in('child',$a_ref_ids,false,'integer').' '.
			"ORDER BY obj_data.title ";
		$res = $ilDB->query($query);
		
		$options[0] = $lng->txt('obj_'.$a_target_type.'_select');
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(strlen($title = $row->obj_title) > 40)
			{
				$title = substr($title,0,40).'...';
			}
			if(strlen($path = $row->path_title) > 40)
			{
				$path = substr($path,0,40).'...';
			}
			$options[$row->child] = ($title.' ('.$lng->txt('path').': '.$path.')');
		}
		return $options ? $options : array();
	}
	
	/**
	 * Init copy from repository/search list commands
	 * @return 
	 */
	protected function initTargetSelection($a_mode = self::MODE_CREATE)
	{
		global $ilCtrl, $tree;
		
		// empty session on init
		$_SESSION['ref_repexpand'] = array();
		
		// copy opened nodes from repository explorer		
		$_SESSION['ref_repexpand'] = is_array($_SESSION['repexpand']) ? $_SESSION['repexpand'] : array();
		
		// open current position
		
		if($a_mode == self::MODE_CREATE)
		{
			$target = (int) $_GET['ref_id'];
		}
		else
		{
			$target = (int) $this->object->getTargetRefId();
		}
		
		$path = $tree->getPathId($target);
		foreach((array) $path as $node_id)
		{
			if(!in_array($node_id, $_SESSION['ref_repexpand']))
			{
				$_SESSION['ref_repexpand'][] = $node_id;
			}
		}
		
		$_SESSION['ref_mode'] = $a_mode;
		
		$this->showTargetSelectionTreeObject();
	}
	
	/**
	 * Show target selection
	 * @return 
	 */
	public function showTargetSelectionTreeObject()
	{
		global $ilTabs, $ilToolbar, $ilCtrl, $tree, $tpl, $objDefinition;
	
		include_once './Services/ContainerReference/classes/class.ilContainerSelectionExplorer.php';
		
		if($_SESSION['ref_mode'] == self::MODE_CREATE)
		{
			$ilToolbar->addButton($this->lng->txt('back'), $ilCtrl->getLinkTarget($this,'cancel'));
			$this->ctrl->setParameter($this,'new_type',$this->getReferenceType());
			$cmd = 'save';
		}
		else
		{
			$ilTabs->setTabActive('edit');
			$cmd = 'update';
		}
		$explorer = new ilContainerSelectionExplorer($this->ctrl->getLinkTarget($this,$cmd));
		
		if(isset($_GET['ref_repexpand']))
		{
			$explorer->setExpand((int) $_GET['ref_repexpand']);
		}
		else
		{
			$explorer->setExpand(ROOT_FOLDER_ID);
		}
		$explorer->setFrameTarget('_self');
		$explorer->setExpandTarget($this->ctrl->getLinkTarget($this,'showTargetSelectionTree'));
		$explorer->setTargetGet('target_id');
		$explorer->setTargetType($this->getTargetType());
		$explorer->setOutput(0);
		$this->tpl->setContent($explorer->getOutput());
	}
}
?>
