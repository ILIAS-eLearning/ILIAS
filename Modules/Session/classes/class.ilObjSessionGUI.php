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

include_once('./classes/class.ilObjectGUI.php');
include_once('./Modules/Session/classes/class.ilObjSession.php');

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* @ilCtrl_Calls ilObjSessionGUI: ilPermissionGUI, ilInfoScreenGUI
*
* @ingroup ModulesSession 
*/

class ilObjSessionGUI extends ilObjectGUI
{
	public $lng;
	public $ctrl;
	public $tpl;
	
	protected $course_ref_id = 0;
	protected $course_obj_id = 0;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		global $ilCtrl, $lng, $ilCtrl,$tpl;
		
		$this->type = "sess";
		parent::__construct($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		
		$this->lng = $lng;
		$this->lng->loadLanguageModule("event");
		$this->lng->loadLanguageModule('crs');
		$this->lng->loadLanguageModule('trac');

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
  		global $ilUser;
  
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
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$ret = $this->ctrl->forwardCommand($perm_gui);
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
  		return true;
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
		global $ilAccess,$ilErr;
		
		if($ilAccess->checkAccess('visible', "", $a_target))
		{
			$_GET["cmd"] = "infoScreen";
			$_GET["ref_id"] = $a_target;
			include("repository.php");
			exit;
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
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
	 * info screen
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	public function infoScreen()
	{
		global $ilAccess, $ilUser;

		if (!$ilAccess->checkAccess("visible", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tabs_gui->setTabActive('info_short');

		$appointment_obj =& $this->object->getFirstAppointment();

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->addSection($this->lng->txt("event_general_properties"));
		$info->addProperty($this->lng->txt('event_title'),
						   $this->object->getTitle());
		if(strlen($desc = $this->object->getDescription()))
		{
			$info->addProperty($this->lng->txt('event_desc'),
							   nl2br($this->object->getDescription()));
		}
		if(strlen($location = $this->object->getLocation()))
		{
			$info->addProperty($this->lng->txt('event_location'),
							   nl2br($this->object->getLocation()));
		}
		$info->addProperty($this->lng->txt('event_date'),
							$appointment_obj->appointmentToString());

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

		$details = $this->object->getDetails();

		// TODO: Files
		$files = $this->object->getFiles();
		if(strlen($details) or is_array($files))
		{
			$info->addSection($this->lng->txt('event_further_informations'));
			
			if(strlen($details))
			{
				$info->addProperty($this->lng->txt('event_details_workflow'),
								   nl2br($details));
			}

			if(count($files))
			{
				$tpl = new ilTemplate('tpl.event_info_file.html',true,true,'Modules/Course');

				foreach($files as $file)
				{
					$tpl->setCurrentBlock("files");
					$this->ctrl->setParameter($this,'file_id',$file->getFileId());
					$tpl->setVariable("DOWN_LINK",$this->ctrl->getLinkTarget($this,'sendfile'));
					$tpl->setVariable("DOWN_NAME",$file->getFileName());
					$tpl->setVariable("DOWN_INFO_TXT",$this->lng->txt('event_file_size_info'));
					$tpl->setVariable("DOWN_SIZE",$file->getFileSize());
					$tpl->setVariable("TXT_BYTES",$this->lng->txt('bytes'));
					$tpl->parseCurrentBlock();
				}
				$info->addProperty($this->lng->txt('event_file_download'),
								   $tpl->get());
			}
			
		}
		#$info->enablePrivateNotes();
		

		// forward the command
		$this->ctrl->forwardCommand($info);
	
	}
	
	
	/**
	 * create new event
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	public function createObject()
	{
		if(!is_object($this->object))
		{
			$this->object = new ilObjSession();
		}
		
		$this->initForm('create');
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.sess_create.html','Modules/Session');
		$this->tpl->setVariable('EVENT_ADD_TABLE',$this->form->getHTML());
		
		// TODO: clone events
		
		/*
		if(!count($events = ilEvent::_getEvents($this->container_obj->getId())) or 1)
		{
			return true;
		}
		$this->tpl->setCurrentBlock('clone_event');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("CLONE_TITLE_IMG",ilUtil::getImagePath('icon_event.gif'));
		$this->tpl->setVariable("CLONE_TITLE_IMG_ALT",$this->lng->txt('events'));
		$this->tpl->setVariable('CLONE_TITLE',$this->lng->txt('events_clone_title'));
		$this->tpl->setVariable('CLONE_EVENT',$this->lng->txt('event'));
		$this->tpl->setVariable('TXT_BTN_CLONE_EVENT',$this->lng->txt('event_clone_btn'));
		$this->tpl->setVariable('TXT_CLONE_CANCEL',$this->lng->txt('cancel'));
		
		$options[0] = $this->lng->txt('event_select_one');
		foreach($events as $event_obj)
		{
			$options[$event_obj->getEventId()] = $event_obj->getTitle();
		}
		$this->tpl->setVariable('SEL_EVENT',ilUtil::formSelect(0,'clone_source',$options,false,true));
		*/
	}
	
	/**
	 * save object
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	public function saveObject()
	{
		global $ilErr;
		
		$this->object = new ilObjSession();

		$this->load();
		#$this->loadRecurrenceSettings();
		$this->initForm('create');
		
		$ilErr->setMessage('');
		if(!$this->form->checkInput())
		{
			$ilErr->setMessage($this->lng->txt('err_check_input'));
		}

		$this->object->validate();
		$this->object->getFirstAppointment()->validate();

		if(strlen($ilErr->getMessage()))
		{
			ilUtil::sendInfo($ilErr->getMessage().$_GET['ref_id']);
			$this->createObject();
			return false;
		}
		// Create session
		$this->object->create();
		$this->object->createReference();
		$this->object->putInTree($_GET["ref_id"]);
		$this->object->setPermissions($_GET["ref_id"]);
		
		// create appointment
		$this->object->getFirstAppointment()->setSessionId($this->object->getId());
		$this->object->getFirstAppointment()->create();
		
		// TODO: files
		/*
		foreach($this->files as $file_obj)
		{
			$file_obj->setEventId($this->event_obj->getEventId());
			$file_obj->create();
		}
		*/
		#$this->createRecurringSessions();

		ilUtil::sendInfo($this->lng->txt('event_add_new_event'),true);
		$this->ctrl->returnToParent($this);
		return true;
	
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
		$this->tabs_gui->setTabActive('edit_properties');
		
		$this->initForm('edit');
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.sess_edit.html','Modules/Session');
		$this->tpl->setVariable('EVENT_EDIT_TABLE',$this->form->getHTML());
		
		// TODO: files
		
		/*
		if(!count($files = ilEventFile::_readFilesByEvent($this->event_obj->getEventId())))
		{
			return true;
		}
		$rows = array();
		foreach($files as $file)
		{
			$table_data['id'] = $file->getFileId();
			$table_data['filename'] = $file->getFileName();
			$table_data['filetype'] = $file->getFileType();
			$table_data['filesize'] = $file->getFileSize();
			
			$rows[] = $table_data; 
		}
		
		include_once("./Modules/Course/classes/Event/class.ilEventFileTableGUI.php");
		$table_gui = new ilEventFileTableGUI($this, "edit");
		$table_gui->setTitle($this->lng->txt("event_files"));
		$table_gui->setData($rows);
		$table_gui->addCommandButton("cancel", $this->lng->txt("cancel"));
		$table_gui->addMultiCommand("confirmDeleteFiles", $this->lng->txt("delete"));
		$table_gui->setSelectAllCheckbox("file_id");
		$this->tpl->setVariable('EVENT_FILE_TABLE',$table_gui->getHTML());

		return true;
		*/
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
			ilUtil::sendInfo($ilErr->getMessage());
			$this->editObject();
			return false;
		}
		// Update event
		$this->object->update();
		$this->object->getFirstAppointment()->update();
		
		/* TODO: files
		foreach($this->files as $file_obj)
		{
			$file_obj->setEventId($this->event_obj->getEventId());
			$file_obj->create();
		}
		*/
		
		ilUtil::sendInfo($this->lng->txt('event_updated'));
		$this->editObject();
		return true;
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

		include_once 'Modules/Session/classes/class.ilEventItems.php';
		$this->event_items = new ilEventItems($this->object->getId());
		$items = $this->event_items->getItems();

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.sess_materials.html','Modules/Session');
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this,'materials'));
		$this->tpl->setVariable("COLL_TITLE_IMG",ilUtil::getImagePath('icon_sess.gif'));
		$this->tpl->setVariable("COLL_TITLE_IMG_ALT",$this->lng->txt('events'));
		$this->tpl->setVariable("TABLE_TITLE",$this->lng->txt('event_assign_materials_table'));
		$this->tpl->setVariable("TABLE_INFO",$this->lng->txt('event_assign_materials_info'));

		$this->course_ref_id = $tree->checkForParentType($this->object->getRefId(),'crs');
		if(!$this->course_ref_id)
		{
			ilUtil::sendInfo('No course object found. Aborting');
			return true;
		}
		$nodes = $tree->getSubTree($tree->getNodeData($this->course_ref_id));
		$counter = 1;
		foreach($nodes as $node)
		{
			// No side blocks here
			if ($objDefinition->isSideBlock($node['type']) or $node['type'] == 'sess')
			{
				continue;
			}
			
			if($node['type'] == 'rolf')
			{
				continue;
			}
			if($counter++ == 1)
			{
				continue;
			}
			$this->tpl->setCurrentBlock("material_row");
			
			$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$node['type'].'_s.gif'));
			$this->tpl->setVariable('IMG_ALT',$this->lng->txt('obj_'.$node['type']));
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor($counter,'tblrow1','tblrow2'));
			$this->tpl->setVariable("CHECK_COLL",ilUtil::formCheckbox(in_array($node['ref_id'],$items) ? 1 : 0,
																	  'items[]',$node['ref_id']));
			$this->tpl->setVariable("COLL_TITLE",$node['title']);

			if(strlen($node['description']))
			{
				$this->tpl->setVariable("COLL_DESC",$node['description']);
			}
			$this->tpl->setVariable("ASSIGNED_IMG_OK",in_array($node['ref_id'],$items) ? 
									ilUtil::getImagePath('icon_ok.gif') :
									ilUtil::getImagePath('icon_not_ok.gif'));
			$this->tpl->setVariable("ASSIGNED_STATUS",$this->lng->txt('event_material_assigned'));
			$this->tpl->setVariable("COLL_PATH",$this->formatPath($node['ref_id']));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("SELECT_ROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
		$this->tpl->setVariable("SELECT_ALL",$this->lng->txt('select_all'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("BTN_SAVE",$this->lng->txt('save'));
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

		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->materialsObject();
	}
	
	/**
	 * edit members
	 *
	 * @access public
	 * @return
	 */
	public function editMembersObject()
	{
		global $tree;
		
		$this->tabs_gui->setTabActive('event_edit_members');
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.sess_members.html','Modules/Session');
	
		$this->course_ref_id = $tree->checkForParentType($this->object->getRefId(),'crs');
		$this->course_obj_id = ilObject::_lookupObjId($this->course_ref_id);
		if(!$this->course_ref_id)
		{
			ilUtil::sendInfo('No course object found. Aborting');
			return true;
		}

		// display print button
		$this->__showButton($this->ctrl->getLinkTarget($this,'printViewMembers'),$this->lng->txt('print'),'target="_blank"');

		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		include_once 'Modules/Session/classes/class.ilEventParticipants.php';

		$members_obj = ilCourseParticipants::_getInstanceByObjId($this->course_obj_id);
		$event_part = new ilEventParticipants($this->object->getId());

		$members = $members_obj->getParticipants();
		$members = ilUtil::_sortIds($members,'usr_data','lastname','usr_id');

		$this->tpl->addBlockfile("PARTICIPANTS_TABLE","participants_table", "tpl.table.html");
		$this->tpl->addBlockfile('TBL_CONTENT','tbl_content','tpl.sess_members_row.html','Modules/Session');

		// Table 
		$tbl = new ilTableGUI();
		$tbl->setTitle($this->lng->txt("event_tbl_participants"),
					   'icon_usr.gif',
					   $this->lng->txt('obj_usr'));
		$this->ctrl->setParameter($this,'offset',(int) $_GET['offset']);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this,'editMembers'));
		$this->tpl->setVariable("COLUMN_COUNTS",6);
		#$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		#$this->tpl->setCurrentBlock("tbl_action_btn");
		#$this->tpl->setVariable("BTN_NAME", "updateMembers");
		#$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("event_save_participants"));
		#$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("plain_button");
		$this->tpl->setVariable("PBTN_NAME",'updateMembers');
		$this->tpl->setVariable("PBTN_VALUE",$this->lng->txt('save'));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("plain_button");
		$this->tpl->setVariable("PBTN_NAME",'cancel');
		$this->tpl->setVariable("PBTN_VALUE",$this->lng->txt('cancel'));
		$this->tpl->parseCurrentBlock();

		if($this->object->enabledRegistration())
		{
			$tbl->setHeaderNames(array($this->lng->txt('name'),
									   $this->lng->txt('trac_mark'),
									   $this->lng->txt('trac_comment'),
									   $this->lng->txt('event_tbl_registered'),
									   $this->lng->txt('event_tbl_participated')));
			$tbl->setHeaderVars(array("name",
									  "mark",
									  "comment",
									  "registered",
									  "participated"),
								$this->ctrl->getParameterArray($this,'editMembers'));
			$tbl->setColumnWidth(array('','','','',''));
		}
		else
		{
			$tbl->setHeaderNames(array($this->lng->txt('name'),
									   $this->lng->txt('trac_mark'),
									   $this->lng->txt('trac_comment'),
									   $this->lng->txt('event_tbl_participated')));

			$tbl->setHeaderVars(array("name",
									  "mark",
									  "comment",
									  "participated"),
								$this->ctrl->getParameterArray($this,'editMembers'));

			$tbl->setColumnWidth(array('','','',''));
		}

		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($members));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		$sliced_users = array_slice($members,$_GET['offset'],$_SESSION['tbl_limit']);
		$tbl->disable('sort');
		$tbl->enable('action');
		$tbl->render();

		$counter = 0;
		foreach($sliced_users as $user_id)
		{
			$user_data = $event_part->getUser($user_id);

			if($this->object->enabledRegistration())
			{
				$this->tpl->setCurrentBlock("registered_col");
				$this->tpl->setVariable("IMAGE_REGISTERED",$event_part->isRegistered($user_id) ? 
										ilUtil::getImagePath('icon_ok.gif') :
										ilUtil::getImagePath('icon_not_ok.gif'));
				$this->tpl->setVariable("REGISTERED",$event_part->isRegistered($user_id) ?
										$this->lng->txt('event_registered') :
										$this->lng->txt('event_not_registered'));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("tbl_content");
			$name = ilObjUser::_lookupName($user_id);
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$this->tpl->setVariable("LASTNAME",$name['lastname']);
			$this->tpl->setVariable("FIRSTNAME",$name['firstname']);
			$this->tpl->setVariable("LOGIN",ilObjUser::_lookupLogin($user_id));
			$this->tpl->setVariable("MARK",$user_data['mark']);
			$this->tpl->setVariable("MARK_NAME",'mark['.$user_id.']');
			$this->tpl->setVariable("COMMENT_NAME",'comment['.$user_id.']');
			$this->tpl->setVariable("COMMENT",$user_data['comment']);

			$this->tpl->setVariable("USER_ID",$user_id);
			$this->tpl->setVariable("CHECKED",$event_part->hasParticipated($user_id) ? 'checked="checked"' : '');
			$this->tpl->setVariable("IMAGE_PART",$event_part->hasParticipated($user_id) ? 
									ilUtil::getImagePath('icon_ok.gif') :
									ilUtil::getImagePath('icon_not_ok.gif'));
			$this->tpl->setVariable("PART",$event_part->hasParticipated($user_id) ?
									$this->lng->txt('event_participated') :
									$this->lng->txt('event_not_participated'));
			$this->ctrl->setParameter($this,'user_id',$user_id);
			$this->tpl->setVariable("EDIT_LINK",$this->ctrl->getLinkTarget($this,'editUser'));
			$this->tpl->setVariable("TXT_EDIT",$this->lng->txt('edit'));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("select_row");
		$this->tpl->setVariable("SELECT_SPAN",$this->object->enabledRegistration() ? 4 : 3);
		$this->tpl->setVariable("ROWCLASS",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
		$this->tpl->setVariable("SELECT_ALL",$this->lng->txt('select_all'));
		$this->tpl->parseCurrentBlock();
		
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
		
		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		include_once 'Modules/Session/classes/class.ilEventParticipants.php';

		$this->course_ref_id = $tree->checkForParentType($this->object->getRefId(),'crs');
		$this->course_obj_id = ilObject::_lookupObjId($this->course_ref_id);
		if(!$this->course_ref_id)
		{
			ilUtil::sendInfo('No course object found. Aborting');
			return true;
		}
		
		$_POST['participants'] = is_array($_POST['participants']) ? $_POST['participants'] : array();

		$members_obj = ilCourseParticipants::_getInstanceByObjId($this->course_obj_id);
		$members = $members_obj->getParticipants();
		$members = ilUtil::_sortIds($members,'usr_data','lastname','usr_id');
		$sliced_users = array_slice($members,$_GET['offset'],$_SESSION['tbl_limit']);
		
		$event_part = new ilEventParticipants($this->object->getId());

		foreach($sliced_users as $user)
		{
			$part = new ilEventParticipants($this->object->getId());
			$part->setUserId($user);
			$part->setMark(ilUtil::stripSlashes($_POST['mark'][$user]));
			$part->setComment(ilUtil::stripSlashes($_POST['comment'][$user]));
			$part->setParticipated(in_array($user,$_POST['participants']));
			$part->setRegistered(ilEventParticipants::_isRegistered($user,$this->object->getId()));
			$part->updateUser();
		}
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->editMembersObject();
	}
	
	/**
	 * print view
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function printViewMembersObject()
	{
		include_once 'Modules/Session/classes/class.ilEventParticipants.php';
		include_once('./Modules/Course/classes/class.ilCourseParticipants.php');

		global $ilErr,$ilAccess,$tree;
		
		$this->course_ref_id = $tree->checkForParentType($this->object->getRefId(),'crs');
		$this->course_obj_id = ilObject::_lookupObjId($this->course_ref_id);
		if(!$this->course_ref_id)
		{
			ilUtil::sendInfo('No course object found. Aborting');
			return true;
		}
		
		$members_obj = ilCourseParticipants::_getInstanceByObjId($this->course_obj_id);
		$event_app = $this->object->getFirstAppointment();
		$event_part = new ilEventParticipants($this->object->getId());
		

		$this->tpl = new ilTemplate('tpl.main.html',true,true);
		// load style sheet depending on user's settings
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$this->tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);

		$tpl = new ilTemplate('tpl.sess_members_print.html',true,true,'Modules/Session');

		$tpl->setVariable("EVENT",$this->lng->txt('event'));
		$tpl->setVariable("EVENT_NAME",$this->object->getTitle());
		$tpl->setVariable("DATE",ilFormat::formatUnixTime($event_app->getStartingTime(),false)." ".
						  $event_app->formatTime());
		$tpl->setVariable("TXT_NAME",$this->lng->txt('name'));
		$tpl->setVariable("TXT_MARK",$this->lng->txt('trac_mark'));
		$tpl->setVariable("TXT_COMMENT",$this->lng->txt('trac_comment'));
		$tpl->setVariable("TXT_PARTICIPATED",$this->lng->txt('event_tbl_participated'));
		if($this->object->enabledRegistration())
		{
			$tpl->setVariable("TXT_REGISTERED",$this->lng->txt('event_tbl_registered'));
		}

		$members = $members_obj->getParticipants();
		$members = ilUtil::_sortIds($members,'usr_data','lastname','usr_id');
		foreach($members as $user_id)
		{
			
			$user_data = $event_part->getUser($user_id);

			if($this->object->enabledRegistration())
			{
				$tpl->setCurrentBlock("reg_col");
				$tpl->setVariable("REGISTERED",$event_part->isRegistered($user_id) ? "X" : "");
				$tpl->parseCurrentBlock();
			}
			$tpl->setVariable("COMMENT",$user_data['comment']);

			$tpl->setCurrentBlock("member_row");
			$name = ilObjUser::_lookupName($user_id);
			$tpl->setVariable("LASTNAME",$name['lastname']);
			$tpl->setVariable("FIRSTNAME",$name['firstname']);
			$tpl->setVariable("LOGIN",ilObjUser::_lookupLogin($user_id));
			$tpl->setVariable("MARK",$user_data['mark']);
			$tpl->setVariable("PARTICIPATED",$event_part->hasParticipated($user_id) ? "X" : "");
			$tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("CONTENT",$tpl->get());
		$this->tpl->setVariable("BODY_ATTRIBUTES",'onload="window.print()"');
		$this->tpl->show();
		exit;
	
	}

	/**
	 * Init Form 
	 *
	 * @access protected
	 */
	protected function initForm($a_mode)
	{
		if(is_object($this->form))
		{
			return true;
		}
	
		$this->lng->loadLanguageModule('dateplaner');
	
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		
		// title
		$title = new ilTextInputGUI($this->lng->txt('event_title'),'title');
		$title->setValue($this->object->getTitle());
		$title->setSize(20);
		$title->setMaxLength(70);
		$title->setRequired(TRUE);
		$this->form->addItem($title);
		
		// desc
		$desc = new ilTextAreaInputGUI($this->lng->txt('event_desc'),'desc');
		$desc->setValue($this->object->getDescription());
		$desc->setRows(4);
		$desc->setCols(50);
		$this->form->addItem($desc);
		
		// location
		$desc = new ilTextAreaInputGUI($this->lng->txt('event_location'),'location');
		$desc->setValue($this->object->getLocation());
		$desc->setRows(4);
		$desc->setCols(50);
		$this->form->addItem($desc);
		
		// registration
		$reg = new ilCheckboxInputGUI($this->lng->txt('event_registration'),'registration');
		$reg->setChecked($this->object->enabledRegistration() ? true : false);
		$reg->setOptionTitle($this->lng->txt('event_registration_info'));
		$this->form->addItem($reg);
		
		// section
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('event_date_time'));
		$this->form->addItem($section);
		
		$full = new ilCheckboxInputGUI($this->lng->txt('cal_from_until'),'fulltime');
		$full->setChecked($this->object->getFirstAppointment()->enabledFulltime() ? true : false);
		$full->setOptionTitle($this->lng->txt('event_fulltime_info'));
		$this->form->addItem($full);
		
		// start
		$start = new ilDateTimeInputGUI($this->lng->txt('event_start_date'),'start');
		$start->setMinuteStepSize(5);
		$start->setUnixTime($this->object->getFirstAppointment()->getStartingTime());
		$start->setShowTime(true);
		$full->addSubItem($start);
		
		// end
		$end = new ilDateTimeInputGUI($this->lng->txt('event_end_date'),'end');
		$end->setMinuteStepSize(5);
		$end->setUnixTime($this->object->getFirstAppointment()->getEndingTime());
		$end->setShowTime(true);
		$full->addSubItem($end);

		// Recurrence
		if($a_mode == 'create')
		{
			if(!is_object($this->rec))
			{
				include_once('./Modules/Course/classes/Event/class.ilEventRecurrence.php');
				$this->rec = new ilEventRecurrence();
			}
			include_once('./Services/Calendar/classes/Form/class.ilRecurrenceInputGUI.php');
			$rec = new ilRecurrenceInputGUI($this->lng->txt('cal_recurrences'),'frequence');
			$rec->enableUntilSelection(false);
			$rec->setRecurrence($this->rec);
			$this->form->addItem($rec);
		}

		// section
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('event_tutor_data'));
		$this->form->addItem($section);
		
		$tutor_name = new ilTextInputGUI($this->lng->txt('tutor_name'),'tutor_name');
		$tutor_name->setValue($this->object->getName());
		$tutor_name->setSize(20);
		$tutor_name->setMaxLength(70);
		$this->form->addItem($tutor_name);
		
		$tutor_email = new ilTextInputGUI($this->lng->txt('tutor_email'),'tutor_email');
		$tutor_email->setValue($this->object->getEmail());
		$tutor_email->setSize(20);
		$tutor_email->setMaxLength(70);
		$this->form->addItem($tutor_email);

		$tutor_phone = new ilTextInputGUI($this->lng->txt('tutor_phone'),'tutor_phone');
		$tutor_phone->setValue($this->object->getPhone());
		$tutor_phone->setSize(20);
		$tutor_phone->setMaxLength(70);
		$this->form->addItem($tutor_phone);
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('event_further_informations'));
		$this->form->addItem($section);
		
		$file = new ilFileInputGUI($this->lng->txt('event_file').' 1','file1');
		$file->enableFileNameSelection('file_name1');
		$this->form->addItem($file);
		
		$file = new ilFileInputGUI($this->lng->txt('event_file').' 2','file2');
		$file->enableFileNameSelection('file_name2');
		$this->form->addItem($file);

		$file = new ilFileInputGUI($this->lng->txt('event_file').' 3','file3');
		$file->enableFileNameSelection('file_name3');
		$this->form->addItem($file);

		$details = new ilTextAreaInputGUI($this->lng->txt('event_details_workflow'),'details');
		$details->setValue($this->object->getDetails());
		$details->setCols(50);
		$details->setRows(4);
		$this->form->addItem($details);

		switch($a_mode)
		{
			case 'create':
				$this->form->setTitle($this->lng->txt('event_table_create'));
				$this->form->setTitleIcon(ilUtil::getImagePath('icon_event.gif'));
		
				$this->form->addCommandButton('save',$this->lng->txt('event_btn_add'));
				$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
				return true;
			
			case 'edit':
				$this->form->setTitle($this->lng->txt('event_table_update'));
				$this->form->setTitleIcon(ilUtil::getImagePath('icon_event.gif'));
			
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
		$this->object->getFirstAppointment()->setStartingTime($this->__toUnix($_POST['start']['date'],$_POST['start']['time']));
		$this->object->getFirstAppointment()->setEndingTime($this->__toUnix($_POST['end']['date'],$_POST['end']['time']));
		$this->object->getFirstAppointment()->toggleFullTime((bool) $_POST['fulltime']);

		$counter = 1;
		$this->files = array();
		
		// TODO: files
		/*
		foreach($_FILES as $name => $data)
		{
			if(!strlen($data['tmp_name']))
			{
				++$counter;
				continue;
			}
			$filename = strlen($_POST['file_name'.$counter]) ?
				$_POST['file_name'.$counter] : 
				$data['name'];
			
			$file = new ilEventFile();
			$file->setFileName($filename);
			$file->setFileSize($data['size']);
			$file->setFileType($data['type']);
			$file->setTemporaryName($data['tmp_name']);
			$file->setErrorCode($data['error']);
			$this->files[] = $file;
			++$counter;
		}
		*/

		$this->object->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->setDescription(ilUtil::stripSlashes($_POST['desc']));
		$this->object->setLocation(ilUtil::stripSlashes($_POST['location']));
		$this->object->setName(ilUtil::stripSlashes($_POST['tutor_name']));
		$this->object->setPhone(ilUtil::stripSlashes($_POST['tutor_phone']));
		$this->object->setEmail(ilUtil::stripSlashes($_POST['tutor_email']));
		$this->object->setDetails(ilUtil::stripSlashes($_POST['details']));
		$this->object->enableRegistration((int) $_POST['registration']);
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
	 * format path
	 *
	 * @access protected
	 * @param int ref_id
	 */
	protected function formatPath($a_ref_id)
	{
		global $tree;

		$path = $this->lng->txt('path') . ': ';
		$first = true;
		foreach($tree->getPathFull($a_ref_id,$this->course_ref_id) as $node)
		{
			if($node['ref_id'] != $a_ref_id)
			{
				if(!$first)
				{
					$path .= ' -> ';
				}
				$first = false;
				$path .= $node['title'];
			}
		}
		return $path;
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
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
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
	 	global $ilAccess,$ilTabs;

		#$tabs_gui->setBackTarget($this->lng->txt('back_to_crs_content'),$this->ctrl->getParentReturn($this));
		$tabs_gui->addTarget('info_short',
							 $this->ctrl->getLinkTarget($this,'infoScreen'));

	 	if($ilAccess->checkAccess('write','',$this->object->getRefId()))
	 	{
			$tabs_gui->addTarget('edit_properties',
								 $this->ctrl->getLinkTarget($this,'edit'));
			$tabs_gui->addTarget('crs_materials',
								 $this->ctrl->getLinkTarget($this,'materials'));
			$tabs_gui->addTarget('event_edit_members',
								 $this->ctrl->getLinkTarget($this,'editMembers'));
	 		
	 	}
		// edit permissions
		if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	 	
	}
	
}
?>