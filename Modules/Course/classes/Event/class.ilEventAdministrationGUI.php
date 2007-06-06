<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

/**
* Class ilEventAdministrationGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObjectGUI
*
*/

include_once './Modules/Course/classes/Event/class.ilEventFile.php';

class ilEventAdministrationGUI
{
	var $container_gui;
	var $container_obj;
	var $course_obj;

	var $event_id = null;

	var $tpl;
	var $ctrl;
	var $lng;
	var $tabs_gui;

	/**
	* Constructor
	* @access public
	*/
	function ilEventAdministrationGUI(&$container_gui_obj,$event_id = 0)
	{
		global $tpl,$ilCtrl,$lng,$ilObjDataCache,$ilTabs;

		$this->tpl =& $tpl;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,'event_id');

		$this->lng =& $lng;
		$this->lng->loadLanguageModule('crs');
		$this->lng->loadLanguageModule('trac');
		$this->tabs_gui =& $ilTabs;

		$this->event_id = $event_id;

		$this->container_gui =& $container_gui_obj;
		$this->container_obj =& $this->container_gui->object;

		// 
		$this->__initCourseObject();
		$this->__initEventObject();
	}		

	function &executeCommand()
	{
		global $ilAccess;

		$cmd = $this->ctrl->getCmd();
		switch($this->ctrl->getNextClass($this))
		{
			default:
				if(!$cmd)
				{
					$cmd = 'addEvent';
				}
				$this->$cmd();
				break;
		}
	}

	function cancel()
	{
		#ilUtil::sendInfo($this->lng->txt('msg_cancel',true));
		$this->ctrl->returnToParent($this);
	}

	function register()
	{
		global $ilUser;

		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';
		ilEventParticipants::_register($ilUser->getId(),(int) $_GET['event_id']);

		ilUtil::sendInfo($this->lng->txt('event_registered'),true);
		$this->ctrl->returnToParent($this);
	}
		
	function unregister()
	{
		global $ilUser;

		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';
		ilEventParticipants::_unregister($ilUser->getId(),(int) $_GET['event_id']);

		ilUtil::sendInfo($this->lng->txt('event_unregistered'),true);
		$this->ctrl->returnToParent($this);
	}

	function printViewMembers()
	{
		include_once 'Modules/Course/classes/class.ilCourseMembers.php';
		include_once 'Modules/Course/classes/Event/class.ilEvent.php';
		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';


		global $ilErr,$ilAccess;

		if(!$ilAccess->checkAccess('write','',$this->course_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		$members_obj = new ilCourseMembers($this->course_obj);
		$event_obj = new ilEvent((int) $_GET['event_id']);
		$event_app =& $event_obj->getFirstAppointment();
		$event_part = new ilEventParticipants((int) $_GET['event_id']);
		

		$this->tpl = new ilTemplate('tpl.main.html',true,true);
		// load style sheet depending on user's settings
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$this->tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);

		$tpl = new ilTemplate('tpl.event_members_print.html',true,true,'Modules/Course');

		$tpl->setVariable("EVENT",$this->lng->txt('event'));
		$tpl->setVariable("EVENT_NAME",$event_obj->getTitle());
		$tpl->setVariable("DATE",ilFormat::formatUnixTime($event_app->getStartingTime(),false)." ".
						  $event_app->formatTime());
		$tpl->setVariable("TXT_NAME",$this->lng->txt('name'));
		$tpl->setVariable("TXT_MARK",$this->lng->txt('trac_mark'));
		$tpl->setVariable("TXT_COMMENT",$this->lng->txt('trac_comment'));
		$tpl->setVariable("TXT_PARTICIPATED",$this->lng->txt('event_tbl_participated'));
		if($event_obj->enabledRegistration())
		{
			$tpl->setVariable("TXT_REGISTERED",$this->lng->txt('event_tbl_registered'));
		}

		$members = $members_obj->getAssignedUsers();
		$members = ilUtil::_sortIds($members,'usr_data','lastname','usr_id');
		foreach($members as $user_id)
		{
			
			$user_data = $event_part->getUser($user_id);

			if($event_obj->enabledRegistration())
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
		

	function editMembers()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_members.html','Modules/Course');
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'cancel'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();

		// display print button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'printViewMembers'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('print'));
		$this->tpl->setVariable("BTN_TARGET",'target="_blank"');
		$this->tpl->parseCurrentBlock();

		include_once 'Modules/Course/classes/class.ilCourseMembers.php';
		include_once 'Modules/Course/classes/Event/class.ilEvent.php';
		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';

		$members_obj = new ilCourseMembers($this->course_obj);
		$event_obj = new ilEvent((int) $_GET['event_id']);
		$event_part = new ilEventParticipants((int) $_GET['event_id']);

		$members = $members_obj->getAssignedUsers();
		$members = ilUtil::_sortIds($members,'usr_data','lastname','usr_id');

		$this->tpl->addBlockfile("PARTICIPANTS_TABLE","participants_table", "tpl.table.html");
		$this->tpl->addBlockfile('TBL_CONTENT','tbl_content','tpl.event_members_row.html','Modules/Course');

		// Table 
		$tbl = new ilTableGUI();
		$tbl->setTitle($this->lng->txt("event_tbl_participants"),
					   'icon_usr.gif',
					   $this->lng->txt('obj_usr'));
		$this->ctrl->setParameter($this,'offset',(int) $_GET['offset']);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
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

		if($event_obj->enabledRegistration())
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
		$tbl->render();

		$counter = 0;
		foreach($sliced_users as $user_id)
		{
			$user_data = $event_part->getUser($user_id);

			if($event_obj->enabledRegistration())
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
		$this->tpl->setVariable("SELECT_SPAN",$event_obj->enabledRegistration() ? 4 : 3);
		$this->tpl->setVariable("ROWCLASS",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
		$this->tpl->setVariable("SELECT_ALL",$this->lng->txt('select_all'));
		$this->tpl->parseCurrentBlock();
		
	}

	function updateMembers()
	{
		include_once 'Modules/Course/classes/class.ilCourseMembers.php';
		include_once 'Modules/Course/classes/Event/class.ilEvent.php';
		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';

		$_POST['participants'] = is_array($_POST['participants']) ? $_POST['participants'] : array();

		$members_obj = new ilCourseMembers($this->course_obj);
		$event_part = new ilEventParticipants((int) $_GET['event_id']);

		$members = $members_obj->getAssignedUsers();
		$members = ilUtil::_sortIds($members,'usr_data','lastname','usr_id');
		$sliced_users = array_slice($members,$_GET['offset'],$_SESSION['tbl_limit']);

		foreach($sliced_users as $user)
		{
			$part = new ilEventParticipants((int) $_GET['event_id']);
			$part->setUserId($user);
			$part->setMark(ilUtil::stripSlashes($_POST['mark'][$user]));
			$part->setComment(ilUtil::stripSlashes($_POST['comment'][$user]));
			$part->setParticipated(in_array($user,$_POST['participants']));
			$part->setRegistered(ilEventParticipants::_isRegistered($user,(int) $_GET['event_id']));
			$part->updateUser();
		}
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->editMembers();
	}

	function editUser()
	{
		global $ilObjDataCache;

		include_once 'Modules/Course/classes/Event/class.ilEvent.php';
		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';

		$event_obj = new ilEvent((int) $_GET['event_id']);
		$part_obj = new ilEventParticipants((int) $_GET['event_id']);


		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'editMembers'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.event_edit_user.html','Modules/Course');

		$this->ctrl->setParameter($this,'user_id',(int) $_GET['user_id']);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("USR_IMAGE",ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable("ALT_USER",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("EVENT_TITLE",$event_obj->getTitle());
		$this->tpl->setVariable("FULLNAME",$ilObjDataCache->lookupTitle((int) $_GET['user_id']));
		$this->tpl->setVariable("LOGIN",ilObjUser::_lookupLogin((int) $_GET['user_id']));
		
		$this->tpl->setVariable("TXT_PARTICIPANCE",$this->lng->txt('event_tbl_participated'));
		$this->tpl->setVariable("TXT_REGISTERED",$this->lng->txt('event_tbl_registered'));
		$this->tpl->setVariable("TXT_MARK",$this->lng->txt('trac_mark'));
		$this->tpl->setVariable("TXT_COMMENT",$this->lng->txt('trac_comment'));
		$this->tpl->setVariable("TXT_BTN_UPDATE",$this->lng->txt('save'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));

		$user_data = $part_obj->getUser((int) $_GET['user_id']);
		
		$this->tpl->setVariable("MARK",$user_data['mark']);
		$this->tpl->setVariable("COMMENT",$user_data['comment']);
		$this->tpl->setVariable("PART_CHECKED",$user_data['participated'] ? 'checked="checked"' : '');
		$this->tpl->setVariable("REG_CHECKED",$user_data['registered'] ? 'checked="checked"' : '');
		
	}

	function updateUser()
	{
		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';
		$part_obj = new ilEventParticipants((int) $_GET['event_id']);
		
		$part_obj->setUserId((int) $_GET['user_id']);
		$part_obj->setMark(ilUtil::stripSlashes($_POST['mark']));
		$part_obj->setComment(ilUtil::stripSlashes($_POST['comment']));
		$part_obj->setRegistered($_POST['registration']);
		$part_obj->setParticipated($_POST['participance']);
		$part_obj->updateUser((int) $_GET['user_id']);

		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->editMembers();
	}


	function materials()
	{
		global $tree, $objDefinition;

		include_once 'Modules/Course/classes/Event/class.ilEventItems.php';
		$this->event_items = new ilEventItems($this->event_id);
		$items = $this->event_items->getItems();

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_materials.html','Modules/Course');
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'cancel'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("COLL_TITLE_IMG",ilUtil::getImagePath('icon_event.gif'));
		$this->tpl->setVariable("COLL_TITLE_IMG_ALT",$this->lng->txt('events'));
		$this->tpl->setVariable("TABLE_TITLE",$this->lng->txt('event_assign_materials_table'));
		$this->tpl->setVariable("TABLE_INFO",$this->lng->txt('event_assign_materials_info'));

		$nodes = $tree->getSubTree($tree->getNodeData($this->course_obj->getRefId()));
		$counter = 1;
		foreach($nodes as $node)
		{
			// No side blocks here
			if ($objDefinition->isSideBlock($node['type']))
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
			$this->tpl->setVariable("COLL_PATH",$this->__formatPath($node['ref_id']));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("SELECT_ROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
		$this->tpl->setVariable("SELECT_ALL",$this->lng->txt('select_all'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("BTN_SAVE",$this->lng->txt('save'));

	}

	function saveMaterials()
	{
		include_once 'Modules/Course/classes/Event/class.ilEventItems.php';
		
		$this->event_items = new ilEventItems($this->event_id);
		$this->event_items->setItems(is_array($_POST['items']) ? $_POST['items'] : array());
		$this->event_items->update();

		ilUtil::sendInfo($this->lng->txt('settings_saved'),true);
		$this->ctrl->returnToParent($this);
	}
		
		

	function info()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_info.html','Modules/Course');

		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'cancel'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();

		
		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		
		$appointment_obj =& $this->event_obj->getFirstAppointment();
		

		// syllabus section
		$info->addSection($this->lng->txt("event_general_properties"));
		$info->addProperty($this->lng->txt('event_title'),
						   $this->event_obj->getTitle());
		if(strlen($desc = $this->event_obj->getDescription()))
		{
			$info->addProperty($this->lng->txt('event_desc'),
							   nl2br($this->event_obj->getDescription()));
		}
		if(strlen($location = $this->event_obj->getLocation()))
		{
			$info->addProperty($this->lng->txt('event_location'),
							   nl2br($this->event_obj->getLocation()));
		}
		$info->addProperty($this->lng->txt('event_date'),
							$appointment_obj->appointmentToString());

		if($this->event_obj->hasTutorSettings())
		{
			$info->addSection($this->lng->txt('event_tutor_data'));
			if(strlen($fullname = $this->event_obj->getFullname()))
			{
				$info->addProperty($this->lng->txt('event_lecturer'),
								   $fullname);
			}
			if(strlen($email = $this->event_obj->getEmail()))
			{
				$info->addProperty($this->lng->txt('tutor_email'),
								   $email);
			}
			if(strlen($phone = $this->event_obj->getPhone()))
			{
				$info->addProperty($this->lng->txt('tutor_phone'),
								   $phone);
			}
		}

		$details = $this->event_obj->getDetails();
		$files = $this->event_obj->getFiles();

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
				
		$this->tpl->setVariable("INFO_TABLE",$info->getHTML());
	}

	function sendFile()
	{
		$file = new ilEventFile((int) $_GET['file_id']);
		
		ilUtil::deliverFile($file->getAbsolutePath(),$file->getFileName(),$file->getFileType());
		return true;
	}

	function addEvent()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_create.html','Modules/Course');

		// display back button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'cancel'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('event_table_create'));
		$this->tpl->setVariable("TXT_GENERAL_INFOS",$this->lng->txt('event_general_infos'));
		$this->tpl->setVariable("TXT_BTN_ADD_EVENT",$this->lng->txt('event_btn_add'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('event_title'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('event_desc'));
		$this->tpl->setVariable("TXT_LOCATION",$this->lng->txt('event_location'));
		$this->tpl->setVariable("TXT_REGISTRATION_PARTICIPANCE",$this->lng->txt('event_registration_participance'));
		$this->tpl->setVariable("TXT_REGISTRATION",$this->lng->txt('event_registration'));
		$this->tpl->setVariable("TXT_PARTICIPANCE",$this->lng->txt('event_participation'));
		$this->tpl->setVariable("REGISTRATION_INFO",$this->lng->txt('event_registration_info'));
		$this->tpl->setVariable("PARTICIPATION_INFO",$this->lng->txt('event_participation_info'));


		$this->tpl->setVariable("TXT_REQUIRED",$this->lng->txt('required_field'));
		$this->tpl->setVariable("TXT_TUTOR_DATA",$this->lng->txt('event_tutor_data'));
		#$this->tpl->setVariable("TXT_TUTOR_TITLE",$this->lng->txt('tutor_title'));
		#$this->tpl->setVariable("TXT_TUTOR_FIRSTNAME",$this->lng->txt('tutor_firstname'));
		$this->tpl->setVariable("TXT_TUTOR_NAME",$this->lng->txt('tutor_name'));
		$this->tpl->setVariable("TXT_TUTOR_EMAIL",$this->lng->txt('tutor_email'));
		$this->tpl->setVariable("TXT_TUTOR_PHONE",$this->lng->txt('tutor_phone'));

		$this->tpl->setVariable("TXT_DATE_TIME",$this->lng->txt('event_date_time'));
		$this->tpl->setVariable("TXT_START_DATE",$this->lng->txt('event_start_date'));
		$this->tpl->setVariable("TXT_END_DATE",$this->lng->txt('event_end_date'));

		$this->tpl->setVariable("TXT_TIME",$this->lng->txt('event_time'));
	
		$this->tpl->setVariable("TXT_TUTOR_NAME",$this->lng->txt('tutor_name'));
		$this->tpl->setVariable("TXT_TUTOR_EMAIL",$this->lng->txt('tutor_email'));
		$this->tpl->setVariable("TXT_TUTOR_PHONE",$this->lng->txt('tutor_phone'));
		$this->tpl->setVariable("TXT_START_DATE",$this->lng->txt('event_start_date'));
		$this->tpl->setVariable("TXT_TIME",$this->lng->txt('event_time'));
		$this->tpl->setVariable("TXT_DATE",$this->lng->txt('date'));
		$this->tpl->setVariable("TXT_FULLDAY",$this->lng->txt('event_fullday'));
		$this->tpl->setVariable("FULLTIME_INFO",$this->lng->txt('event_fulltime_info'));

		$this->tpl->setVariable("FULL_CHECKED",$this->appointment_obj->enabledFullTime() ? 'checked="checked"' : '');
		$date = $this->__prepareDateSelect($this->appointment_obj->getStartingTime());
		$end_date = $this->__prepareDateSelect($this->appointment_obj->getEndingTime());
		$start_time = $this->__prepareTimeSelect($this->appointment_obj->getStartingTime());
		$end_time = $this->__prepareTimeSelect($this->appointment_obj->getEndingTime());

		$this->tpl->setVariable("START_DATE",ilUtil::makeDateSelect('event_date',$date['y'],$date['m'],$date['d'],date('Y',time())));
		$this->tpl->setVariable("START_TIME",ilUtil::makeTimeSelect('event_time_start',true,$start_time['h'],$start_time['m'],0,false));
		$this->tpl->setVariable("END_DATE",
								ilUtil::makeDateSelect('event_end_date',$end_date['y'],$end_date['m'],$end_date['d'],date('Y',time())));
		$this->tpl->setVariable("END_TIME",ilUtil::makeTimeSelect('event_time_end',true,$end_time['h'],$end_time['m'],0,false));

		$this->tpl->setVariable("TITLE",$this->event_obj->getTitle());
		$this->tpl->setVariable("DESC",$this->event_obj->getDescription());
		$this->tpl->setVariable("LOCATION",$this->event_obj->getLocation());
		$this->tpl->setVariable("REG_CHECKED",$this->event_obj->enabledRegistration() ? 'checked="checked"' : '');
		$this->tpl->setVariable("PART_CHECKED",$this->event_obj->enabledParticipation() ? 'checked="checked"' : '');
		#$this->tpl->setVariable("TUTOR_FIRSTNAME",$this->event_obj->getFirstname());
		$this->tpl->setVariable("TUTOR_NAME",$this->event_obj->getName());
		#$this->tpl->setVariable("TUTOR_TITLE",$this->event_obj->getPTitle());
		$this->tpl->setVariable("TUTOR_EMAIL",$this->event_obj->getEmail());
		$this->tpl->setVariable("TUTOR_PHONE",$this->event_obj->getPhone());
		$this->tpl->setVariable("DETAILS",$this->event_obj->getDetails());

		$this->tpl->setVariable("TXT_FURTHER_INFORMATIONS",$this->lng->txt('event_further_informations'));
		$this->tpl->setVariable("TXT_FILE_NAME",$this->lng->txt('event_file_name'));
		$this->tpl->setVariable("TXT_FILE",$this->lng->txt('event_file'));
		$this->tpl->setVariable("FILE_HINT",$this->lng->txt('if_no_title_then_filename'));
		$this->tpl->setVariable("TXT_DETAILS",$this->lng->txt('event_details_workflow'));
		$this->tpl->setVariable("TXT_FILESIZE",ilUtil::getFileSizeInfo());


		return true;
	}

	function edit()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_edit.html','Modules/Course');

		// display back button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'cancel'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();


		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('event_table_update'));
		$this->tpl->setVariable("TXT_GENERAL_INFOS",$this->lng->txt('event_general_infos'));
		$this->tpl->setVariable("TXT_BTN_UPDATE",$this->lng->txt('save'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('event_title'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('event_desc'));
		$this->tpl->setVariable("TXT_LOCATION",$this->lng->txt('event_location'));
		$this->tpl->setVariable("TXT_REGISTRATION_PARTICIPANCE",$this->lng->txt('event_registration_participance'));
		$this->tpl->setVariable("TXT_REGISTRATION",$this->lng->txt('event_registration'));
		$this->tpl->setVariable("TXT_PARTICIPANCE",$this->lng->txt('event_participation'));
		$this->tpl->setVariable("REGISTRATION_INFO",$this->lng->txt('event_registration_info'));
		$this->tpl->setVariable("PARTICIPATION_INFO",$this->lng->txt('event_participation_info'));

		$this->tpl->setVariable("TXT_DATE_TIME",$this->lng->txt('event_date_time'));
		$this->tpl->setVariable("TXT_START_DATE",$this->lng->txt('event_start_date'));
		$this->tpl->setVariable("TXT_END_DATE",$this->lng->txt('event_end_date'));

		$this->tpl->setVariable("TXT_REQUIRED",$this->lng->txt('required_field'));
		$this->tpl->setVariable("TXT_TUTOR_DATA",$this->lng->txt('event_tutor_data'));
		#$this->tpl->setVariable("TXT_TUTOR_TITLE",$this->lng->txt('tutor_title'));
		#$this->tpl->setVariable("TXT_TUTOR_FIRSTNAME",$this->lng->txt('tutor_firstname'));
		#$this->tpl->setVariable("TXT_TUTOR_LASTNAME",$this->lng->txt('tutor_lastname'));
		$this->tpl->setVariable("TXT_TUTOR_NAME",$this->lng->txt('tutor_name'));
		$this->tpl->setVariable("TXT_TUTOR_EMAIL",$this->lng->txt('tutor_email'));
		$this->tpl->setVariable("TXT_TUTOR_PHONE",$this->lng->txt('tutor_phone'));
		$this->tpl->setVariable("TXT_START_DATE",$this->lng->txt('event_start_date'));
		$this->tpl->setVariable("TXT_TIME",$this->lng->txt('event_time'));
		$this->tpl->setVariable("TXT_DATE",$this->lng->txt('date'));
		$this->tpl->setVariable("TXT_FULLDAY",$this->lng->txt('event_fullday'));
		$this->tpl->setVariable("FULLTIME_INFO",$this->lng->txt('event_fulltime_info'));


		$this->tpl->setVariable("FULL_CHECKED",$this->appointment_obj->enabledFullTime() ? 'checked="checked"' : '');
		$date = $this->__prepareDateSelect($this->appointment_obj->getStartingTime());
		$end_date = $this->__prepareDateSelect($this->appointment_obj->getEndingTime());
		$start_time = $this->__prepareTimeSelect($this->appointment_obj->getStartingTime());
		$end_time = $this->__prepareTimeSelect($this->appointment_obj->getEndingTime());

		$this->tpl->setVariable("START_DATE",ilUtil::makeDateSelect('event_date',$date['y'],$date['m'],$date['d'],date('Y',time())));
		$this->tpl->setVariable("START_TIME",ilUtil::makeTimeSelect('event_time_start',true,$start_time['h'],$start_time['m'],0,false));
		$this->tpl->setVariable("END_DATE",
								ilUtil::makeDateSelect('event_end_date',$end_date['y'],$end_date['m'],$end_date['d'],date('Y',time())));
		$this->tpl->setVariable("END_TIME",ilUtil::makeTimeSelect('event_time_end',true,$end_time['h'],$end_time['m'],0,false));

		$this->tpl->setVariable("TITLE",$this->event_obj->getTitle());
		$this->tpl->setVariable("DESC",$this->event_obj->getDescription());
		$this->tpl->setVariable("LOCATION",$this->event_obj->getLocation());
		$this->tpl->setVariable("REG_CHECKED",$this->event_obj->enabledRegistration() ? 'checked="checked"' : '');
		$this->tpl->setVariable("PART_CHECKED",$this->event_obj->enabledParticipation() ? 'checked="checked"' : '');
		#$this->tpl->setVariable("TUTOR_FIRSTNAME",$this->event_obj->getFirstname());
		#$this->tpl->setVariable("TUTOR_LASTNAME",$this->event_obj->getLastname());
		#$this->tpl->setVariable("TUTOR_TITLE",$this->event_obj->getPTitle());
		$this->tpl->setVariable("TUTOR_NAME",$this->event_obj->getName());
		$this->tpl->setVariable("TUTOR_EMAIL",$this->event_obj->getEmail());
		$this->tpl->setVariable("TUTOR_PHONE",$this->event_obj->getPhone());
		$this->tpl->setVariable("DETAILS",$this->event_obj->getDetails());

		$this->tpl->setVariable("TXT_FURTHER_INFORMATIONS",$this->lng->txt('event_further_informations'));
		$this->tpl->setVariable("TXT_FILE_NAME",$this->lng->txt('event_file_name'));
		$this->tpl->setVariable("TXT_FILE",$this->lng->txt('event_file'));
		$this->tpl->setVariable("FILE_HINT",$this->lng->txt('if_no_title_then_filename'));
		$this->tpl->setVariable("TXT_DETAILS",$this->lng->txt('event_details_workflow'));

		foreach($file_objs =& ilEventFile::_readFilesByEvent($this->event_id) as $file_obj)
		{
			$this->tpl->setCurrentBlock("file");
			$this->tpl->setVariable("FILE_ID",$file_obj->getFileId());
			$this->tpl->setVariable("DEL_FILE",$file_obj->getFileName());
			$this->tpl->setVariable("TXT_DEL_FILE",$this->lng->txt('event_delete_file'));
			$this->tpl->parseCurrentBlock();
		}
		if(count($file_objs))
		{
			$this->tpl->setCurrentBlock("files");
			$this->tpl->setVariable("TXT_EXISTING_FILES",$this->lng->txt('event_existing_files'));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_FILESIZE",ilUtil::getFileSizeInfo());

		return true;
	}

	function update()
	{
		global $ilErr;

		$this->__load();

		$ilErr->setMessage('');
		$this->event_obj->validate();
		$this->appointment_obj->validate();
		$this->file_obj->validate();

		if(strlen($ilErr->getMessage()))
		{
			ilUtil::sendInfo($ilErr->getMessage());
			$this->edit();
			return false;
		}
		// Update event
		$this->event_obj->update();

		// create appointment
		$this->appointment_obj->update();

		// Create file
		$this->file_obj->setEventId($this->event_obj->getEventId());
		$this->file_obj->create();

		// Todo delete files
		if(count($_POST['del_files']))
		{
			foreach($this->event_obj->getFiles() as $file_obj)
			{
				if(in_array($file_obj->getFileId(),$_POST['del_files']))
				{
					$file_obj->delete();
				}
			}
		}
		// Reread file objects
		$this->event_obj->readFiles();

		ilUtil::sendInfo($this->lng->txt('event_updated'));
		$this->edit();
		return true;
	}

	function createEvent()
	{
		global $ilErr;

		$this->__load();

		$ilErr->setMessage('');
		$this->event_obj->validate();
		$this->appointment_obj->validate();
		$this->file_obj->validate();

		if(strlen($ilErr->getMessage()))
		{
			ilUtil::sendInfo($ilErr->getMessage());
			$this->addEvent();
			return false;
		}
		// Create event
		$event_id = $this->event_obj->create();

		// create appointment
		$this->appointment_obj->setEventId($event_id);
		$this->appointment_obj->create();

		// Create file
		$this->file_obj->setEventId($event_id);
		$this->file_obj->create();

		ilUtil::sendInfo($this->lng->txt('event_add_new_event'),true);
		$this->ctrl->returnToParent($this);
		return true;
	}

	function confirmDelete()
	{
		include_once './Modules/Course/classes/Event/class.ilEvent.php';

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_delete.html','Modules/Course');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_EVENT_NAME",$this->lng->txt('title'));
		$this->tpl->setVariable("DELETE",$this->lng->txt('delete'));
		$this->tpl->setVariable("CANCEL",$this->lng->txt('cancel'));
		

		$events = is_array($_POST['event_ids']) ? $_POST['event_ids'] : array($this->event_id);
		$_SESSION['event_del'] = $events;
		$counter = 0;
		foreach($events as $event)
		{
			$event_obj = new ilEvent($event);
			if(strlen($desc = $event_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("desc");
				$this->tpl->setVariable("DESCRIPTION",$desc);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("events");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$this->tpl->setVariable("EVENT_NAME",$event_obj->getTitle());
			$this->tpl->parseCurrentBlock();
		}
		ilUtil::sendInfo($this->lng->txt('event_delete_sure'));
		return true;
	}

	function delete()
	{
		include_once './Modules/Course/classes/Event/class.ilEvent.php';

		if(!is_array($_SESSION['event_del']))
		{
			ilUtil::sendInfo($this->lng->txt('event_none_selected'));
			$this->ctrl->returnToParent($this);
			return false;
		}
		foreach($_SESSION['event_del'] as $event_id)
		{
			ilEvent::_delete($event_id);
		}

		ilUtil::sendInfo($this->lng->txt('events_deleted'),true);
		$this->ctrl->returnToParent($this);

		return true;
	}
	
	/**
	 * Events List CSV Export
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function exportCSV()
	{
		include_once('Services/Utilities/classes/class.ilCSVWriter.php');
		include_once 'Modules/Course/classes/class.ilCourseMembers.php';
		include_once 'Modules/Course/classes/Event/class.ilEvent.php';
		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';
		
		$members_obj = new ilCourseMembers($this->course_obj);		
		$members = $members_obj->getAssignedUsers();
		$members = ilUtil::_sortIds($members,'usr_data','lastname','usr_id');		
		
		$events = ilEvent::_getEvents($this->course_obj->getId());
		
		$this->csv = new ilCSVWriter();
		$this->csv->addColumn($this->lng->txt("lastname"));
		$this->csv->addColumn($this->lng->txt("firstname"));
		$this->csv->addColumn($this->lng->txt("login"));
		
		foreach($events as $event_obj)
		{			
			$this->csv->addColumn($event_obj->getTitle());			
		}
		
		$this->csv->addRow();
		
		foreach($members as $user_id)
		{
			$name = ilObjUser::_lookupName($user_id);
			
			$this->csv->addColumn($name['lastname']);
			$this->csv->addColumn($name['firstname']);
			$this->csv->addColumn(ilObjUser::_lookupLogin($user_id));
			
			foreach($events as $event_obj)
			{			
				$event_part = new ilEventParticipants((int) $event_obj->getEventId());
				
				$this->csv->addColumn($event_part->hasParticipated($user_id) ?
										$this->lng->txt('event_participated') :
										$this->lng->txt('event_not_participated'));
			}
			
			$this->csv->addRow();
		}
		
		ilUtil::deliverData($this->csv->getCSVString(), date("Y_m_d")."_course_events.csv", "text/csv");		
	}
	
	/**
	 * Events List
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function eventsList()
	{			
		global $ilErr,$ilAccess, $ilUser;

		if(!$ilAccess->checkAccess('write','',$this->course_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_list.html','Modules/Course');
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'exportCSV'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('event_csv_export'));
		$this->tpl->parseCurrentBlock();
				
		include_once 'Modules/Course/classes/class.ilCourseMembers.php';
		include_once 'Modules/Course/classes/Event/class.ilEvent.php';
		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';
		
		$this->tpl->addBlockfile("EVENTS_TABLE","events_table", "tpl.table.html");
		$this->tpl->addBlockfile('TBL_CONTENT','tbl_content','tpl.event_list_row.html','Modules/Course');
		
		$members_obj = new ilCourseMembers($this->course_obj);		
		$members = $members_obj->getAssignedUsers();
		$members = ilUtil::_sortIds($members,'usr_data','lastname','usr_id');		
		
		// Table 
		$tbl = new ilTableGUI();
		$tbl->setTitle($this->lng->txt("event_overview"),
					   'icon_usr.gif',
					   $this->lng->txt('obj_usr'));
		$this->ctrl->setParameter($this,'offset',(int) $_GET['offset']);	
		
		$events = ilEvent::_getEvents($this->course_obj->getId());		
		
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
							
				$event_part = new ilEventParticipants((int) $event_obj->getEventId());														
										
				if ($event_obj->enabledParticipation())
				{			
					$this->tpl->setVariable("IMAGE_PARTICIPATED", $event_part->hasParticipated($user_id) ? 
											ilUtil::getImagePath('icon_ok.gif') :
											ilUtil::getImagePath('icon_not_ok.gif'));
					
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


	function __load()
	{
		$this->appointment_obj->setStartingTime($this->__toUnix($_POST['event_date'],$_POST['event_time_start']));
		$this->appointment_obj->setEndingTime($this->__toUnix($_POST['event_end_date'],$_POST['event_time_end']));
		$this->appointment_obj->toggleFullTime((bool) $_POST['fulltime']);

		$this->file_obj = new ilEventFile();
		$this->file_obj->setFileName(strlen($_POST['file_name']) ?
							   ilUtil::stripSlashes($_POST['file_name']) :
							   $_FILES['file']['name']);
		$this->file_obj->setFileSize($_FILES['file']['size']);
		$this->file_obj->setFileType($_FILES['file']['type']);
		$this->file_obj->setTemporaryName($_FILES['file']['tmp_name']);
		$this->file_obj->setErrorCode($_FILES['file']['error']);
							   
		

		$this->event_obj->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->event_obj->setDescription(ilUtil::stripSlashes($_POST['desc']));
		$this->event_obj->setLocation(ilUtil::stripSlashes($_POST['location']));
		#$this->event_obj->setFirstname(ilUtil::stripSlashes($_POST['tutor_firstname']));
		$this->event_obj->setName(ilUtil::stripSlashes($_POST['tutor_name']));
		#$this->event_obj->setPTitle(ilUtil::stripSlashes($_POST['tutor_title']));
		$this->event_obj->setEmail(ilUtil::stripSlashes($_POST['tutor_email']));
		$this->event_obj->setPhone(ilUtil::stripSlashes($_POST['tutor_phone']));
		$this->event_obj->setDetails(ilUtil::stripSlashes($_POST['details']));
		$this->event_obj->enableRegistration((int) $_POST['registration']);
		$this->event_obj->enableParticipation((int) $_POST['participance']);
	}


	function __initCourseObject()
	{
		global $tree;

		if($this->container_obj->getType() == 'crs')
		{
			// Container is course
			$this->course_obj =& $this->container_obj;
		}
		else
		{
			$course_ref_id = $tree->checkForParentType($this->container_obj->getRefId(),'crs');
			$this->course_obj =& ilObjectFactory::getInstanceByRefId($course_ref_id);
		}
		return true;
	}

	function __initEventObject()
	{
		if(!is_object($this->event_obj))
		{
			include_once 'Modules/Course/classes/Event/class.ilEvent.php';

			$this->event_obj = new ilEvent($this->event_id);
			$this->event_obj->setObjId($this->container_obj->getId());

			if(!is_object($this->appointment_obj))
			{
				$this->appointment_obj =& $this->event_obj->getFirstAppointment();
			}
		}
		return true;
	}

	function __prepareDateSelect($a_unix_time)
	{
		return array('y' => date('Y',$a_unix_time),
					 'm' => date('m',$a_unix_time),
					 'd' => date('d',$a_unix_time));
	}
	function __prepareTimeSelect($a_unix_time)
	{
		return array('h' => date('G',$a_unix_time),
					 'm' => date('i',$a_unix_time),
					 's' => date('s',$a_unix_time));
	}
	function __toUnix($date,$time)
	{
		return mktime($time['h'],$time['m'],0,$date['m'],$date['d'],$date['y']);
	}

	function __formatPath($a_ref_id)
	{
		global $tree;

		$path = $this->lng->txt('path') . ': ';
		$first = true;
		foreach($tree->getPathFull($a_ref_id,$this->course_obj->getRefId()) as $node)
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
			

} // END class.ilCourseContentGUI
?>
