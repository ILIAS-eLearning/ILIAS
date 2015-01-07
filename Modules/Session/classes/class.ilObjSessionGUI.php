<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Object/classes/class.ilObjectGUI.php');
include_once('./Modules/Session/classes/class.ilObjSession.php');
include_once('./Modules/Session/classes/class.ilSessionFile.php');
include_once './Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php';

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* @ilCtrl_Calls ilObjSessionGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjSessionGUI: ilExportGUI, ilCommonActionDispatcherGUI, ilMembershipGUI
* @ilCtrl_Calls ilObjSessionGUI:  ilLearningProgressGUI
*
* @ingroup ModulesSession 
*/
class ilObjSessionGUI extends ilObjectGUI implements ilDesktopItemHandling
{


	public $lng;
	public $ctrl;
	public $tpl;
	
	protected $container_ref_id = 0;
	protected $container_obj_id = 0;
	
	protected $files = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		global $ilCtrl, $lng, $tpl;
		
		$this->type = "sess";
		parent::__construct($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		
		$this->lng = $lng;
		$this->lng->loadLanguageModule("event");
		$this->lng->loadLanguageModule('crs');
		$this->lng->loadLanguageModule('trac');
		$this->lng->loadLanguageModule('sess');
		

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
	}
	
	
	/**
	 * execute command
	 *
	 * @access public
	 * @return
	 */
	public function executeCommand()
	{
  		global $ilUser,$ilCtrl;
  
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		
		$this->prepareOutput();
  		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->checkPermission("visible");
				$this->infoScreen();	// forwards command
				break;

			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$ret = $this->ctrl->forwardCommand($perm_gui);
				break;
		
			case 'ilobjectcopygui':
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('sess');
				$this->ctrl->forwardCommand($cp);
				break;
				
			case "ilexportgui":
//				$this->prepareOutput();
				$this->tabs_gui->setTabActive("export");
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this);
				$exp_gui->addFormat("xml");
				$ret = $this->ctrl->forwardCommand($exp_gui);
//				$this->tpl->show();
				break;

			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case 'ilmembershipgui':				
				$this->ctrl->setReturn($this,'members');
				include_once './Services/Membership/classes/class.ilMembershipGUI.php';
				$mem = new ilMembershipGUI($this);
				$this->ctrl->forwardCommand($mem);
				break;
			
			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				$new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
													  $this->object->getRefId(),
													  $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
				break;
		
			default:
				if(!$cmd)
				{
					$cmd = "infoScreen";
				}
				$cmd .= "Object";
				if ($cmd != "infoScreenObject")
				{
					$this->checkPermission("read");
				}
				else
				{
					$this->checkPermission("visible");
				}
				$this->$cmd();
	
			break;
		}
		
		$this->addHeaderAction();
		
  		return true;
	}
	
	/**
	 * Get session object
	 * @return ilObjSession
	 */
	public function getCurrentObject()
	{
		return $this->object;
	}
	
    /**
     * @see ilObjectGUI::prepareOutput()
     */
    protected function prepareOutput()
    {
        parent::prepareOutput();
		
		if(!$this->getCreationMode())
		{
			$title = strlen($this->object->getTitle()) ? (': '.$this->object->getTitle()) : ''; 
			
			include_once './Modules/Session/classes/class.ilSessionAppointment.php';
			$this->tpl->setTitle(
				$this->object->getFirstAppointment()->appointmentToString().$title);
		}
    }

	/**
	 * register to session
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function registerObject()
	{
		global $ilUser;

		$this->checkPermission('read');
		
		include_once './Services/Membership/classes/class.ilParticipants.php';
		$part = ilParticipants::getInstanceByObjId($this->getCurrentObject()->getId());

		include_once './Modules/Session/classes/class.ilEventParticipants.php';
		$event_part = new ilEventParticipants($this->getCurrentObject()->getId());
		if(
			$this->getCurrentObject()->isRegistrationUserLimitEnabled() and 
			$this->getCurrentObject()->getRegistrationMaxUsers() and
			(count($event_part->getRegistered()) >= $this->getCurrentObject()->getRegistrationMaxUsers())
		)
		{
			include_once './Modules/Session/classes/class.ilSessionWaitingList.php';
			$wait = new ilSessionWaitingList($this->getCurrentObject()->getId());
			$wait->addToList($ilUser->getId());
			ilUtil::sendInfo($this->lng->txt('sess_reg_added_to_wl'),TRUE);
			$this->ctrl->redirect($this,'infoScreen');
			return TRUE;
		}
		
		
		switch($this->getCurrentObject()->getRegistrationType())
		{
			case ilMembershipRegistrationSettings::TYPE_NONE:
				$this->ctrl->redirect($this,'info');
				break;
			
			case ilMembershipRegistrationSettings::TYPE_DIRECT:
				$part->add($ilUser->getId());
				ilUtil::sendSuccess($this->lng->txt('event_registered'),true);
				$this->ctrl->redirect($this,'infoScreen');
				break;
			
			case ilMembershipRegistrationSettings::TYPE_REQUEST:
				ilUtil::sendSuccess($this->lng->txt('sess_registered_confirm'),true);
				$part->addSubscriber($ilUser->getId());
				$this->ctrl->redirect($this,'infoScreen');
				break;
		}
	}
	
	/**
	 * Called from info screen
	 * @return 
	 */
	public function joinObject()
	{
		global $ilUser;
		
		$this->checkPermission('read');
		
		include_once './Modules/Session/classes/class.ilEventParticipants.php';
			
		if(ilEventParticipants::_isRegistered($ilUser->getId(),$this->object->getId()))
		{
			$_SESSION['sess_hide_info'] = true;
			ilEventParticipants::_unregister($ilUser->getId(),$this->object->getId());
			ilUtil::sendSuccess($this->lng->txt('event_unregistered'),true);
		}
		else
		{
			ilEventParticipants::_register($ilUser->getId(),$this->object->getId());
			ilUtil::sendSuccess($this->lng->txt('event_registered'),true);
		}
		
		$this->ctrl->redirect($this,'infoScreen');
	}
	
	/**
	 * unregister from session
	 *
	 * @access public
	 * @return
	 */
	public function unregisterObject()
	{
		global $ilUser;

		include_once './Modules/Session/classes/class.ilEventParticipants.php';
		ilEventParticipants::_unregister($ilUser->getId(),$this->object->getId());
		
		include_once './Modules/Session/classes/class.ilSessionWaitingList.php';
		ilSessionWaitingList::deleteUserEntry($ilUser->getId(), $this->getCurrentObject()->getId());

		ilUtil::sendSuccess($this->lng->txt('event_unregistered'),true);
		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * goto
	 *
	 * @access public
	 * @param
	 * @return
	 * @static
	 */
	public static function _goto($a_target)
	{
		global $ilAccess,$ilErr,$lng;
		
		if($ilAccess->checkAccess('visible', "", $a_target))
		{
			ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}
	
    /**
     * @see ilDesktopItemHandling::addToDesk()
     */
    public function addToDeskObject()
    {
	 	include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
	 	ilDesktopItemGUI::addToDesktop();
		$this->infoScreenObject();
    }
    
    /**
     * @see ilDesktopItemHandling::removeFromDesk()
     */
    public function removeFromDeskObject()
    {
	 	include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
	 	ilDesktopItemGUI::removeFromDesktop();
		$this->infoScreenObject();
    }
	
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	public function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}

	/**
	* Modify Item ListGUI for presentation in container
	*/
	function modifyItemGUI($a_item_list_gui,$a_item_data, $a_show_path)
	{
		global $tree;

		// if folder is in a course, modify item list gui according to course requirements
		if ($course_ref_id = $tree->checkForParentType($this->object->getRefId(),'crs'))
		{			
			// #10611
			include_once "Services/Object/classes/class.ilObjectActivation.php";
			ilObjectActivation::addListGUIActivationProperty($a_item_list_gui, $a_item_data);		
						
			include_once("./Modules/Course/classes/class.ilObjCourse.php");
			include_once("./Modules/Course/classes/class.ilObjCourseGUI.php");			
			$course_obj_id = ilObject::_lookupObjId($course_ref_id);
			ilObjCourseGUI::_modifyItemGUI(
				$a_item_list_gui,
				get_class($this), 
				$a_item_data, 
				$a_show_path,
				ilObjCourse::_lookupAboStatus($course_obj_id),
				$course_ref_id, 
				$course_obj_id,
				$this->object->getRefId());
		}
	}
	
	/**
	 * show join request
	 */
	protected function showJoinRequestButton()
	{
		global $ilToolbar, $ilUser;
		
		if(!$this->getCurrentObject()->enabledRegistration())
		{
			return FALSE;
		}
		
		include_once './Modules/Session/classes/class.ilSessionWaitingList.php';
		
		include_once './Services/Membership/classes/class.ilParticipants.php';
		$part = ilParticipants::getInstanceByObjId($this->getCurrentObject()->getId());
		
		include_once './Modules/Session/classes/class.ilEventParticipants.php';
		if(ilEventParticipants::_isRegistered($ilUser->getId(), $this->getCurrentObject()->getId()))
		{
			$ilToolbar->addFormButton($this->lng->txt('event_unregister'),'unregister');
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			return TRUE;
		}
		elseif($part->isSubscriber($ilUser->getId()))
		{
			$ilToolbar->addFormButton($this->lng->txt('event_unregister'),'unregister');
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			return TRUE;
		}
		elseif(ilSessionWaitingList::_isOnList($ilUser->getId(), $this->getCurrentObject()->getId()))
		{
			$ilToolbar->addFormButton($this->lng->txt('leave_waiting_list'),'unregister');
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			return TRUE;
		}
		
		$event_part = new ilEventParticipants($this->getCurrentObject()->getId());

		if(
			$this->getCurrentObject()->isRegistrationUserLimitEnabled() and 
			$this->getCurrentObject()->getRegistrationMaxUsers() and
			(count($event_part->getRegistered()) >= $this->getCurrentObject()->getRegistrationMaxUsers())
		)
		{
			if($this->getCurrentObject()->isRegistrationWaitingListEnabled())
			{
				ilUtil::sendInfo($this->lng->txt('sess_reg_max_users_exceeded_wl'));
				$ilToolbar->addFormButton($this->lng->txt('mem_add_to_wl'),'register');
				$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
				return TRUE;
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt('sess_reg_max_users_exceeded'));
				return TRUE;
			}
		}
		else
		{
			if(!isset($_SESSION['sess_hide_info']))
			{
				ilUtil::sendInfo($this->lng->txt('sess_join_info'));
				$ilToolbar->addFormButton($this->lng->txt('join_session'),'register', '', true);
				$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
				return TRUE;
			}
		}
	}
		
		

	/**
	 * info screen
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	public function infoScreen()
	{
		global $ilAccess, $ilUser,$ilCtrl,$tree,$ilToolbar;

		$this->checkPermission('visible');
		$this->tabs_gui->setTabActive('info_short');

		$appointment_obj = $this->object->getFirstAppointment();

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$this->showJoinRequestButton();
		
		// Session information
		if(strlen($this->object->getLocation()) or strlen($this->object->getDetails()))
		{
			$info->addSection($this->lng->txt('event_section_information'));
		}
		if(strlen($location = $this->object->getLocation()))
		{
			$info->addProperty($this->lng->txt('event_location'),
							   nl2br($this->object->getLocation()));
		}
		if(strlen($this->object->getDetails()))
		{
			$info->addProperty($this->lng->txt('event_details_workflow'),
							   nl2br($this->object->getDetails()));
		}
		
		// Tutor information
		if($this->object->hasTutorSettings())
		{
			$info->addSection($this->lng->txt('event_tutor_data'));
			if(strlen($fullname = $this->object->getName()))
			{
				$info->addProperty($this->lng->txt('event_lecturer'),
								   $fullname);
			}
			if(strlen($email = $this->object->getEmail()))
			{
				$info->addProperty($this->lng->txt('tutor_email'),
								   $email);
			}
			if(strlen($phone = $this->object->getPhone()))
			{
				$info->addProperty($this->lng->txt('tutor_phone'),
								   $phone);
			}
		}
		
				
		$html = '';		
			
		include_once './Services/Object/classes/class.ilObjectActivation.php';		
		include_once './Services/Container/classes/class.ilContainerSorting.php';	
		include_once './Modules/Session/classes/class.ilSessionObjectListGUIFactory.php';
		
		$eventItems = ilObjectActivation::getItemsByEvent($this->object->getId());			
		$parent_id = $tree->getParentId($this->object->getRefId());
		$parent_id = ilObject::_lookupObjId($parent_id);				
		$eventItems = ilContainerSorting::_getInstance($parent_id)->sortSubItems(
			'sess',
			$this->object->getId(),
			$eventItems
		);			
		
		foreach($eventItems as $item)
		{						
			$list_gui = ilSessionObjectListGUIFactory::factory($item['type']);
			$list_gui->setContainerObject($this);
			
			$this->modifyItemGUI($list_gui, $item, false);
			
			$html .= $list_gui->getListItemHTML(
				$item['ref_id'],
				$item['obj_id'],
				$item['title'],
				$item['description']
			);
		}
		
		if(strlen($html))
		{
			$info->addSection($this->lng->txt('event_materials'));
			$info->addProperty(
				'&nbsp;',
				$html);
		}

		// forward the command
		$this->ctrl->forwardCommand($info);
		
		// store read event
		require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
		ilChangeEvent::_recordReadEvent($this->object->getType(), $this->object->getRefId(),
			$this->object->getId(), $ilUser->getId());
	}
	
	/**
	 * send file
	 *
	 * @access public
	 */
	public function sendFileObject()
	{
		$file = new ilSessionFile((int) $_GET['file_id']);
		
		ilUtil::deliverFile($file->getAbsolutePath(),$file->getFileName(),$file->getFileType());
		return true;
	}
	
	protected function initCreateForm($a_new_type)
	{
		if(!is_object($this->object))
		{
			$this->object = new ilObjSession();
		}
		$this->initForm('create');
		return $this->form;
	}

	/**
	 * Save and assign sessoin materials
	 *  
	 * @access protected 
	 */
	public function saveAndAssignMaterialsObject()
	{
		global $ilLog;
		
		$this->saveObject(false);
		
		/*
		$this->ctrl->setParameter($this,'ref_id',$this->object->getRefId());
		$target = $this->ctrl->getLinkTarget($this,'materials');
		$target = str_replace('new_type=','nt=',$target);
		*/
		$this->ctrl->setParameter($this,'ref_id',$this->object->getRefId());
		$this->ctrl->redirect($this,'materials');
	}
		
	/**
	 * save object
	 *
	 * @access protected
	 * @param bool	$a_redirect_on_success	Redirect to repository after success.
	 * @return
	 */
	public function saveObject($a_redirect_on_success = true)
	{
		global $ilErr,$ilUser;
		
		$this->object = new ilObjSession();

		$this->load();
		$this->loadRecurrenceSettings();
		$this->initForm('create');
		
		$ilErr->setMessage('');
		if(!$this->form->checkInput())		{
			$ilErr->setMessage($this->lng->txt('err_check_input'));
		}

		$this->object->validate();
		$this->object->getFirstAppointment()->validate();

		if(strlen($ilErr->getMessage()))
		{
			ilUtil::sendFailure($ilErr->getMessage().$_GET['ref_id']);
			$this->createObject();
			return false;
		}
		// Create session
		$this->object->create();
		$this->object->createReference();
		$this->object->putInTree($_GET["ref_id"]);
		$this->object->setPermissions($_GET["ref_id"]);
				
		// #14547 - active is default
		if(!$this->form->getInput("lp_preset"))
		{
			include_once "Services/Tracking/classes/class.ilLPObjSettings.php";
			$lp_obj_settings = new ilLPObjSettings($this->object->getId());
			$lp_obj_settings->setMode(ilLPObjSettings::LP_MODE_DEACTIVATED);	
			$lp_obj_settings->update(false);
		}
		
		// create appointment
		$this->object->getFirstAppointment()->setSessionId($this->object->getId());
		$this->object->getFirstAppointment()->create();

		$this->handleFileUpload();
		
		$this->createRecurringSessions($this->form->getInput("lp_preset"));

		if($a_redirect_on_success) 
		{
			ilUtil::sendInfo($this->lng->txt('event_add_new_event'),true);
			$this->ctrl->returnToParent($this);
		}
		
		return true;
	
	}
	
	public function handleFileUpload()
	{
		global $tree;
		
		include_once './Modules/Session/classes/class.ilEventItems.php';
		$ev = new ilEventItems($this->object->getId());
		$items = $ev->getItems();

		$counter = 0;
		while(true)
		{
			if(!isset($_FILES['files']['name'][$counter]))
			{
				break;
			}
			if(!strlen($_FILES['files']['name'][$counter]))
			{
				$counter++;
				continue;
			}
			
			include_once './Modules/File/classes/class.ilObjFile.php';
			$file = new ilObjFile();
			$file->setTitle(ilUtil::stripSlashes($_FILES['files']['name'][$counter]));
			$file->setDescription('');
			$file->setFileName(ilUtil::stripSlashes($_FILES['files']['name'][$counter]));
			$file->setFileType($_FILES['files']['type'][$counter]);
			$file->setFileSize($_FILES['files']['size'][$counter]);
			$file->create();
			$new_ref_id = $file->createReference();
			$file->putInTree($tree->getParentId($this->object->getRefId()));
			$file->setPermissions($tree->getParentId($this->object->getRefId()));
			$file->createDirectory();
			$file->getUploadFile(
				$_FILES['files']['tmp_name'][$counter],
				$_FILES['files']['name'][$counter]
			);
			
			$items[] = $new_ref_id;
			$counter++;
			
		}
		
		$ev->setItems($items);
		$ev->update();			
	}
	
	
	
	/**
	 * create recurring sessions
	 *
	 * @access protected
	 * @param bool $a_activate_lp
	 * @return
	 */
	protected function createRecurringSessions($a_activate_lp = true)
	{
		global $tree;
		
		if(!$this->rec->getFrequenceType())
		{
			return true;
		}
		include_once('./Services/Calendar/classes/class.ilCalendarRecurrenceCalculator.php');
		$calc = new ilCalendarRecurrenceCalculator($this->object->getFirstAppointment(),$this->rec);
		
		$period_start = clone $this->object->getFirstAppointment()->getStart();
		
		
		$period_end = clone $this->object->getFirstAppointment()->getStart();
		$period_end->increment(IL_CAL_YEAR,5);
		$date_list = $calc->calculateDateList($period_start,$period_end);
		
		$period_diff = $this->object->getFirstAppointment()->getEnd()->get(IL_CAL_UNIX) - 
			$this->object->getFirstAppointment()->getStart()->get(IL_CAL_UNIX);
		$parent_id = $tree->getParentId($this->object->getRefId());
		
		include_once './Modules/Session/classes/class.ilEventItems.php';
		$evi = new ilEventItems($this->object->getId());
		$eitems = $evi->getItems(); 
		
		$counter = 0;
		foreach($date_list->get() as $date)
		{
			if(!$counter++)
			{
				continue;
			}
			
			$new_obj = $this->object->cloneObject($parent_id);
			$new_obj->read();
			$new_obj->getFirstAppointment()->setStartingTime($date->get(IL_CAL_UNIX));
			$new_obj->getFirstAppointment()->setEndingTime($date->get(IL_CAL_UNIX) + $period_diff);
			$new_obj->getFirstAppointment()->update();
			$new_obj->update();
			
			// #14547 - active is default
			if(!$a_activate_lp)
			{
				include_once "Services/Tracking/classes/class.ilLPObjSettings.php";
				$lp_obj_settings = new ilLPObjSettings($new_obj->getId());
				$lp_obj_settings->setMode(ilLPObjSettings::LP_MODE_DEACTIVATED);	
				$lp_obj_settings->update(false);
			}
			
			$new_evi = new ilEventItems($new_obj->getId());
			$new_evi->setItems($eitems);
			$new_evi->update();
		}	
	}
	
	
	/**
	 * edit object
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	public function editObject()
	{
		$this->tabs_gui->setTabActive('settings');
		
		$this->initForm('edit');
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.sess_edit.html','Modules/Session');
		$this->tpl->setVariable('EVENT_EDIT_TABLE',$this->form->getHTML());
		
		if(!count($this->object->getFiles()))		
		{
			return true;
		}
		$rows = array();
		foreach($this->object->getFiles() as $file)
		{
			$table_data['id'] = $file->getFileId();
			$table_data['filename'] = $file->getFileName();
			$table_data['filetype'] = $file->getFileType();
			$table_data['filesize'] = $file->getFileSize();
			
			$rows[] = $table_data; 
		}
		
		include_once("./Modules/Session/classes/class.ilSessionFileTableGUI.php");
		$table_gui = new ilSessionFileTableGUI($this, "edit");
		$table_gui->setTitle($this->lng->txt("event_files"));
		$table_gui->setData($rows);
		$table_gui->addCommandButton("cancel", $this->lng->txt("cancel"));
		$table_gui->addMultiCommand("confirmDeleteFiles", $this->lng->txt("delete"));
		$table_gui->setSelectAllCheckbox("file_id");
		$this->tpl->setVariable('EVENT_FILE_TABLE',$table_gui->getHTML());

		return true;
	}
	
	/**
	 * update object
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	public function updateObject()
	{
		global $ilErr;

		$this->load();
		$this->initForm('edit');
		
		$ilErr->setMessage('');
		if(!$this->form->checkInput())
		{
			$ilErr->setMessage($this->lng->txt('err_check_input'));
		}
		$this->object->validate();
		$this->object->getFirstAppointment()->validate();

		if(strlen($ilErr->getMessage()))
		{
			ilUtil::sendFailure($ilErr->getMessage());
			$this->editObject();
			return false;
		}
		// Update event
		$this->object->update();
		$this->object->getFirstAppointment()->update();
		
		$this->handleFileUpload();
		
		ilUtil::sendSuccess($this->lng->txt('event_updated'),true);
		$this->ctrl->redirect($this,'edit');
		#$this->object->initFiles();
		#$this->editObject();
		return true;
	}
	
	/**
	 * confirm delete files
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function confirmDeleteFilesObject()
	{
		$this->tabs_gui->setTabActive('settings');

		if(!count($_POST['file_id']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->editObject();
			return false;
		}
		
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteFiles"));
		$c_gui->setHeaderText($this->lng->txt("info_delete_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "edit");
		$c_gui->setConfirm($this->lng->txt("confirm"), "deleteFiles");

		// add items to delete
		foreach($_POST["file_id"] as $file_id)
		{
			$file = new ilSessionFile($file_id);
			if($file->getSessionId() != $this->object->getEventId())
			{
				ilUtil::sendFailure($this->lng->txt('select_one'));
				$this->edit();
				return false;
			}
			$c_gui->addItem("file_id[]", $file_id, $file->getFileName());
		}
		
		$this->tpl->setContent($c_gui->getHTML());
		return true;	
	}
	
	/**
	 * delete files
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function deleteFilesObject()
	{
		if(!count($_POST['file_id']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->editObject();
			return false;
		}
		foreach($_POST['file_id'] as $id)
		{
			$file = new ilSessionFile($id);
			$file->delete();
		}
		$this->object->initFiles();
		$this->editObject();
		return true;	
	}
	
	protected function initContainer($a_init_participants = false)
	{
		global $tree;
		
		$is_course = $is_group = false;
		
		// #13178
		$this->container_ref_id = $tree->checkForParentType($this->object->getRefId(),'grp');
		if($this->container_ref_id)
		{
			$is_group = true;
		}
		if(!$this->container_ref_id)
		{
			$this->container_ref_id = $tree->checkForParentType($this->object->getRefId(),'crs');
			if($this->container_ref_id)
			{
				$is_course = true;
			}
		}		
		if(!$this->container_ref_id)
		{
			ilUtil::sendFailure('No container object found. Aborting');
			return true;
		}
		$this->container_obj_id = ilObject::_lookupObjId($this->container_ref_id);
		
		if($a_init_participants && $this->container_obj_id)
		{
			if($is_course)
			{
				include_once './Modules/Course/classes/class.ilCourseParticipants.php';
				return ilCourseParticipants::_getInstanceByObjId($this->container_obj_id);
			}
			else if($is_group)
			{
				include_once './Modules/Group/classes/class.ilGroupParticipants.php';
				return ilGroupParticipants::_getInstanceByObjId($this->container_obj_id);
			}
		}
		
	}
	
	/**
	 * show material assignment
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	public function materialsObject()
	{
		global $tree, $objDefinition;

		$this->tabs_gui->setTabActive('crs_materials');
		
		// #11337 - support ANY parent container (crs, grp, fld)
		$parent_ref_id = $tree->getParentId($this->object->getRefId());
		
		include_once "Services/Object/classes/class.ilObjectAddNewItemGUI.php";
		$gui = new ilObjectAddNewItemGUI($parent_ref_id);
		$gui->setDisabledObjectTypes(array("itgr", "sess"));
		$gui->setAfterCreationCallback($this->ref_id);
		$gui->render();		

		include_once 'Modules/Session/classes/class.ilEventItems.php';
		$this->event_items = new ilEventItems($this->object->getId());

		include_once 'Modules/Session/classes/class.ilSessionMaterialsTableGUI.php';
		$tbl = new ilSessionMaterialsTableGUI($this, "materials");
		$tbl->setTitle($this->lng->txt("event_assign_materials_table"));
		$tbl->setDescription($this->lng->txt('event_assign_materials_info'));
		$tbl->setId("sess_materials_". $this->object->getId());

		$tbl->setMaterialItems($this->event_items->getItems());
		$tbl->setContainerRefId($this->getContainerRefId());
		$tbl->getDataFromDb();

		$this->tpl->setContent($tbl->getHTML());
	}
	
	/**
	 * save material assignment
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function saveMaterialsObject()
	{
		include_once './Modules/Session/classes/class.ilEventItems.php';
		
		$this->event_items = new ilEventItems($this->object->getId());
		$this->event_items->setItems(is_array($_POST['items']) ? $_POST['items'] : array());
		$this->event_items->update();

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->materialsObject();
	}
	
	/**
	 * Show participants table
	 * @return void 
	 */
	protected function membersObject()
	{
		global $tree,$ilUser;
		
		$this->checkPermission('write');
		$this->tabs_gui->setTabActive('event_edit_members');
		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.sess_members.html', 'Modules/Session');
		
		include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		$toolbar = new ilToolbarGUI();		
		$toolbar->addButton($this->lng->txt('sess_gen_attendance_list'), 
			$this->ctrl->getLinkTarget($this,'attendanceList'));		
		
		$this->tpl->setVariable('ACTION_BUTTONS',$toolbar->getHTML());

		$members_obj = $this->initContainer(true);
				
		include_once './Modules/Session/classes/class.ilEventParticipants.php';	
		
		// Save hide/show table settings		
		$this->setShowHidePrefs();
		
		// Waiting list table
		include_once('./Modules/Session/classes/class.ilSessionWaitingList.php');
		$waiting_list = new ilSessionWaitingList($this->object->getId());
		if(count($wait = $waiting_list->getAllUsers()))
		{
			include_once('./Services/Membership/classes/class.ilWaitingListTableGUI.php');
			if($ilUser->getPref('sess_wait_hide'))
			{
				$table_gui = new ilWaitingListTableGUI($this,$waiting_list,false);
				$this->ctrl->setParameter($this,'wait_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilWaitingListTableGUI($this,$waiting_list,true);
				$this->ctrl->setParameter($this,'wait_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setUsers($wait);
			$table_gui->setTitle($this->lng->txt('grp_header_waiting_list'),'icon_usr.svg',$this->lng->txt('group_new_registrations'));
			$this->tpl->setVariable('TABLE_WAIT',$table_gui->getHTML());
		}
		
		// subscribers
		// Subscriber table
		include_once './Services/Membership/classes/class.ilParticipants.php';
		$part = ilParticipants::getInstanceByObjId($this->object->getId());
		if($part->getSubscribers())
		{
			include_once('./Services/Membership/classes/class.ilSubscriberTableGUI.php');
			if($ilUser->getPref('grp_subscriber_hide'))
			{
				$table_gui = new ilSubscriberTableGUI($this,false, false);
				$this->ctrl->setParameter($this,'subscriber_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilSubscriberTableGUI($this,true, false);
				$this->ctrl->setParameter($this,'subscriber_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->readSubscriberData();
			$table_gui->setTitle($this->lng->txt('group_new_registrations'),'icon_usr.svg',$this->lng->txt('group_new_registrations'));
			$this->tpl->setVariable('TABLE_SUB',$table_gui->getHTML());
		}
		
		
		// Admins
		if(count($admins = $members_obj->getAdmins()))
		{
			include_once('./Modules/Session/classes/class.ilSessionParticipantsTableGUI.php');
			if($ilUser->getPref('sess_admin_hide'))
			{
				$table = new ilSessionParticipantsTableGUI($this,ilSessionParticipantsTableGUI::TYPE_ADMIN,false);
				$this->ctrl->setParameter($this,'admin_hide',0);
				$table->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table = new ilSessionParticipantsTableGUI($this,ilSessionParticipantsTableGUI::TYPE_ADMIN,true);
				$this->ctrl->setParameter($this,'admin_hide',1);
				$table->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}

			$table->addCommandButton('updateMembers',$this->lng->txt('save'));
			$table->setTitle($this->lng->txt('event_tbl_admins'),'icon_usr.svg',$this->lng->txt('event_tbl_admins'));
			$table->enableRegistration($this->object->enabledRegistration());
			$table->setParticipants($admins);
			$table->parse();
			$this->tpl->setVariable('ADMINS',$table->getHTML());
		}
		
		// Tutors
		if(count($tutors = $members_obj->getTutors()))
		{
			include_once('./Modules/Session/classes/class.ilSessionParticipantsTableGUI.php');
			if($ilUser->getPref('sess_tutor_hide'))
			{
				$table = new ilSessionParticipantsTableGUI($this,ilSessionParticipantsTableGUI::TYPE_TUTOR,false);
				$this->ctrl->setParameter($this,'tutor_hide',0);
				$table->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table = new ilSessionParticipantsTableGUI($this,ilSessionParticipantsTableGUI::TYPE_TUTOR,true);
				$this->ctrl->setParameter($this,'tutor_hide',1);
				$table->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
			$table->addCommandButton('updateMembers',$this->lng->txt('save'));
			$table->setTitle($this->lng->txt('event_tbl_tutors'),'icon_usr.svg',$this->lng->txt('event_tbl_admins'));
			$table->enableRegistration($this->object->enabledRegistration());
			$table->setParticipants($tutors);
			$table->parse();
			$this->tpl->setVariable('TUTORS',$table->getHTML());
		}

		// Members
		if(count($members = $members_obj->getMembers()))
		{
			include_once('./Modules/Session/classes/class.ilSessionParticipantsTableGUI.php');
			if($ilUser->getPref('sess_member_hide'))
			{
				$table = new ilSessionParticipantsTableGUI($this,ilSessionParticipantsTableGUI::TYPE_MEMBER,false);
				$this->ctrl->setParameter($this,'member_hide',0);
				$table->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table = new ilSessionParticipantsTableGUI($this,ilSessionParticipantsTableGUI::TYPE_MEMBER,true);
				$this->ctrl->setParameter($this,'member_hide',1);
				$table->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
			$table->addCommandButton('updateMembers',$this->lng->txt('save'));
			$table->setTitle($this->lng->txt('event_tbl_members'),'icon_usr.svg',$this->lng->txt('event_tbl_admins'));
			$table->enableRegistration($this->object->enabledRegistration());
			$table->setParticipants($members);
			$table->parse();
			$this->tpl->setVariable('MEMBERS',$table->getHTML());
		}
		
		
		$GLOBALS['lng']->loadLanguageModule('mmbr');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TXT_SELECTED_USER',$this->lng->txt('mmbr_selected_users'));
		$this->tpl->setVariable('BTN_FOOTER_MAIL',$this->lng->txt('mmbr_btn_mail_selected_users'));
		$this->tpl->setVariable('ARROW_DOWN',ilUtil::getImagePath('arrow_downright.svg'));
	}
	
	/**
	 * set preferences (show/hide tabel content)
	 *
	 * @access public
	 * @return
	 */
	public function setShowHidePrefs()
	{
		global $ilUser;
		
		if(isset($_GET['admin_hide']))
		{
			$ilUser->writePref('sess_admin_hide',(int) $_GET['admin_hide']);
		}
		if(isset($_GET['tutor_hide']))
		{
			$ilUser->writePref('sess_tutor_hide',(int) $_GET['tutor_hide']);
		}
		if(isset($_GET['member_hide']))
		{
			$ilUser->writePref('sess_member_hide',(int) $_GET['member_hide']);
		}
	}
	
	/**
	 * update participants
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateMembersObject()
	{
		global $tree;
		
		$this->checkPermission('write');

		

		$this->initContainer();
		
		$_POST['participants'] = is_array($_POST['participants']) ? $_POST['participants'] : array();
		$_POST['registered'] = is_array($_POST['registered']) ? $_POST['registered'] : array();

		include_once 'Modules/Session/classes/class.ilEventParticipants.php';

		$visible = $_POST['visible_participants'] ? $_POST['visible_participants'] : array();
		foreach($visible as $user)
		{
			$part = new ilEventParticipants($this->object->getId());
			$part->setUserId($user);
			$part->setMark(ilUtil::stripSlashes($_POST['mark'][$user]));
			$part->setComment(ilUtil::stripSlashes($_POST['comment'][$user]));
			$part->setParticipated(isset($_POST['participants'][$user]) ? true : false);
			$part->setRegistered(isset($_POST['registered'][$user]) ? true : false);
			$part->updateUser();
		}
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->membersObject();
	}
	
	/**
	 * show attendance list selection
	 *
	 * @access public
	 * @return
	 */
	public function attendanceListObject()
	{
		global $tpl,$ilTabs;
		
		$this->checkPermission('write');		
		$ilTabs->setTabActive('event_edit_members');	
		
		$list = $this->initAttendanceList();
		$form = $list->initForm('printAttendanceList');
		$tpl->setContent($form->getHTML());		
	}
	
	/**
	 * Init attendance list object
	 * 
	 * @return ilAttendanceList 
	 */
	protected function initAttendanceList()
	{
		$members_obj = $this->initContainer(true);
		
		include_once 'Services/Membership/classes/class.ilAttendanceList.php';
		$list = new ilAttendanceList($this, $members_obj);	
		$list->setId('sessattlst');
		
		$event_app = $this->object->getFirstAppointment();				 
		ilDatePresentation::setUseRelativeDates(false);
		$desc = ilDatePresentation::formatPeriod($event_app->getStart(),$event_app->getEnd());
		ilDatePresentation::setUseRelativeDates(true);		
		$desc .= " ".$this->object->getTitle();	
		$list->setTitle($this->lng->txt('sess_attendance_list'), $desc);		
		
		$list->addPreset('mark', $this->lng->txt('trac_mark'), true);
		$list->addPreset('comment', $this->lng->txt('trac_comment'), true);		
		if($this->object->enabledRegistration())
		{
			$list->addPreset('registered', $this->lng->txt('event_tbl_registered'), true);			
		}	
		$list->addPreset('participated', $this->lng->txt('event_tbl_participated'), true);		
		$list->addBlank($this->lng->txt('sess_signature'));
		
		$list->addUserFilter('registered', $this->lng->txt('event_list_registered_only'));
		
		return $list;
	}
		
	/**
	 * print attendance list
	 *
	 * @access protected
	 */
	protected function printAttendanceListObject()
	{		
		$this->checkPermission('write');
													
		$list = $this->initAttendanceList();		
		$list->initFromForm();					
		$list->setCallback(array($this, 'getAttendanceListUserData'));
		
		include_once 'Modules/Session/classes/class.ilEventParticipants.php';
		$this->event_part = new ilEventParticipants($this->object->getId());	
		
		echo $list->getFullscreenHTML();
		exit();
	}	
	
	/**
	 * Get user data for attendance list
	 * @param int $a_user_id
	 * @param bool $a_is_admin
	 * @param bool $a_is_tutor
	 * @param bool $a_is_member
	 * @param array $a_filters
	 * @return array 
	 */
	public function getAttendanceListUserData($a_user_id, $a_filters)
	{			
		$data = $this->event_part->getUser($a_user_id);	
		
		if($a_filters && $a_filters["registered"] && !$data["registered"])
		{
			return;
		}
		
		$data['registered'] = $data['registered'] ? 
			$this->lng->txt('yes') : 
			$this->lng->txt('no');
		$data['participated'] = $data['participated'] ? 
			$this->lng->txt('yes') : 
			$this->lng->txt('no');		
		
		return $data;
	}
	
	/**
	 * list sessions of all user
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function eventsListObject()
	{
		global $ilErr,$ilAccess, $ilUser,$tree;

		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.sess_list.html','Modules/Session');
		$this->__showButton($this->ctrl->getLinkTarget($this,'exportCSV'),$this->lng->txt('event_csv_export'));
				
		include_once 'Modules/Session/classes/class.ilEventParticipants.php';
		
		$this->tpl->addBlockfile("EVENTS_TABLE","events_table", "tpl.table.html");
		$this->tpl->addBlockfile('TBL_CONTENT','tbl_content','tpl.sess_list_row.html','Modules/Session');
		
		$members_obj = $this->initContainer(true);
		$members = $members_obj->getParticipants();
		$members = ilUtil::_sortIds($members,'usr_data','lastname','usr_id');		
		
		// Table 
		$tbl = new ilTableGUI();
		$tbl->setTitle($this->lng->txt("event_overview"),
					   'icon_usr.svg',
					   $this->lng->txt('obj_usr'));
		$this->ctrl->setParameter($this,'offset',(int) $_GET['offset']);	
		
		$course_ref_id = $tree->checkForParentType($this->object->getRefId(),'crs');
		$events = array();
		foreach($tree->getSubtree($tree->getNodeData($course_ref_id),false,'sess') as $event_id)
		{
			$tmp_event = ilObjectFactory::getInstanceByRefId($event_id,false);
			if(!is_object($tmp_event) or $tmp_event->getType() != 'sess') 
			{
				continue;
			}
			$events[] = $tmp_event;
		}
		
		$headerNames = array();
		$headerVars = array();
		$colWidth = array();
		
		$headerNames[] = $this->lng->txt('name');		
		$headerVars[] = "name";		
		$colWidth[] = '20%';		
					
		for ($i = 1; $i <= count($events); $i++)
		{
			$headerNames[] = $i;
			$headerVars[] = "event_".$i;
			$colWidth[] = 80/count($events)."%";	
		}		
		
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tbl->setHeaderNames($headerNames);
		$tbl->setHeaderVars($headerVars, $this->ctrl->getParameterArray($this,'eventsList'));
		$tbl->setColumnWidth($colWidth);		

		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setOffset($_GET["offset"]);				
		$tbl->setLimit($ilUser->getPref("hits_per_page"));
		$tbl->setMaxCount(count($members));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		
		$sliced_users = array_slice($members,$_GET['offset'],$_SESSION['tbl_limit']);
		$tbl->disable('sort');
		$tbl->render();
		
		$counter = 0;
		foreach($sliced_users as $user_id)
		{			
			foreach($events as $event_obj)
			{								
				$this->tpl->setCurrentBlock("eventcols");
							
				$event_part = new ilEventParticipants($this->object->getId());														
										
				{			
					$this->tpl->setVariable("IMAGE_PARTICIPATED", $event_part->hasParticipated($user_id) ? 
											ilUtil::getImagePath('icon_ok.svg') :
											ilUtil::getImagePath('icon_not_ok.svg'));
					
					$this->tpl->setVariable("PARTICIPATED", $event_part->hasParticipated($user_id) ?
										$this->lng->txt('event_participated') :
										$this->lng->txt('event_not_participated'));
				}						
				
				$this->tpl->parseCurrentBlock();				
			}			
			
			$this->tpl->setCurrentBlock("tbl_content");
			$name = ilObjUser::_lookupName($user_id);
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$this->tpl->setVariable("LASTNAME",$name['lastname']);
			$this->tpl->setVariable("FIRSTNAME",$name['firstname']);
			$this->tpl->setVariable("LOGIN",ilObjUser::_lookupLogin($user_id));				
			$this->tpl->parseCurrentBlock();			
		}		
		
		$this->tpl->setVariable("HEAD_TXT_LEGEND", $this->lng->txt("legend"));		
		$this->tpl->setVariable("HEAD_TXT_DIGIT", $this->lng->txt("event_digit"));
		$this->tpl->setVariable("HEAD_TXT_EVENT", $this->lng->txt("event"));
		$this->tpl->setVariable("HEAD_TXT_LOCATION", $this->lng->txt("event_location"));
		$this->tpl->setVariable("HEAD_TXT_DATE_TIME",$this->lng->txt("event_date_time"));
		$i = 1;
		foreach($events as $event_obj)
		{
			$this->tpl->setCurrentBlock("legend_loop");
			$this->tpl->setVariable("LEGEND_CSS_ROW",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$this->tpl->setVariable("LEGEND_DIGIT", $i++);
			$this->tpl->setVariable("LEGEND_EVENT_TITLE", $event_obj->getTitle());
			$this->tpl->setVariable("LEGEND_EVENT_DESCRIPTION", $event_obj->getDescription());	
			$this->tpl->setVariable("LEGEND_EVENT_LOCATION", $event_obj->getLocation());
			$this->tpl->setVariable("LEGEND_EVENT_APPOINTMENT", $event_obj->getFirstAppointment()->appointmentToString());		
			$this->tpl->parseCurrentBlock();
		}
	
	}

	/**
	 * Init Form 
	 *
	 * @access protected
	 */
	protected function initForm($a_mode)
	{
		global $ilUser;
		
		if(is_object($this->form))
		{
			return true;
		}
	
		$this->lng->loadLanguageModule('dateplaner');
	
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initDomEvent();

		$this->form = new ilPropertyFormGUI();
		$this->form->setMultipart(true);
		$this->form->setTableWidth('600px');
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->setMultipart(true);
		
		/*
		$full = new ilCheckboxInputGUI('','fulltime');
		$full->setChecked($this->object->getFirstAppointment()->enabledFulltime() ? true : false);
		$full->setOptionTitle($this->lng->txt('event_fulltime_info'));
		$full->setAdditionalAttributes('onchange="ilToggleSessionTime(this);"');
		#$this->form->addItem($full);
		*/
		
		$this->lng->loadLanguageModule('dateplaner');
		include_once './Services/Form/classes/class.ilDateDurationInputGUI.php';
		#$this->tpl->addJavaScript('./Modules/Session/js/toggle_session_time.js');
		$this->tpl->addJavaScript('./Services/Form/js/date_duration.js');
		$dur = new ilDateDurationInputGUI($this->lng->txt('cal_fullday'),'event');
		$dur->setStartText($this->lng->txt('event_start_date'));
		$dur->setEndText($this->lng->txt('event_end_date'));
		$dur->enableToggleFullTime(
			$this->lng->txt('event_fulltime_info'),
			$this->object->getFirstAppointment()->enabledFulltime() ? true : false 
		);
		$dur->setShowTime(true);
		$dur->setStart($this->object->getFirstAppointment()->getStart());
		$dur->setEnd($this->object->getFirstAppointment()->getEnd());
		
		$this->form->addItem($dur);
		
		/*
		// start
		$start = new ilDateTimeInputGUI($this->lng->txt('event_start_date'),'start');
		$start->setMinuteStepSize(5);
		$start->setDate($this->object->getFirstAppointment()->getStart());
		$start->setShowTime(true);
		#$this->form->addItem($start);
		
		// end
		$end = new ilDateTimeInputGUI($this->lng->txt('event_end_date'),'end');
		$end->setMinuteStepSize(5);
		$end->setDate($this->object->getFirstAppointment()->getEnd());
		$end->setShowTime(true);
		#$this->form->addItem($end);
		*/

		// Recurrence
		if($a_mode == 'create')
		{
			// #14547
			$lp = new ilCheckboxInputGUI($this->lng->txt("sess_lp_preset"), "lp_preset");
			$lp->setInfo($this->lng->txt("sess_lp_preset_info"));
			$lp->setChecked(true);
			$this->form->addItem($lp);			
			
			if(!is_object($this->rec))
			{
				include_once('./Modules/Session/classes/class.ilEventRecurrence.php');
				$this->rec = new ilEventRecurrence();
			}
			include_once('./Services/Calendar/classes/Form/class.ilRecurrenceInputGUI.php');
			$rec = new ilRecurrenceInputGUI($this->lng->txt('cal_recurrences'),'frequence');
			$rec->allowUnlimitedRecurrences(false);
			$rec->setRecurrence($this->rec);
			$this->form->addItem($rec);
		}

		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('event_section_information'));
		$this->form->addItem($section);

		// title
		$title = new ilTextInputGUI($this->lng->txt('event_title'),'title');
		$title->setValue($this->object->getTitle());
		$title->setSize(50);
		$title->setMaxLength(70);
		$this->form->addItem($title);
		
		// desc
		$desc = new ilTextAreaInputGUI($this->lng->txt('event_desc'),'desc');
		$desc->setValue($this->object->getLongDescription());
		$desc->setRows(4);
		$desc->setCols(50);
		$this->form->addItem($desc);
		
		// location
		$desc = new ilTextAreaInputGUI($this->lng->txt('event_location'),'location');
		$desc->setValue($this->object->getLocation());
		$desc->setRows(4);
		$desc->setCols(50);
		$this->form->addItem($desc);

		// workflow		
		$details = new ilTextAreaInputGUI($this->lng->txt('event_details_workflow'),'details');
		$details->setValue($this->object->getDetails());
		$details->setCols(50);
		$details->setRows(4);
		$this->form->addItem($details);

		// section
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('event_tutor_data'));
		$this->form->addItem($section);
		
		// name
		$tutor_name = new ilTextInputGUI($this->lng->txt('tutor_name'),'tutor_name');
		$tutor_name->setValue($this->object->getName());
		$tutor_name->setSize(20);
		$tutor_name->setMaxLength(70);
		$this->form->addItem($tutor_name);
		
		// email
		$tutor_email = new ilTextInputGUI($this->lng->txt('tutor_email'),'tutor_email');
		$tutor_email->setValue($this->object->getEmail());
		$tutor_email->setSize(20);
		$tutor_email->setMaxLength(70);
		$this->form->addItem($tutor_email);

		// phone
		$tutor_phone = new ilTextInputGUI($this->lng->txt('tutor_phone'),'tutor_phone');
		$tutor_phone->setValue($this->object->getPhone());
		$tutor_phone->setSize(20);
		$tutor_phone->setMaxLength(70);
		$this->form->addItem($tutor_phone);
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('sess_section_reg'));
		$this->form->addItem($section);

		include_once './Modules/Session/classes/class.ilSessionMembershipRegistrationSettingsGUI.php';
		include_once './Services/Membership/classes/class.ilMembershipRegistrationSettings.php';
		$reg_settings = new ilSessionMembershipRegistrationSettingsGUI(
				$this,
				$this->object,
				array(
					ilMembershipRegistrationSettings::TYPE_DIRECT,
					ilMembershipRegistrationSettings::TYPE_REQUEST,
					ilMembershipRegistrationSettings::TYPE_NONE,
					ilMembershipRegistrationSettings::REGISTRATION_LIMITED_USERS
				)
		);
		$reg_settings->addMembershipFormElements($this->form, '');


		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('event_assign_files'));
		$this->form->addItem($section);
		
		$files = new ilFileWizardInputGUI($this->lng->txt('objs_file'),'files');
		$files->setFilenames(array(0 => ''));
		$this->form->addItem($files);
				
		switch($a_mode)
		{
			case 'create':
				$this->form->setTitle($this->lng->txt('event_table_create'));

				$this->form->addCommandButton('save',$this->lng->txt('event_btn_add'));
				$this->form->addCommandButton('saveAndAssignMaterials',$this->lng->txt('event_btn_add_edit'));
				$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
				return true;
			
			case 'edit':
				$this->form->setTitle($this->lng->txt('event_table_update'));

				$this->form->addCommandButton('update',$this->lng->txt('save'));
				$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				
				return true;
		}
		return true;
	}
	
	/**
	 * load settings
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function load()
	{
		global $ilUser;

		$this->object->getFirstAppointment()->setStartingTime($this->__toUnix($_POST['event']['start']['date'],$_POST['event']['start']['time']));
		$this->object->getFirstAppointment()->setEndingTime($this->__toUnix($_POST['event']['end']['date'],$_POST['event']['end']['time']));
		$this->object->getFirstAppointment()->toggleFullTime((bool) $_POST['event']['fulltime']);
		
		include_once('./Services/Calendar/classes/class.ilDate.php');
		if($this->object->getFirstAppointment()->isFullday())
		{
			$start = new ilDate($_POST['event']['start']['date']['y'].'-'.$_POST['event']['start']['date']['m'].'-'.$_POST['event']['start']['date']['d'],
				IL_CAL_DATE);
			$this->object->getFirstAppointment()->setStart($start);
				
			$end = new ilDate($_POST['event']['end']['date']['y'].'-'.$_POST['event']['end']['date']['m'].'-'.$_POST['event']['end']['date']['d'],
				IL_CAL_DATE);
			$this->object->getFirstAppointment()->setEnd($end);
		}
		else
		{
			$start_dt['year'] = (int) $_POST['event']['start']['date']['y'];
			$start_dt['mon'] = (int) $_POST['event']['start']['date']['m'];
			$start_dt['mday'] = (int) $_POST['event']['start']['date']['d'];
			$start_dt['hours'] = (int) $_POST['event']['start']['time']['h'];
			$start_dt['minutes'] = (int) $_POST['event']['start']['time']['m'];
			
			$start = new ilDateTime($start_dt,IL_CAL_FKT_GETDATE,$ilUser->getTimeZone());
			$this->object->getFirstAppointment()->setStart($start);

			$end_dt['year'] = (int) $_POST['event']['end']['date']['y'];
			$end_dt['mon'] = (int) $_POST['event']['end']['date']['m'];
			$end_dt['mday'] = (int) $_POST['event']['end']['date']['d'];
			$end_dt['hours'] = (int) $_POST['event']['end']['time']['h'];
			$end_dt['minutes'] = (int) $_POST['event']['end']['time']['m'];
			$end = new ilDateTime($end_dt,IL_CAL_FKT_GETDATE,$ilUser->getTimeZone());
			$this->object->getFirstAppointment()->setEnd($end);
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->setDescription(ilUtil::stripSlashes($_POST['desc']));
		$this->object->setLocation(ilUtil::stripSlashes($_POST['location']));
		$this->object->setName(ilUtil::stripSlashes($_POST['tutor_name']));
		$this->object->setPhone(ilUtil::stripSlashes($_POST['tutor_phone']));
		$this->object->setEmail(ilUtil::stripSlashes($_POST['tutor_email']));
		$this->object->setDetails(ilUtil::stripSlashes($_POST['details']));
		
		$this->object->setRegistrationType((int) $_POST['registration_type']);
		$this->object->setRegistrationMaxUsers((int) $_POST['registration_max_members']);
		$this->object->enableRegistrationUserLimit((int) $_POST['registration_membership_limited']);
		$this->object->enableRegistrationWaitingList((int) $_POST['waiting_list']);
	}

	/**
	 * load recurrence settings
	 *
	 * @access protected
	 * @return
	 */
	protected function loadRecurrenceSettings()
	{
		include_once('./Modules/Session/classes/class.ilSessionRecurrence.php');
		$this->rec = new ilSessionRecurrence();
		
		switch($_POST['frequence'])
		{
			case IL_CAL_FREQ_DAILY:
				$this->rec->setFrequenceType($_POST['frequence']);
				$this->rec->setInterval((int) $_POST['count_DAILY']);
				break;
			
			case IL_CAL_FREQ_WEEKLY:
				$this->rec->setFrequenceType($_POST['frequence']);
				$this->rec->setInterval((int) $_POST['count_WEEKLY']);
				if(is_array($_POST['byday_WEEKLY']))
				{
					$this->rec->setBYDAY(ilUtil::stripSlashes(implode(',',$_POST['byday_WEEKLY'])));
				}				
				break;

			case IL_CAL_FREQ_MONTHLY:
				$this->rec->setFrequenceType($_POST['frequence']);
				$this->rec->setInterval((int) $_POST['count_MONTHLY']);
				switch((int) $_POST['subtype_MONTHLY'])
				{
					case 0:
						// nothing to do;
						break;
					
					case 1:
						switch((int) $_POST['monthly_byday_day'])
						{
							case 8:
								// Weekday
								$this->rec->setBYSETPOS((int) $_POST['monthly_byday_num']);
								$this->rec->setBYDAY('MO,TU,WE,TH,FR');
								break;
								
							case 9:
								// Day of month
								$this->rec->setBYMONTHDAY((int) $_POST['monthly_byday_num']);
								break;
								
							default:
								$this->rec->setBYDAY((int) $_POST['monthly_byday_num'].$_POST['monthly_byday_day']);
								break;
						}
						break;
					
					case 2:
						$this->rec->setBYMONTHDAY((int) $_POST['monthly_bymonthday']);
						break;
				}
				break;			
			
			case IL_CAL_FREQ_YEARLY:
				$this->rec->setFrequenceType($_POST['frequence']);
				$this->rec->setInterval((int) $_POST['count_YEARLY']);
				switch((int) $_POST['subtype_YEARLY'])
				{
					case 0:
						// nothing to do;
						break;
					
					case 1:
						$this->rec->setBYMONTH((int) $_POST['yearly_bymonth_byday']);
						$this->rec->setBYDAY((int) $_POST['yearly_byday_num'].$_POST['yearly_byday']);
						break;
					
					case 2:
						$this->rec->setBYMONTH((int) $_POST['yearly_bymonth_by_monthday']);
						$this->rec->setBYMONTHDAY((int) $_POST['yearly_bymonthday']);
						break;
				}
				break;			
		}
		
		// UNTIL
		switch((int) $_POST['until_type'])
		{
			case 1:
				$this->rec->setFrequenceUntilDate(null);
				// nothing to do
				break;
				
			case 2:
				$this->rec->setFrequenceUntilDate(null);
				$this->rec->setFrequenceUntilCount((int) $_POST['count']);
				break;
				
			case 3:
				$end_dt['year'] = (int) $_POST['until_end']['date']['y'];
				$end_dt['mon'] = (int) $_POST['until_end']['date']['m'];
				$end_dt['mday'] = (int) $_POST['until_end']['date']['d'];
				
				$this->rec->setFrequenceUntilCount(0);
				$this->rec->setFrequenceUntilDate(new ilDate($end_dt,IL_CAL_FKT_GETDATE,$this->timezone));
				break;
		}
	}
	
	
	/**
	 * 
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function __toUnix($date,$time)
	{
		return mktime($time['h'],$time['m'],0,$date['m'],$date['d'],$date['y']);
	}

	/**
	 * Add session locator
	 *
	 * @access public
	 * 
	 */
	public function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{						
			// see prepareOutput()
			include_once './Modules/Session/classes/class.ilSessionAppointment.php';
			$title = strlen($this->object->getTitle()) ? (': '.$this->object->getTitle()) : ''; 
			$title = $this->object->getFirstAppointment()->appointmentToString().$title;
		
			$ilLocator->addItem($title, $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
		}
	}
	
	/**
	 * Build tabs
	 *
	 * @access public
	 * 
	 */
	public function getTabs($tabs_gui)
	{
	 	global $ilAccess, $ilTabs, $tree, $ilCtrl, $ilHelp;

	 	$ilHelp->setScreenIdComponent("sess");
	 	
		$parent_id = $tree->getParentId($this->object->getRefId());
		
		// #11650
		$parent_type = ilObject::_lookupType($parent_id, true);		

		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $parent_id);
		$tabs_gui->setBackTarget($this->lng->txt('back_to_'.$parent_type.'_content'),
			$ilCtrl->getLinkTargetByClass("ilrepositorygui", ""));
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
		
		$tabs_gui->addTarget('info_short',
							 $this->ctrl->getLinkTarget($this,'infoScreen'));

	 	if($ilAccess->checkAccess('write','',$this->object->getRefId()))
	 	{
			$tabs_gui->addTarget('settings',
								 $this->ctrl->getLinkTarget($this,'edit'));
			$tabs_gui->addTarget('crs_materials',
								 $this->ctrl->getLinkTarget($this,'materials'));
			$tabs_gui->addTarget('event_edit_members',
								 $this->ctrl->getLinkTarget($this,'members'));
	 	}
		
		// learning progress
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
		{
			$tabs_gui->addTarget('learning_progress',
				$this->ctrl->getLinkTargetByClass(array('ilobjsessiongui','illearningprogressgui'),''),
				'',
				array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}

		// export
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("export",
				$this->ctrl->getLinkTargetByClass("ilexportgui", ""), "", "ilexportgui");
		}


		// edit permissions
		if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	 	
	}
	
	/**
	 * Custom callback after object is created (in parent containert
	 * 
	 * @param ilObject $a_obj 
	 */	
	public function afterSaveCallback(ilObject $a_obj)
	{		
		// add new object to materials
		include_once './Modules/Session/classes/class.ilEventItems.php';
		$event_items = new ilEventItems($this->object->getId());
		$event_items->addItem($a_obj->getRefId());
		$event_items->update();

		/*
		ilUtil::sendSuccess($this->lng->txt("object_added"), true);
		$this->ctrl->redirect($this, "materials");
		*/
	}	
	
	/**
	 * Send mail to selected users
	 */
	protected function sendMailToSelectedUsersObject()
	{
		$GLOBALS['ilCtrl']->setReturn($this,'members');
		$GLOBALS['ilCtrl']->setCmdClass('ilmembershipgui');
		include_once './Services/Membership/classes/class.ilMembershipGUI.php';
		$mem = new ilMembershipGUI($this);
		$GLOBALS['ilCtrl']->forwardCommand($mem);
	}
	
	/**
	 * Used for waiting list
	 */
	public function readMemberData($a_usr_ids)
	{
		$tmp_data = array();
		foreach ($a_usr_ids as $usr_id)
		{
			$tmp_data[$usr_id] = array();
		}
		return $tmp_data;
	}
	
	/**
	 * add from waiting list 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function assignFromWaitingListObject()
	{
		$this->checkPermission('write');
		
		if(!count($_POST["waiting"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->membersObject();
			return false;
		}
		
		include_once('./Modules/Session/classes/class.ilSessionWaitingList.php');
		$waiting_list = new ilSessionWaitingList($this->object->getId());
		
		include_once './Modules/Session/classes/class.ilEventParticipants.php';
		$part = new ilEventParticipants($this->object->getId());

		$added_users = 0;
		foreach($_POST["waiting"] as $user_id)
		{
			$part->register($user_id);
			$waiting_list->removeFromList($user_id);

			include_once './Modules/Session/classes/class.ilSessionMembershipMailNotification.php';
			$noti = new ilSessionMembershipMailNotification();
			$noti->setRefId($this->object->getRefId());
			$noti->setRecipients(array($user_id));
			$noti->setType(ilSessionMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);
			$noti->send();
			
			++$added_users;
		}
		if($added_users)
		{
			ilUtil::sendSuccess($this->lng->txt("sess_users_added"));
			$this->membersObject();

			return true;
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("sess_users_already_assigned"));
			$this->searchObject();
			return false;
		}
	}
	
	/**
	 * refuse from waiting list
	 *
	 * @access public
	 * @return
	 */
	public function refuseFromListObject()
	{
		$this->checkPermission('write');
		
		if(!count($_POST['waiting']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		include_once('./Modules/Session/classes/class.ilSessionWaitingList.php');
		$waiting_list = new ilSessionWaitingList($this->object->getId());

		foreach($_POST["waiting"] as $user_id)
		{
			$waiting_list->removeFromList($user_id);
			
			include_once './Modules/Session/classes/class.ilSessionMembershipMailNotification.php';
			$noti = new ilSessionMembershipMailNotification();
			$noti->setRefId($this->object->getRefId());
			$noti->setRecipients(array($user_id));
			$noti->setType(ilSessionMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER);
			$noti->send();
			
		}
		
		ilUtil::sendSuccess($this->lng->txt('sess_users_removed_from_list'));
		$this->membersObject();
		return true;
	}
	
	/**
	 * assign subscribers
	 *
	 * @access public
	 * @return
	 */
	public function assignSubscribersObject()
	{
		global $lng,$ilUser;

		$this->checkPermission('write');
		
		if(!count($_POST['subscribers']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		include_once './Services/Membership/classes/class.ilParticipants.php';
		$part = ilParticipants::getInstanceByObjId($this->object->getId());
		
		foreach($_POST['subscribers'] as $usr_id)
		{
			$part->add($usr_id);
			$part->deleteSubscriber($usr_id);

			include_once './Modules/Session/classes/class.ilSessionMembershipMailNotification.php';
			$noti = new ilSessionMembershipMailNotification();
			$noti->setRefId($this->object->getRefId());
			$noti->setRecipients(array($usr_id));
			$noti->setType(ilSessionMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);
			$noti->send();
		}
		ilUtil::sendSuccess($this->lng->txt("sess_msg_applicants_assigned"),true);
		$this->ctrl->redirect($this,'members');
		return true;
	}
	
	/**
	 * refuse subscribers
	 *
	 * @access public
	 * @return
	 */
	public function refuseSubscribersObject()
	{
		global $lng;

		$this->checkPermission('write');
		
		if(!count($_POST['subscribers']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		include_once './Services/Membership/classes/class.ilParticipants.php';
		$part = ilParticipants::getInstanceByObjId($this->object->getId());
		foreach($_POST['subscribers'] as $usr_id)
		{
			$part->deleteSubscriber($usr_id);
			
			include_once './Modules/Session/classes/class.ilSessionMembershipMailNotification.php';
			$noti = new ilSessionMembershipMailNotification();
			$noti->setRefId($this->object->getRefId());
			$noti->setRecipients(array($usr_id));
			$noti->setType(ilSessionMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER);
			$noti->send();
		}
		ilUtil::sendSuccess($this->lng->txt("sess_msg_applicants_removed"));
		$this->membersObject();
		return true;
		
	}
	/**
	 * container ref id
	 * @return int ref id
	 */
	public function getContainerRefId()
	{
		if(!$this->container_ref_id)
		{
			$this->initContainer();
		}
		return $this->container_ref_id;
	}}
?>