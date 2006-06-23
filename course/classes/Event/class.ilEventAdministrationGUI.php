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
* @package ilias-core
*
*/

include_once './course/classes/Event/class.ilEventFile.php';

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
	function ilEventAdministrationGUI(&$container_gui_obj,$event_id)
	{
		global $tpl,$ilCtrl,$lng,$ilObjDataCache,$ilTabs;

		$this->tpl =& $tpl;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,'event_id');

		$this->lng =& $lng;
		$this->lng->loadLanguageModule('crs');
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
		$this->tabs_gui->clearSubTabs();
	}

	function cancel()
	{
		#sendInfo($this->lng->txt('msg_cancel',true));
		$this->ctrl->returnToParent($this);
	}

	function materials()
	{
		global $tree;

		include_once 'course/classes/Event/class.ilEventItems.php';
		$this->event_items = new ilEventItems($this->event_id);
		$items = $this->event_items->getItems();

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_materials.html','course');
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
		include_once 'course/classes/Event/class.ilEventItems.php';
		
		$this->event_items = new ilEventItems($this->event_id);
		$this->event_items->setItems(is_array($_POST['items']) ? $_POST['items'] : array());
		$this->event_items->update();

		sendInfo($this->lng->txt('settings_saved'),true);
		$this->ctrl->returnToParent($this);
	}
		
		

	function info()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_info.html','course');

		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'cancel'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();

		
		include_once("classes/class.ilInfoScreenGUI.php");
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
						   ilFormat::formatUnixTime($appointment_obj->getStartingTime(),false)." ".
						   $appointment_obj->formatTime());

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
				$info->addProperty($this->lng->txt('event_email'),
								   $email);
			}
			if(strlen($phone = $this->event_obj->getPhone()))
			{
				$info->addProperty($this->lng->txt('event_phone'),
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
				$tpl = new ilTemplate('tpl.event_info_file.html',true,true,'course');

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
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_create.html','course');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('event_table_create'));
		$this->tpl->setVariable("TXT_GENERAL_INFOS",$this->lng->txt('event_general_infos'));
		$this->tpl->setVariable("TXT_BTN_ADD_EVENT",$this->lng->txt('event_btn_add'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('event_title'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('event_desc'));
		$this->tpl->setVariable("TXT_LOCATION",$this->lng->txt('event_location'));

		$this->tpl->setVariable("TXT_REQUIRED",$this->lng->txt('required_field'));
		$this->tpl->setVariable("TXT_TUTOR_DATA",$this->lng->txt('event_tutor_data'));
		$this->tpl->setVariable("TXT_TUTOR_TITLE",$this->lng->txt('tutor_title'));
		$this->tpl->setVariable("TXT_TUTOR_FIRSTNAME",$this->lng->txt('tutor_firstname'));
		$this->tpl->setVariable("TXT_TUTOR_LASTNAME",$this->lng->txt('tutor_lastname'));
		$this->tpl->setVariable("TXT_TUTOR_EMAIL",$this->lng->txt('tutor_email'));
		$this->tpl->setVariable("TXT_TUTOR_PHONE",$this->lng->txt('tutor_phone'));
		$this->tpl->setVariable("TXT_START_DATE",$this->lng->txt('event_start_date'));
		$this->tpl->setVariable("TXT_TIME",$this->lng->txt('event_time'));

		$appointment_obj =& $this->event_obj->getFirstAppointment();
		
		$date = $this->__prepareDateSelect($appointment_obj->getStartingTime());
		$start_time = $this->__prepareTimeSelect($appointment_obj->getStartingTime());
		$end_time = $this->__prepareTimeSelect($appointment_obj->getEndingTime());

		$this->tpl->setVariable("START_DATE",ilUtil::makeDateSelect('event_date',$date['y'],$date['m'],$date['d'],date('Y',time())));
		$this->tpl->setVariable("START_TIME",ilUtil::makeTimeSelect('event_time_start',true,$start_time['h'],$start_time['m'],0,false));
		$this->tpl->setVariable("END_TIME",ilUtil::makeTimeSelect('event_time_end',true,$end_time['h'],$end_time['m'],0,false));

		$this->tpl->setVariable("TITLE",$this->event_obj->getTitle());
		$this->tpl->setVariable("DESC",$this->event_obj->getDescription());
		$this->tpl->setVariable("LOCATION",$this->event_obj->getLocation());
		$this->tpl->setVariable("TUTOR_FIRSTNAME",$this->event_obj->getFirstname());
		$this->tpl->setVariable("TUTOR_LASTNAME",$this->event_obj->getLastname());
		$this->tpl->setVariable("TUTOR_TITLE",$this->event_obj->getPTitle());
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
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_edit.html','course');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('event_table_create'));
		$this->tpl->setVariable("TXT_GENERAL_INFOS",$this->lng->txt('event_general_infos'));
		$this->tpl->setVariable("TXT_BTN_UPDATE",$this->lng->txt('save'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('event_title'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('event_desc'));
		$this->tpl->setVariable("TXT_LOCATION",$this->lng->txt('event_location'));

		$this->tpl->setVariable("TXT_REQUIRED",$this->lng->txt('required_field'));
		$this->tpl->setVariable("TXT_TUTOR_DATA",$this->lng->txt('event_tutor_data'));
		$this->tpl->setVariable("TXT_TUTOR_TITLE",$this->lng->txt('tutor_title'));
		$this->tpl->setVariable("TXT_TUTOR_FIRSTNAME",$this->lng->txt('tutor_firstname'));
		$this->tpl->setVariable("TXT_TUTOR_LASTNAME",$this->lng->txt('tutor_lastname'));
		$this->tpl->setVariable("TXT_TUTOR_EMAIL",$this->lng->txt('tutor_email'));
		$this->tpl->setVariable("TXT_TUTOR_PHONE",$this->lng->txt('tutor_phone'));
		$this->tpl->setVariable("TXT_START_DATE",$this->lng->txt('event_start_date'));
		$this->tpl->setVariable("TXT_TIME",$this->lng->txt('event_time'));

		$appointment_obj =& $this->event_obj->getFirstAppointment();
		
		$date = $this->__prepareDateSelect($appointment_obj->getStartingTime());
		$start_time = $this->__prepareTimeSelect($appointment_obj->getStartingTime());
		$end_time = $this->__prepareTimeSelect($appointment_obj->getEndingTime());

		$this->tpl->setVariable("START_DATE",ilUtil::makeDateSelect('event_date',$date['y'],$date['m'],$date['d'],date('Y',time())));
		$this->tpl->setVariable("START_TIME",ilUtil::makeTimeSelect('event_time_start',true,$start_time['h'],$start_time['m'],0,false));
		$this->tpl->setVariable("END_TIME",ilUtil::makeTimeSelect('event_time_end',true,$end_time['h'],$end_time['m'],0,false));

		$this->tpl->setVariable("TITLE",$this->event_obj->getTitle());
		$this->tpl->setVariable("DESC",$this->event_obj->getDescription());
		$this->tpl->setVariable("LOCATION",$this->event_obj->getLocation());
		$this->tpl->setVariable("TUTOR_FIRSTNAME",$this->event_obj->getFirstname());
		$this->tpl->setVariable("TUTOR_LASTNAME",$this->event_obj->getLastname());
		$this->tpl->setVariable("TUTOR_TITLE",$this->event_obj->getPTitle());
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
			sendInfo($ilErr->getMessage());
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

		sendInfo($this->lng->txt('event_updated'));
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
			sendInfo($ilErr->getMessage());
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

		sendInfo($this->lng->txt('event_add_new_event'));
		$this->ctrl->returnToParent($this);
		return true;
	}

	function confirmDelete()
	{
		include_once './course/classes/Event/class.ilEvent.php';

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.event_delete.html','course');
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
		sendInfo($this->lng->txt('event_delete_sure'));
		return true;
	}

	function delete()
	{
		include_once './course/classes/Event/class.ilEvent.php';

		if(!is_array($_SESSION['event_del']))
		{
			sendInfo($this->lng->txt('event_none_selected'));
			$this->ctrl->returnToParent($this);
			return false;
		}
		foreach($_SESSION['event_del'] as $event_id)
		{
			ilEvent::_delete($event_id);
		}

		sendInfo($this->lng->txt('events_deleted'));
		$this->ctrl->returnToParent($this);

		return true;
	}


	function __load()
	{
		$this->appointment_obj =& $this->event_obj->getFirstAppointment();
		$this->appointment_obj->setStartingTime($this->__toUnix($_POST['event_date'],$_POST['event_time_start']));
		$this->appointment_obj->setEndingTime($this->__toUnix($_POST['event_date'],$_POST['event_time_end']));

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
		$this->event_obj->setFirstname(ilUtil::stripSlashes($_POST['tutor_firstname']));
		$this->event_obj->setLastname(ilUtil::stripSlashes($_POST['tutor_lastname']));
		$this->event_obj->setPTitle(ilUtil::stripSlashes($_POST['tutor_title']));
		$this->event_obj->setEmail(ilUtil::stripSlashes($_POST['tutor_email']));
		$this->event_obj->setPhone(ilUtil::stripSlashes($_POST['tutor_phone']));
		$this->event_obj->setDetails(ilUtil::stripSlashes($_POST['details']));
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
			include_once 'course/classes/Event/class.ilEvent.php';

			$this->event_obj = new ilEvent($this->event_id);
			$this->event_obj->setObjId($this->container_obj->getId());
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
