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

	/** @var string */
	protected $target_type;
	/** @var string */
	protected $reference_type;
	/** @var ilPropertyFormGUI */
	protected $form;

	/**
	 * Constructor
	 * @param
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $lng; 
		parent::__construct($a_data, $a_id,$a_call_by_reference,$a_prepare_output);

		$lng->loadLanguageModule('objref');
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 * @return bool|mixed
	 * @throws ilCtrlException
	 */
	public function executeCommand()
	{
		global $ilCtrl,$ilTabs;


		if(isset($_GET['creation_mode']) && $_GET['creation_mode'] == self::MODE_CREATE)
		{
			$this->setCreationMode(true);
		}

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		$this->prepareOutput();

		switch($next_class)
		{
			case "ilpropertyformgui":
				$form = $this->initForm($this->creation_mode ? self::MODE_CREATE : self::MODE_EDIT);
				$this->ctrl->forwardCommand($form);
			case 'ilpermissiongui':
				$ilTabs->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$ilCtrl->forwardCommand(new ilPermissionGUI($this));
				break;

			default:
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "edit";
				}
				$cmd .= "Object";
				$this->$cmd();
				break;
		}
		return true;
	}
	
	/**
	 * Add locator item
	 * @global ilLocatorGUI $ilLocator
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
		$form = $this->initForm(self::MODE_CREATE);
		$this->tpl->setContent($form->getHTML());
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
		return $this->initForm(self::MODE_CREATE);
	}

	/**
	 * @param ilObject $a_new_object
	 */
	protected function afterSave(ilObject $a_new_object)
	{		
		$target_obj_id = ilObject::_lookupObjId((int) $this->form->getInput('target_id'));
		$a_new_object->setTargetId($target_obj_id);

		$a_new_object->setTitleType($this->form->getInput('title_type'));
		if($this->form->getInput('title_type') == ilContainerReference::TITLE_TYPE_CUSTOM)
		{
			$a_new_object->setTitle($this->form->getInput('title'));
		}

		$a_new_object->update();
		
		ilUtil::sendSuccess($this->lng->txt("object_added"), true);
		$this->ctrl->setParameter($this,'ref_id',$a_new_object->getRefId());
		$this->ctrl->setParameter($this,'creation_mode',0);
		$this->ctrl->redirect($this,'firstEdit');
	}
	
	/**
	 * show edit screen without info message
	 */
	protected function firstEditObject()
	{
		$this->editObject();
	}

	public function editReferenceObject()
	{
		$this->editObject();
	}
	
	/**
	 * edit title
	 * 
	 * @param ilPropertyFormGUI $form
	 */
	public function editObject(ilPropertyFormGUI $form = null)
	{
		global $ilTabs;

		$ilTabs->setTabActive('edit');
		
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initForm();
		}
		$GLOBALS['tpl']->setContent($form->getHTML());
	}
	
	/**
	 * Init title form
	 * @param int $a_mode
	 * @return ilPropertyFormGUI 
	 */
	protected function initForm($a_mode = self::MODE_EDIT)
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		include_once './Services/ContainerReference/classes/class.ilContainerReference.php';
		$form = new ilPropertyFormGUI();

		if ($a_mode == self::MODE_CREATE) {
			$form->setTitle($this->lng->txt($this->getReferenceType(). '_new' ));

			$this->ctrl->setParameter($this, 'creation_mode', $a_mode);
			$this->ctrl->setParameter($this, 'new_type', $_REQUEST['new_type']);
		}
		else
		{
			$form->setTitle($this->lng->txt('edit'));
		}

		$form->setFormAction($this->ctrl->getFormAction($this));
		if ($a_mode == self::MODE_CREATE) {
			$form->addCommandButton('save', $this->lng->txt('create'));
		} else {
			$form->addCommandButton('update', $this->lng->txt('save'));
		}


		// title type 
		$ttype = new ilRadioGroupInputGUI($this->lng->txt('title'), 'title_type');
		if ($a_mode == self::MODE_EDIT)
		{
			$ttype->setValue($this->object->getTitleType());
		}
		else
		{
			$ttype->setValue(ilContainerReference::TITLE_TYPE_REUSE);
		}

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

		if($a_mode == self::MODE_EDIT)
		{
			$title->setValue($this->object->getTitle());
		}

		$custom->addSubItem($title);
		$ttype->addOption($custom);
		$form->addItem($ttype);

		include_once("./Services/Form/classes/class.ilRepositorySelector2InputGUI.php");
		$repo = new ilRepositorySelector2InputGUI($this->lng->txt("objref_edit_ref"), "target_id");
		$repo->setParent($this);
		$repo->setRequired(true);
		$repo->getExplorerGUI()->setSelectableTypes(array($this->getTargetType()));
		$repo->getExplorerGUI()->setTypeWhiteList(array_merge(
				array($this->getTargetType()),
				array("root", "cat", "grp", "fold", "crs"))
		);
		$repo->setInfo($this->lng->txt($this->getReferenceType().'_edit_info'));

		if($a_mode == self::MODE_EDIT)
		{
			$repo->getExplorerGUI()->setPathOpen($this->object->getTargetRefId());
			$repo->setValue($this->object->getTargetRefId());
		}

		$form->addItem($repo);
		$this->form = $form;
		return $form;
	}
	
	/**
	 * update title
	 */
	public function updateObject()
	{
		global $ilAccess;
		$form = $this->initForm();
		if($form->checkInput())
		{
			$this->object->setTitleType($form->getInput('title_type'));
			if($form->getInput('title_type') == ilContainerReference::TITLE_TYPE_CUSTOM)
			{
				$this->object->setTitle($form->getInput('title'));
			}

			if(!$ilAccess->checkAccess('visible','',(int) $form->getInput('target_id')) ||
				ilObject::_lookupType($form->getInput('target_id'), true) != $this->target_type)
			{
				ilUtil::sendFailure($this->lng->txt('permission_denied'));
				$this->editObject();
				return false;
			}
			$this->checkPermission('write');

			$target_obj_id = ilObject::_lookupObjId((int) $form->getInput('target_id'));
			$this->object->setTargetId($target_obj_id);


			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
			$this->ctrl->redirect($this,'edit');
		}
		$form->setValuesByPost();
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->editObject($form);
		return true;
	}

	/**
	 * get target type
	 *
	 * @access public
	 * @return string
	 */
	public function getTargetType()
	{
		return $this->target_type;
	}
	
	/**
	 * get reference type
	 *
	 * @access public
	 * @return string
	 */
	public function getReferenceType()
	{
		return $this->reference_type;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->obj_id;
	}

}
?>
