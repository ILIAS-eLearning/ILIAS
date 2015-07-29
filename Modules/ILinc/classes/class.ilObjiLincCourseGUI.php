<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once "./Services/Container/classes/class.ilContainerGUI.php";

/**
 * Class ilObjiLincCourseGUI
 *
 * @author Sascha Hofmann <saschahofmann@gmx.de> 
 *
 * @version $Id$
 * 
 * @extends ilObjectGUI
 * 
 * @ilCtrl_Calls ilObjiLincCourseGUI: ilObjiLincClassroomGUI, ilPermissionGUI, ilInfoScreenGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilObjiLincCourseGUI: ilPublicUserProfileGUI, ilColumnGUI
 * @ilCtrl_Calls ilObjiLincCourseGUI: ilCommonActionDispatcherGUI
 */
class ilObjiLincCourseGUI extends ilContainerGUI
{
	/**
	* Constructor
	* @access public
	*/
	public function ilObjiLincCourseGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = false)
	{
		$this->type = "icrs";
		$this->ilContainerGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		
		$this->ctrl->saveParameter($this,'ref_id');
		
		$this->lng->loadLanguageModule('ilinc');
	}
	
	/**
	* create new object form
	*
	* @access	public
	*/
	public function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST['new_type'] ? $_POST['new_type'] : $_GET['new_type'];

		if(!$rbacsystem->checkAccess('create', $_GET['ref_id'], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}		
		
		$this->initSettingsForm('create');
		return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
	}
	
	public function updateObject()
	{
		global $ilAccess;
		
		if(!$ilAccess->checkAccess('write', '', (int)$_GET['ref_id']))
		{
			$this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->MESSAGE);
		}
		
		$this->initSettingsForm('edit');
		if($this->form_gui->checkInput())
		{
			$_POST['Fobject']['title'] = $this->form_gui->getInput('title');
			$_POST['Fobject']['desc'] = $this->form_gui->getInput('desc');	
			$this->object->setTitle(ilUtil::prepareDBString($_POST['Fobject']['title']));
			$this->object->setDescription(ilUtil::prepareDBString($_POST['Fobject']['desc']));
			$this->object->update();			
			ilUtil::sendInfo($this->lng->txt('msg_obj_modified'));
			$this->form_gui->setValuesByPost();
			return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
		}
	}
	
	/**
	* save object
	* @access	public
	*/
	public function saveObject()
	{
		$this->initSettingsForm('create');
		if($this->form_gui->checkInput())
		{				
			$_POST['Fobject']['title'] = $this->form_gui->getInput('title');
			$_POST['Fobject']['desc'] = $this->form_gui->getInput('desc');
			// if everything ok
			parent::saveObject();
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
		}
	}
	
	/**
	 * @param ilObjiLincCourse $a_new_object 
	 */
	protected function afterSave(ilObject $a_new_object)
	{
		ilUtil::sendInfo($this->lng->txt('icrs_added'), true);			
		$this->redirectToRefId((int)$_GET['ref_id']);
	}
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	public function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$ilAccess;

		$this->ctrl->setParameter($this,'ref_id',$this->ref_id);

		if($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTarget('ilinc_classrooms',
				$this->ctrl->getLinkTarget($this, ''),
				array('', 'view', 'editClassroom', 'updateClassroom', 'removeClassroom')
				);
		}
					
		if($this->ilias->getSetting('ilinc_active'))
		{
			if($ilAccess->checkAccess('write', '', $this->ref_id))
			{
				$tabs_gui->addTarget('edit_properties',
					$this->ctrl->getLinkTarget($this, 'edit'), array('edit', 'update', 'save'), get_class($this));
			}
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	/**
	* canceledObject is called when an operation is canceled, method links back
	* @access	public
	*/
	public function canceledObject()
	{
		ilUtil::sendInfo($this->lng->txt("action_aborted"),true);
		$this->ctrl->redirect($this, "");
	}

	public function &executeCommand()
	{
		global $ilUser,$rbacsystem,$ilAccess,$ilErr;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case "ilconditionhandlergui":
				include_once './Services/AccessControl/classes/class.ilConditionHandlerGUI.php';

				if($_GET['item_id'])
				{
					$new_gui =& new ilConditionHandlerGUI($this,(int) $_GET['item_id']);
					$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
					$this->ctrl->forwardCommand($new_gui);
				}
				else
				{
					$new_gui =& new ilConditionHandlerGUI($this);
					$this->ctrl->forwardCommand($new_gui);
				}
				break;
				
			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setCallback($this,'addUserObject');

				// Set tabs
				$this->tabs_gui->setTabActive('members');
				$this->ctrl->setReturn($this,'members');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				$this->__setSubTabs('members');
				$this->tabs_gui->setSubTabActive('members');
				break;

			case "ilobjilincclassroomgui":
				include_once ('./Modules/ILinc/classes/class.ilObjiLincClassroomGUI.php');
				$icla_gui = new ilObjiLincClassroomGUI($_GET['class_id'],$this->ref_id);
				$ret =& $this->ctrl->forwardCommand($icla_gui);
				break;
				
			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case 'ilpublicuserprofilegui':
				require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$html = $this->ctrl->forwardCommand($profile_gui);
				$this->__setSubTabs('members');
				$this->tabs_gui->setTabActive('group_members');
				$this->tabs_gui->setSubTabActive('grp_members_gallery');
				$this->tpl->setVariable("ADM_CONTENT", $html);
				break;

			default:
				if (!$this->getCreationMode() and !$ilAccess->checkAccess('visible','',$this->object->getRefId(),'icrs'))
				{
					$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
				}
				
				
				if(!$cmd)
				{
					$cmd = 'view';
				}
				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
	}
	
	public function viewObject()
	{
		global $ilCtrl, $ilNavigationHistory, $ilAccess;

		if(!$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilias->error_obj->MESSAGE);
		}
		
		// add entry to navigation history
		if(!$this->getCreationMode() &&
			$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$ilNavigationHistory->addItem($this->object->getRefId(),
				'ilias.php?baseClass=ilRepositoryGUI&cmd=view&ref_id='.$this->object->getRefId(), 'icrs');
		}
		
		if(strtolower($_GET['baseClass']) == 'iladministrationgui')
		{
			parent::viewObject();
			return true;
		}		

		return $this->renderObject();
	}

	public function editObject()
	{
		if(!$this->ilias->getSetting('ilinc_active'))
		{
			$this->ilias->raiseError($this->lng->txt('ilinc_server_not_active'), $this->ilias->error_obj->MESSAGE);
		}
		
		$this->initSettingsForm('edit');		
		$this->getObjectValues();
		return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());	
	}
	
	protected function getObjectValues()
	{
		$this->form_gui->setValuesByArray(array(
			'title' => $this->object->getTitle(),
			'desc' => $this->object->getDescription()
		));
	}
	
	protected function initSettingsForm($a_mode = 'create')
	{
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setTableWidth('600');
		if($a_mode == 'create')
		{
			$this->form_gui->setTitle($this->lng->txt('icrs_new'));
		}		
		else
		{
			$this->form_gui->setTitle($this->lng->txt('icrs_edit'));
		}		
		
		// Title
		$text_input = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$text_input->setRequired(true);
		$this->form_gui->addItem($text_input);
		
		// Description
		$text_area = new ilTextAreaInputGUI($this->lng->txt('desc'), 'desc');
		$this->form_gui->addItem($text_area);
		
		if($this->call_by_reference)
		{
			$this->ctrl->setParameter($this, 'obj_id', $this->obj_id);
		}
		
		// save and cancel commands
		if($a_mode == 'create')
		{
			$this->ctrl->setParameter($this, 'mode', 'create');
			$this->ctrl->setParameter($this, 'new_type', 'icrs');
			
			$this->form_gui->addCommandButton('save', $this->lng->txt('icrs_add'));
			$this->form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
			$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'save'));
		}
		else
		{			
			$this->form_gui->addCommandButton('update', $this->lng->txt('save'));
			$this->form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
			$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'update'));
		}
	}

	public function isActiveAdministrationPanel()
	{
		return false;
	}

	public function addStandardContainerSubTabs($a_include_view = true)
	{
	}
	
	public function showAdministrationPanel($tpl)
	{
	}

	public static function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess('read', '', $a_target))
		{
			ilObjectGUI::_gotoRepositoryNode($a_target);
		}
		else
		{
			$ilErr->raiseError($lng->txt('msg_no_perm_read'), $ilErr->FATAL);
		}
	}
}