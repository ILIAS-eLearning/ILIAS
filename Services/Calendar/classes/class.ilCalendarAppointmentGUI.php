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

/**
* Administrate calendar appointments 
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

include_once('./Services/Calendar/classes/class.ilTimeZone.php');
include_once('./Services/Calendar/classes/class.ilDateTime.php');

class ilCalendarAppointmentGUI
{
	protected $seed = null;
	protected $default_fulltime = true;
	
	protected $app = null;
	protected $rec = null;
	protected $timezone = null;
	
	protected $tpl;
	protected $lng;
	protected $ctrl;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param ilDate seed
	 * @return
	 */
	public function __construct(ilDate $seed,$a_appointment_id = 0)
	{
		global $ilCtrl,$lng;
		
		$this->lng = $lng;
		$lng->loadLanguageModule('dateplaner');
		$this->ctrl = $ilCtrl;

		$this->initTimeZone();
		$this->initSeed($seed);
		$this->initAppointment($a_appointment_id);
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function executeCommand()
	{
		global $ilUser, $ilSetting,$tpl;

		$next_class = $this->ctrl->getNextClass($this);
		switch($next_class)
		{
			
			default:
				$cmd = $this->ctrl->getCmd("add");
				$this->$cmd();
				break;
		}
		return true;
	}
	
	/**
	 * cancel editing
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function cancel()
	{
		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * init form
	 *
	 * @access protected
	 * @param string mode ('edit' | 'create')
	 * @return
	 */
	protected function initForm($a_mode, $a_as_milestone = false)
	{
		global $ilUser,$tpl;
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		include_once('./Services/Calendar/classes/class.ilCalendarRecurrenceGUI.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');

		$this->form = new ilPropertyFormGUI();
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initDomEvent();
		$resp_info = false;
		switch($a_mode)
		{
			case 'create':
				$this->ctrl->saveParameter($this,array('seed'));
				$this->form->setFormAction($this->ctrl->getFormAction($this));
				if ($a_as_milestone)
				{
					$this->form->setTitle($this->lng->txt('cal_new_ms'));
					$this->form->addCommandButton('saveMilestone',$this->lng->txt('cal_add_milestone'));
					$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				}
				else
				{
					$this->form->setTitle($this->lng->txt('cal_new_app'));
					$this->form->addCommandButton('save',$this->lng->txt('cal_add_appointment'));
					$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				}
				break;

			case 'edit':
				if ($a_as_milestone)
				{
					$this->form->setTitle($this->lng->txt('cal_edit_milestone'));
				}
				else
				{
					$this->form->setTitle($this->lng->txt('cal_edit_appointment'));
				}
				$this->ctrl->saveParameter($this,array('seed','app_id'));
				$this->form->setFormAction($this->ctrl->getFormAction($this));
				
				$ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
				$cat = $ass->getFirstAssignment();
				include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
				$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat);
				$type = ilObject::_lookupType($cat_info['obj_id']);
				if ($a_as_milestone && $cat_info['type'] == ilCalendarCategory::TYPE_OBJ
					&& ($type == "grp" || $type == "crs"))
				{
					$resp_info = true;
					$this->form->addCommandButton('editResponsibleUsers',$this->lng->txt('cal_change_responsible_users'));
				}
				$this->form->addCommandButton('update',$this->lng->txt('save'));
				$this->form->addCommandButton('askDelete',$this->lng->txt('delete'));
				$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				break;
		}
		// title
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		$title->setValue($this->app->getTitle());
		$title->setRequired(true);
		$title->setMaxLength(128);
		$title->setSize(32);
		$this->form->addItem($title);
		
		// calendar selection
		$calendar = new ilSelectInputGUI($this->lng->txt('cal_category_selection'),'calendar');
		if($_POST['category'])
		{
			$calendar->setValue((int) $_POST['calendar']);
		}
		elseif($a_mode == 'edit')
		{
			$ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
			$cat = $ass->getFirstAssignment();
			$calendar->setValue($cat);
		}
		elseif(isset($_GET['ref_id']))
		{
			include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
			$obj_cal = ilObject::_lookupObjId($_GET['ref_id']);
			$calendar->setValue(ilCalendarCategories::_lookupCategoryIdByObjId($obj_cal));
		}
		$calendar->setRequired(true);
		$cats = ilCalendarCategories::_getInstance($ilUser->getId());
		$calendar->setOptions($cats->prepareCategoriesOfUserForSelection());
		$this->form->addItem($calendar);
		
		if (!$a_as_milestone)
		{
			$tpl->addJavaScript('./Services/Calendar/js/toggle_appointment_time.js');		
			$fullday = new ilCheckboxInputGUI($this->lng->txt('cal_fullday'),'fullday');
			$fullday->setChecked($this->app->isFullday() ? true : false);
			$fullday->setOptionTitle($this->lng->txt('cal_fullday_title'));
			$fullday->setAdditionalAttributes('onchange="ilToggleAppointmentTime(this);"');
			$this->form->addItem($fullday);
	
			$start = new ilDateTimeInputGUI($this->lng->txt('cal_start'),'start');
			$start->setDate($this->app->getStart());
			$start->setShowTime(true);
			$start->setMinuteStepSize(5);
			$this->form->addItem($start);
			#$fullday->addSubItem($start);
			
			$end = new ilDateTimeInputGUI($this->lng->txt('cal_end'),'end');
			$end->setDate($this->app->getEnd());
			$end->setShowTime(true);
			$end->setMinuteStepSize(5);
			#$fullday->addSubItem($end);
			$this->form->addItem($end);
		
			// recurrence
			include_once('./Services/Calendar/classes/Form/class.ilRecurrenceInputGUI.php');
			$rec = new ilRecurrenceInputGUI($this->lng->txt('cal_recurrences'),'frequence');
			$rec->setRecurrence($this->rec);
			$this->form->addItem($rec);

			// location
			$where = new ilTextInputGUI($this->lng->txt('cal_where'),'location');
			$where->setValue($this->app->getLocation());
			$where->setMaxLength(128);
			$where->setSize(32);
			$this->form->addItem($where);
		}
		else
		{
			$deadline = new ilDateTimeInputGUI($this->lng->txt('cal_deadline'),'start');
			$deadline->setDate($this->app->getStart());
			$deadline->setShowTime(false);
			$deadline->setMinuteStepSize(5);
			$this->form->addItem($deadline);
			
			// completion
			$completion_vals = array();
			for($i = 0; $i <= 100; $i+=5)
			{
				$completion_vals[$i] = $i." %";
			}
			$compl = new ilSelectInputGUI($this->lng->txt('cal_task_completion'),
				'completion');
			$compl->setOptions($completion_vals);
			$compl->setValue($this->app->getCompletion());
			$this->form->addItem($compl);
		}
		
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'description');
		$desc->setValue($this->app->getDescription());
		$desc->setCols(3);
		$this->form->addItem($desc);

		if ($a_as_milestone && $a_mode == "edit" && $resp_info)
		{
			// users responsible
			$users = $this->app->readResponsibleUsers();
			$resp = new ilNonEditableValueGUI($this->lng->txt('cal_responsible'),
				$users);
			$delim = "";
			foreach($users as $r)
			{
				$value.= $delim.$r["lastname"].", ".$r["firstname"]." [".$r["login"]."]";
				$delim = "<br />";
			}
			if (count($users) > 0)
			{
				$resp->setValue($value);
			}
			else
			{
				$resp->setValue("-");
			}

			$this->form->addItem($resp);
		}
	}
	
	
	/**
	 * add new appointment
	 *
	 * @access protected
	 * @return
	 */
	protected function add()
	{
		global $tpl;
		
		$this->initForm('create');
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * add milestone
	 *
	 * @access protected
	 * @return
	 */
	protected function addMilestone()
	{
		global $tpl;
		
		$this->initForm('create', true);
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * save milestone
	 *
	 * @access protected
	 */
	protected function saveMilestone()
	{
		$this->save(true);
	}

	/**
	 * save appointment
	 *
	 * @access protected
	 */
	protected function save($a_as_milestone = false)
	{
		global $ilErr;
		
		$this->load($a_as_milestone);
		
		if($this->app->validate())
		{
			if(!(int) $_POST['calendar'])
			{
				$cat_id = $this->createDefaultCalendar();
			}
			else
			{
				$cat_id = (int) $_POST['calendar'];
			}
			
			$this->app->save();
			$this->rec->setEntryId($this->app->getEntryId());
			$this->saveRecurrenceSettings();
			
			include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
			$ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
			$ass->addAssignment($cat_id);
			
			include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
			$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
			$type = ilObject::_lookupType($cat_info['obj_id']);
			
			if ($a_as_milestone && $cat_info['type'] == ilCalendarCategory::TYPE_OBJ
				&& ($type == "grp" || $type == "crs"))
			{
				ilUtil::sendInfo($this->lng->txt('cal_created_milestone_resp_q'));
				return $this->showResponsibleUsersList($cat_info['obj_id']);
			}
			else
			{
				ilUtil::sendSuccess($this->lng->txt('cal_created_milestone'));
				$this->ctrl->returnToParent($this);
			}
		}
		else
		{
			ilUtil::sendFailure($ilErr->getMessage());
		}
		if ($a_as_milestone)
		{
			$this->addMilestone();
		}
		else
		{
			$this->add();
		}

	}
	
	/**
	* Edit responsible users
	*/
	function editResponsibleUsers()
	{
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		$cat_id = ilCalendarCategoryAssignments::_lookupCategory($this->app->getEntryId());
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);

		$this->showResponsibleUsersList($cat_info['obj_id']);
	}
	
	/**
	* Show responsible uses of a milestone (default set is participants
	* of group)
	*/
	function showResponsibleUsersList($a_grp_id)
	{
		global $tpl;

		include_once("./Services/Calendar/classes/class.ilMilestoneResponsiblesTableGUI.php");
		$table_gui = new ilMilestoneResponsiblesTableGUI($this, "", $a_grp_id,
			$this->app->getEntryId());
		$tpl->setContent($table_gui->getHTML());
	}
	
	/**
	* Save milestone responsibilites
	*/
	function saveMilestoneResponsibleUsers()
	{
		global $ilCtrl;

		$this->app->writeResponsibleUsers($_POST["user_id"]);
		$ilCtrl->returnToParent($this);
	}
	
	/**
	 * edit appointment
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function edit()
	{
		global $tpl,$ilUser,$ilErr;
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		
		$cat_id = ilCalendarCategoryAssignments::_lookupCategory($this->app->getEntryId());
		$cats = ilCalendarCategories::_getInstance($ilUser->getId());
		
		if(!$cats->isVisible($cat_id))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->WARNING);
			return false;
		}
		if(!$cats->isEditable($cat_id) or $this->app->isAutoGenerated())
		{
			$this->showInfoScreen();
			return true;
		}
		
		$this->initForm('edit', $this->app->isMilestone());
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * show info screen
	 *
	 * @access protected
	 * @return
	 */
	protected function showInfoScreen()
	{
		global $tpl,$ilUser;
		
		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($this->ctrl->getFormAction($this));

		if ($this->app->isMilestone())
		{
			$info->addSection($this->lng->txt('cal_ms_details'));
		}
		else
		{
			$info->addSection($this->lng->txt('cal_details'));
		}

		// Appointment
		$info->addProperty($this->lng->txt('appointment'),
			ilDatePresentation::formatPeriod(
				$this->app->getStart(),
				$this->app->getEnd()));
		$info->addProperty($this->lng->txt('title'),$this->app->getPresentationTitle());

		// Description
		if(strlen($desc = $this->app->getDescription()))
		{
			$info->addProperty($this->lng->txt('description'),$desc);
		}
		
		// Location
		if(strlen($loc = $this->app->getLocation()))
		{
			$info->addProperty($this->lng->txt('cal_where'),$loc);
		}

		// completion
		if ($this->app->isMilestone() && $this->app->getCompletion() > 0)
		{
			$info->addProperty($this->lng->txt('cal_task_completion'),
				$this->app->getCompletion()." %");
		}

		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		$cat_id = ilCalendarCategoryAssignments::_lookupCategory($this->app->getEntryId());
		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
		$type = ilObject::_lookupType($cat_info['obj_id']);
		if ($this->app->isMilestone() && $cat_info['type'] == ilCalendarCategory::TYPE_OBJ
			&& ($type == "grp" || $type == "crs"))
		{
			// users responsible
			$users = $this->app->readResponsibleUsers();
			$delim = "";
			foreach($users as $r)
			{
				$value.= $delim.$r["lastname"].", ".$r["firstname"]." [".$r["login"]."]";
				$delim = "<br />";
			}
			if (count($users) > 0)
			{
				$info->addProperty($this->lng->txt('cal_responsible'),
					$value);
			}
		}

		$category = new ilCalendarCategory($cat_id);		
		
		if($category->getType() == ilCalendarCategory::TYPE_OBJ)
		{
			$info->addSection($this->lng->txt('additional_info'));
			
			$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
			$refs = ilObject::_getAllReferences($cat_info['obj_id']);
			
			include_once('classes/class.ilLink.php');
			$href = ilLink::_getStaticLink(current($refs),ilObject::_lookupType($cat_info['obj_id']),true);
			$info->addProperty($this->lng->txt('perma_link'),'<a class="small" href="'.$href.'" target="_top">'.$href.'</a>');
		}
			
		$tpl->setContent($info->getHTML());
	}
	
	/**
	 * update
	 *
	 * @access protected
	 * @return
	 */
	protected function update()
	{
		global $ilErr;

		$this->load($this->app->isMilestone());
		
		if($this->app->validate())
		{
			if(!(int) $_POST['calendar'])
			{
				$cat_id = $this->createDefaultCalendar();
			}
			else
			{
				$cat_id = (int) $_POST['calendar'];
			}
			
			$this->app->update();
			$this->saveRecurrenceSettings();
			
			include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
			$ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
			$ass->deleteAssignments();
			$ass->addAssignment($cat_id);
			
			ilUtil::sendSuccess($this->lng->txt('settings_saved'));
			$this->ctrl->returnToParent($this);
		}
		else
		{
			ilUtil::sendFailure($ilErr->getMessage());
		}
		
		$this->edit();
		
	}
	
	/**
	 * ask delete
	 *
	 * @access protected
	 * @return
	 */
	protected function askDelete()
	{
		global $tpl;
		
		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		
		$this->ctrl->saveParameter($this,array('seed','app_id'));
	
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($this->lng->txt('cal_delete_app_sure'));
		$confirm->setCancel($this->lng->txt('cancel'),'edit');
		$confirm->setConfirm($this->lng->txt('delete'),'delete');
		$confirm->addItem('appointments[]',$this->app->getEntryId(),$this->app->getTitle());
		$tpl->setContent($confirm->getHTML());
		
	}
	
	/**
	 * delete
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function delete()
	{
		foreach($_POST['appointments'] as $app_id)
		{
			$app = new ilCalendarEntry($app_id);
			$app->delete();
			
			include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
			ilCalendarCategoryAssignments::_deleteByAppointmentId($app_id);
		}
		ilUtil::sendSuccess($this->lng->txt('cal_deleted_app'),true);
		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * init timezone
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initTimeZone()
	{
		global $ilUser;
		
		$this->timezone = $ilUser->getTimeZone();
	}
	
	/**
	 * init seed
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initSeed(ilDate $seed)
	{
		if(!isset($_GET['hour']))
		{
			$this->seed = clone $seed;
			$this->default_fulltime = true;
		}
		else
		{
			if((int) $_GET['hour'] < 10)
			{
				$time = '0'.(int) $_GET['hour'].':00:00';
			}
			else
			{
				$time = (int) $_GET['hour'].':00:00';
			}
			$this->seed = new ilDateTime($seed->get(IL_CAL_DATE).' '.$time,IL_CAL_DATETIME,$this->timezone);
			$this->default_fulltime = false;
		}
		
	}
	
	/**
	 * init appointment
	 *
	 * @access protected
	 * @param int appointment id
	 * @return
	 */
	protected function initAppointment($a_app_id = 0)
	{
		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		include_once('./Services/Calendar/classes/class.ilCalendarRecurrences.php');
		$this->app = new ilCalendarEntry($a_app_id);
		
		if(!$a_app_id)
		{
			$start = clone $this->seed;
			$this->app->setStart($start);

			$seed_end = clone $this->seed;
			if($this->default_fulltime)
			{
				#$seed_end->increment(IL_CAL_DAY,1);
			}
			else
			{
				$seed_end->increment(IL_CAL_HOUR,2);
			}
			$this->app->setEnd($seed_end);
			$this->app->setFullday($this->default_fulltime);
			
			$this->rec = new ilCalendarRecurrence();
		}
		else
		{
			$this->rec = ilCalendarRecurrences::_getFirstRecurrence($this->app->getEntryId());
		}
		
	}
	
	/**
	 * load post
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function load($a_as_milestone = false)
	{
		if ($a_as_milestone)
		{
			$this->app->setMilestone(true);
			$this->app->setCompletion(ilUtil::stripSlashes($_POST['completion']));
		}
		
		$this->app->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->app->setLocation(ilUtil::stripSlashes($_POST['location']));
		$this->app->setDescription(ilUtil::stripSlashes($_POST['description']));
		$this->app->setTitle(ilUtil::stripSlashes($_POST['title']));
		if ($a_as_milestone)	// milestones are always fullday events
		{
			$this->app->setFullday(true);
		}
		else
		{
			$this->app->setFullday(isset($_POST['fullday']) ? true : false);
		}

		if($this->app->isFullday())
		{
			$start = new ilDate($_POST['start']['date']['y'].'-'.$_POST['start']['date']['m'].'-'.$_POST['start']['date']['d'],
				IL_CAL_DATE);
			$this->app->setStart($start);
				
			$end = new ilDate($_POST['end']['date']['y'].'-'.$_POST['end']['date']['m'].'-'.$_POST['end']['date']['d'],
				IL_CAL_DATE);

			if ($a_as_milestone)
			{
				// for milestones is end date = start date
				$this->app->setEnd($start);
			}
			else
			{
				$this->app->setEnd($end);
			}
		}
		else
		{
			$start_dt['year'] = (int) $_POST['start']['date']['y'];
			$start_dt['mon'] = (int) $_POST['start']['date']['m'];
			$start_dt['mday'] = (int) $_POST['start']['date']['d'];
			$start_dt['hours'] = (int) $_POST['start']['time']['h'];
			$start_dt['minutes'] = (int) $_POST['start']['time']['m'];
			$start = new ilDateTime($start_dt,IL_CAL_FKT_GETDATE,$this->timezone);
			$this->app->setStart($start);

			$end_dt['year'] = (int) $_POST['end']['date']['y'];
			$end_dt['mon'] = (int) $_POST['end']['date']['m'];
			$end_dt['mday'] = (int) $_POST['end']['date']['d'];
			$end_dt['hours'] = (int) $_POST['end']['time']['h'];
			$end_dt['minutes'] = (int) $_POST['end']['time']['m'];
			$end = new ilDateTime($end_dt,IL_CAL_FKT_GETDATE,$this->timezone);
			$this->app->setEnd($end);
		}
		$this->loadRecurrenceSettings($a_as_milestone = false);
	}
	
	/**
	 * load recurrence settings
	 *
	 * @access protected
	 * @return
	 */
	protected function loadRecurrenceSettings($a_as_milestone = false)
	{
		$this->rec->reset();
		
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
				// nothing to do
				break;
				
			case 2:
				$this->rec->setFrequenceUntilCount((int) $_POST['count']);
				break;
		}
		
	}
	
	/**
	 * save recurrence settings
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function saveRecurrenceSettings()
	{
		switch($_POST['frequence'])
		{
			case 'NONE':
				// No recurrence => delete if there is an recurrence rule
				if($this->rec->getRecurrenceId())
				{
					$this->rec->delete();
				}
				break;
			
			default:
				if($this->rec->getRecurrenceId())
				{
					$this->rec->update();
				}
				else
				{
					$this->rec->save();
				}
				break;
		}
	}
	
	/**
	 * Create a default calendar
	 *
	 * @access protected
	 * @return
	 */
	protected function createDefaultCalendar()
	{
		global $ilUser,$lng;

		$cat = new ilCalendarCategory();
		$cat->setColor(ilCalendarCategory::DEFAULT_COLOR);
		$cat->setType(ilCalendarCategory::TYPE_USR);
		$cat->setTitle($this->lng->txt('cal_default_calendar'));
		$cat->setObjId($ilUser->getId());
		return $cat->add();
	}

}
?>