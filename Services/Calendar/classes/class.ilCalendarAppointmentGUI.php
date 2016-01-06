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
	protected $initialDate = null;
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
	public function __construct(ilDate $seed,ilDate $initialDate, $a_appointment_id = 0)
	{
		global $ilCtrl,$lng;
		
		$this->lng = $lng;
		$lng->loadLanguageModule('dateplaner');
		$this->ctrl = $ilCtrl;

		$this->initTimeZone();
		$this->initSeed($seed);
		$this->initInitialDate($initialDate);
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
		global $ilUser, $ilSetting,$tpl, $ilTabs;
		
		
		// Clear tabs and set back target
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget(
			$this->lng->txt('cal_back_to_cal'),
			$this->ctrl->getLinkTarget($this,'cancel')
		);

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
	 * Get current appointment
	 * @return ilCalendarEntry
	 */
	public function getAppointment()
	{
		return $this->app;
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
	protected function initForm($a_mode, $a_as_milestone = false, $a_edit_single_app = false)
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
				$this->ctrl->saveParameter($this,array('seed','idate'));
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
				$this->ctrl->saveParameter($this,array('seed','app_id','idate'));
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
				// $this->form->addCommandButton('askDelete',$this->lng->txt('delete'));
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
			$selected_calendar = (int) $_POST['calendar'];
		}
		else if($_GET['category_id'])
		{
			$calendar->setValue((int) $_GET['category_id']);
			$selected_calendar = (int) $_GET['category_id'];
		}
		elseif($a_mode == 'edit')
		{
			$ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
			$cat = $ass->getFirstAssignment();
			$calendar->setValue($cat);
			$selected_calendar = $cat;
		}
		elseif(isset($_GET['ref_id']))
		{
			include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
			$obj_cal = ilObject::_lookupObjId($_GET['ref_id']);
			$calendar->setValue(ilCalendarCategories::_lookupCategoryIdByObjId($obj_cal));
			$selected_calendar = ilCalendarCategories::_lookupCategoryIdByObjId($obj_cal);
		}
		$calendar->setRequired(true);
		$cats = ilCalendarCategories::_getInstance($ilUser->getId());
		$calendar->setOptions($cats->prepareCategoriesOfUserForSelection());
		
		include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
		if(ilCalendarSettings::_getInstance()->isNotificationEnabled())
		{
			$notification_cals = $cats->getNotificationCalendars();
			$notification_cals = count($notification_cals) ? implode(',',$notification_cals) : ''; 
			$calendar->addCustomAttribute("onchange=\"ilToggleNotification(new Array(".$notification_cals."));\"");
		}		
		$this->form->addItem($calendar);
		
		if (!$a_as_milestone)
		{
			include_once './Services/Form/classes/class.ilDateDurationInputGUI.php';
			$tpl->addJavaScript('./Services/Form/js/date_duration.js');
			$dur = new ilDateDurationInputGUI($this->lng->txt('cal_fullday'),'event');
			$dur->setStartText($this->lng->txt('cal_start'));
			$dur->setEndText($this->lng->txt('cal_end'));
			$dur->enableToggleFullTime(
				$this->lng->txt('cal_fullday_title'),
				$this->app->isFullday() ? true : false 
			);
			$dur->setShowTime(true);
			$dur->setStart($this->app->getStart());
			$dur->setEnd($this->app->getEnd());
			$this->form->addItem($dur);

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
			$deadline = new ilDateTimeInputGUI($this->lng->txt('cal_deadline'),'event[start]');
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
		$desc->setRows(5);
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
		

		if(ilCalendarSettings::_getInstance()->isUserNotificationEnabled())
		{
			$notu = new ilTextWizardInputGUI($this->lng->txt('cal_user_notification'), 'notu');
			$notu->setInfo($this->lng->txt('cal_user_notification_info'));
			$notu->setSize(20);
			$notu->setMaxLength(64);

			$values = array();
			foreach($this->notification->getRecipients() as $rcp)
			{
				switch ($rcp['type'])
				{
					case ilCalendarUserNotification::TYPE_USER:
						$values[] = ilObjUser::_lookupLogin($rcp['usr_id']);
						break;

					case ilCalendarUserNotification::TYPE_EMAIL:
						$values[] = $rcp['email'];
						break;
				}
			}
			if(count($values))
				$notu->setValues($values);
			else
				$notu->setValues(array(''));
			$this->form->addItem($notu);
		}

		// Notifications
		include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
		if(ilCalendarSettings::_getInstance()->isNotificationEnabled()  and count($cats->getNotificationCalendars()))
		{
			$selected_cal = new ilCalendarCategory($selected_calendar);
			$disabled = true;
			if($selected_cal->getType() == ilCalendarCategory::TYPE_OBJ)
			{
				if(ilObject::_lookupType($selected_cal->getObjId()) == 'crs' or ilObject::_lookupType($selected_cal->getObjId()) == 'grp')
				{
					$disabled = false;
				}
			}

			$tpl->addJavaScript('./Services/Calendar/js/toggle_notification.js');
			$not = new ilCheckboxInputGUI($this->lng->txt('cal_cg_notification'),'not');
			$not->setInfo($this->lng->txt('cal_notification_info'));
			$not->setValue(1);
			$not->setChecked($this->app->isNotificationEnabled());
			$not->setDisabled($disabled);
			$this->form->addItem($not);
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
		global $tpl, $ilHelp;

		$ilHelp->setScreenIdComponent("cal");
		$ilHelp->setScreenId("app");
		$ilHelp->setSubScreenId("create");
		
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
		global $tpl, $ilHelp;

		$ilHelp->setScreenIdComponent("cal");
		$ilHelp->setScreenId("app");
		$ilHelp->setSubScreenId("create_milestone");

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
		
		if($this->app->validate() and $this->notification->validate())
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
			$this->notification->setEntryId($this->app->getEntryId());
			$this->notification->save();
			$this->rec->setEntryId($this->app->getEntryId());
			$this->saveRecurrenceSettings();
			
			include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
			$ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
			$ass->addAssignment($cat_id);
			
			// Send notifications
			include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
			if(ilCalendarSettings::_getInstance()->isNotificationEnabled() and (bool) $_POST['not'])
			{
				$this->distributeNotifications($cat_id,$this->app->getEntryId(),true);
			}
			if(ilCalendarSettings::_getInstance()->isUserNotificationEnabled())
			{
				$this->distributeUserNotifications();
			}

			include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
			$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
			$type = ilObject::_lookupType($cat_info['obj_id']);
			
			if ($a_as_milestone && $cat_info['type'] == ilCalendarCategory::TYPE_OBJ
				&& ($type == "grp" || $type == "crs"))
			{
				ilUtil::sendSuccess($this->lng->txt('cal_created_milestone_resp_q'),true);
				return $this->showResponsibleUsersList($cat_info['obj_id']);
			}
			elseif($a_as_milestone)
			{
				ilUtil::sendSuccess($this->lng->txt('cal_created_milestone'),true);
				$this->ctrl->returnToParent($this);
			}
			else
			{
				ilUtil::sendSuccess($this->lng->txt('cal_created_appointment'),true);
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
	 * Send mail to selected users
	 * @global ilObjUser $ilUser 
	 */
	protected function distributeUserNotifications()
	{
		global $ilUser;

		include_once './Services/Calendar/classes/class.ilCalendarMailNotification.php';
		$notification =  new ilCalendarMailNotification();
		$notification->setAppointmentId($this->app->getEntryId());
		
		foreach($this->notification->getRecipients() as $rcp)
		{
			switch($rcp['type'])
			{
				case ilCalendarUserNotification::TYPE_USER:
					$notification->setSender(ANONYMOUS_USER_ID);
					$notification->setRecipients(array($rcp['usr_id']));
					$notification->setType(ilCalendarMailNotification::TYPE_USER);
					break;

				case ilCalendarUserNotification::TYPE_EMAIL:
					$notification->setSender(ANONYMOUS_USER_ID);
					$notification->setRecipients(array($rcp['email']));
					$notification->setType(ilCalendarMailNotification::TYPE_USER_ANONYMOUS);
					break;
			}
			$notification->send();
		}
	}


	/**
	 * Distribute mail notifications
	 * @return 
	 */
	protected function distributeNotifications($a_cat_id, $app_id, $a_new_appointment = true)
	{
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($a_cat_id);
		
		include_once './Services/Calendar/classes/class.ilCalendarMailNotification.php';
		$notification =  new ilCalendarMailNotification();
		$notification->setAppointmentId($app_id);
		
		switch($cat_info['type'])
		{
			case ilCalendarCategory::TYPE_OBJ:
				
					switch($cat_info['obj_type'])
					{
						case 'crs':
							$ref_ids = ilObject::_getAllReferences($cat_info['obj_id']);
							$ref_id = current($ref_ids);
							$notification->setRefId($ref_id);
							$notification->setType(
								$a_new_appointment ?
								ilCalendarMailNotification::TYPE_CRS_NEW_NOTIFICATION :
								ilCalendarMailNotification::TYPE_CRS_NOTIFICATION);
							break;
						
						case 'grp':
							$ref_ids = ilObject::_getAllReferences($cat_info['obj_id']);
							$ref_id = current($ref_ids);
							$notification->setRefId($ref_id);
							$notification->setType(
								$a_new_appointment ?
								ilCalendarMailNotification::TYPE_GRP_NEW_NOTIFICATION :
								ilCalendarMailNotification::TYPE_GRP_NOTIFICATION);
							break;
					}
					break;
		}

		$notification->send();
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
	 * Check edit single apppointment / edit all appointments for recurring appointments.
	 * @todo works with milestones???
	 */
	protected function askEdit()
	{
		// check for recurring entries
		include_once './Services/Calendar/classes/class.ilCalendarRecurrences.php';
		$rec = ilCalendarRecurrences::_getRecurrences($this->getAppointment()->getEntryId());
		if(!$rec)
		{
			return $this->edit(TRUE);
		}
		// Show edit single/all appointments
		$this->ctrl->saveParameter($this,array('seed','app_id','dt','idate'));

		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		#$confirm->setHeaderText($this->lng->txt('cal_edit_app_sure'));
		$confirm->setCancel($this->lng->txt('cancel'),'cancel');
		$confirm->addItem('appointments[]',$this->app->getEntryId(),$this->app->getTitle());
		$confirm->addButton($this->lng->txt('cal_edit_single'),'editSingle');
	    $confirm->setConfirm($this->lng->txt('cal_edit_recurrences'),'edit');

		$GLOBALS['tpl']->setContent($confirm->getHTML());
	}
	
	/**
	 * Edit one single appointment
	^ */
	protected function editSingle()
	{
		$_REQUEST['rexl'] = 1;
		$GLOBALS['ilCtrl']->setParameter($this,'rexcl',1);
		$this->edit(true);
	}
	
	/**
	 * edit appointment
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function edit($a_edit_single_app = false)
	{
		global $tpl,$ilUser,$ilErr, $ilHelp;

		$ilHelp->setScreenIdComponent("cal");
		$ilHelp->setScreenId("app");
		if ($this->app->isMilestone())
		{
			$ilHelp->setSubScreenId("edit_milestone");
		}
		else
		{
			$ilHelp->setSubScreenId("edit");
		}

		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');		

		$GLOBALS['ilCtrl']->saveParameter($this,array('seed','app_id','dt','idate'));

		if($_REQUEST['rexl'])
		{
			$GLOBALS['ilCtrl']->setParameter($this,'rexl',1);

			// Calculate new appointment time
			$duration = $this->getAppointment()->getEnd()->get(IL_CAL_UNIX) - $this->getAppointment()->getStart()->get(IL_CAL_UNIX);
			include_once './Services/Calendar/classes/class.ilCalendarRecurrenceCalculator.php';
			$calc = new ilCalendarRecurrenceCalculator($this->getAppointment(), $this->rec);
			
			$current_date = new ilDateTime($_REQUEST['dt'],IL_CAL_UNIX);

			$yesterday = clone $current_date;
			$yesterday->increment(IL_CAL_DAY,-1);
			$tomorrow = clone $current_date;
			$tomorrow->increment(IL_CAL_DAY, 1);
			

			foreach($calc->calculateDateList($current_date, $tomorrow, 1) as $date_entry)
			{
				if(ilDateTime::_equals($current_date, $date_entry,IL_CAL_DAY))
				{
					$this->getAppointment()->setStart(new ilDateTime($date_entry->get(IL_CAL_UNIX),IL_CAL_UNIX));
					$this->getAppointment()->setEnd(new ilDateTime($date_entry->get(IL_CAL_UNIX) + $duration,IL_CAL_UNIX));
					break;
				}
			}
			
			// Finally reset recurrence
			$this->rec = new ilCalendarRecurrence();
		}
		
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
		
		$this->initForm('edit', $this->app->isMilestone(), $a_edit_single_app);
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
			
			include_once('./Services/Link/classes/class.ilLink.php');
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

		$single_editing = ($_REQUEST['rexl'] ? true : false);
		
		$this->load($this->app->isMilestone());
		
		if($this->app->validate() and $this->notification->validate())
		{
			if(!(int) $_POST['calendar'])
			{
				$cat_id = $this->createDefaultCalendar();
			}
			else
			{
				$cat_id = (int) $_POST['calendar'];
			}
			
			if($single_editing)
			{
				$this->getAppointment()->save();
				$this->deleteExclude(false);
				
				$this->rec = new ilCalendarRecurrence();
				$this->rec->setEntryId($this->getAppointment()->getEntryId());
			}
			else
			{
				$this->getAppointment()->update();
			}
			$this->notification->save();
			$this->saveRecurrenceSettings();
			
			include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
			$ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
			$GLOBALS['ilLog']->write($this->app->getEntryId());
			$ass->deleteAssignments();
			$ass->addAssignment($cat_id);
			
			// Send notifications
			include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
			if(ilCalendarSettings::_getInstance()->isNotificationEnabled() and (bool) $_POST['not'])
			{
				$this->distributeNotifications($cat_id,$this->app->getEntryId(),false);
			}
			if(ilCalendarSettings::_getInstance()->isUserNotificationEnabled())
			{
				$this->distributeUserNotifications();
			}
			
			ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
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
		
		$this->ctrl->saveParameter($this,array('seed','app_id','dt','idate'));

		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($this->lng->txt('cal_delete_app_sure'));
		$confirm->setCancel($this->lng->txt('cancel'),'cancel');
		$confirm->addItem('appointments[]',$this->app->getEntryId(),$this->app->getTitle());

		include_once('./Services/Calendar/classes/class.ilCalendarRecurrences.php');
		if(sizeof(ilCalendarRecurrences::_getRecurrences($_GET['app_id']))
			&& !$this->app->isMilestone())
		{
			$confirm->addButton($this->lng->txt('cal_delete_single'),'deleteexclude');
		    $confirm->setConfirm($this->lng->txt('cal_delete_recurrences'),'delete');
		}
		else
		{
			$confirm->setConfirm($this->lng->txt('delete'),'delete');
		}

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

			include_once './Services/Calendar/classes/class.ilCalendarUserNotification.php';
			ilCalendarUserNotification::deleteCalendarEntry($app_id);
		}
		ilUtil::sendSuccess($this->lng->txt('cal_deleted_app'),true);
		$this->ctrl->returnToParent($this);
	}

	/**
	 * delete single item of recurrence list
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function deleteExclude($a_return = true)
	{
		include_once('./Services/Calendar/classes/class.ilCalendarRecurrenceExclusion.php');
		$excl = new ilCalendarRecurrenceExclusion();
		$excl->setEntryId($_REQUEST['app_id']);
		$excl->setDate(new ilDate($_REQUEST['dt'], IL_CAL_UNIX));
		$excl->save();

		if($a_return)
		{
			ilUtil::sendSuccess($this->lng->txt('cal_deleted_app'),true);
			$this->ctrl->returnToParent($this);
		}
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
	 * init initial date
	 * @param ilDate $initialDate
	 */
	protected function initInitialDate(ilDate $initialDate)
	{
		if(!isset($_GET['hour']))
		{
			$this->initialDate = clone $initialDate;
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
			$this->initialDate = new ilDateTime($initialDate->get(IL_CAL_DATE).' '.$time,IL_CAL_DATETIME,$this->timezone);
			$this->default_fulltime = false;
		}
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
		$this->seed = clone $seed;
		$this->default_fulltime = true;
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

		include_once './Services/Calendar/classes/class.ilCalendarUserNotification.php';
		$this->notification = new ilCalendarUserNotification($this->app->getEntryId());
		
		if(!$a_app_id)
		{
			$start = clone $this->initialDate;
			$this->app->setStart($start);

			$seed_end = clone $this->initialDate;
			if($this->default_fulltime)
			{
				#$seed_end->increment(IL_CAL_DAY,1);
			}
			else
			{
				$seed_end->increment(IL_CAL_HOUR,1);
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
		$this->app->enableNotification((int) $_POST['not']);
		if ($a_as_milestone)	// milestones are always fullday events
		{
			$this->app->setFullday(true);
		}
		else
		{
			$this->app->setFullday(isset($_POST['event']['fulltime']) ? true : false);
		}

		if($this->app->isFullday())
		{
			$start = new ilDate($_POST['event']['start']['date']['y'].'-'.$_POST['event']['start']['date']['m'].'-'.$_POST['event']['start']['date']['d'],
				IL_CAL_DATE);
			$this->app->setStart($start);
				
			$end = new ilDate($_POST['event']['end']['date']['y'].'-'.$_POST['event']['end']['date']['m'].'-'.$_POST['event']['end']['date']['d'],
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
			$start_dt['year'] = (int) $_POST['event']['start']['date']['y'];
			$start_dt['mon'] = (int) $_POST['event']['start']['date']['m'];
			$start_dt['mday'] = (int) $_POST['event']['start']['date']['d'];
			$start_dt['hours'] = (int) $_POST['event']['start']['time']['h'];
			$start_dt['minutes'] = (int) $_POST['event']['start']['time']['m'];
			$start = new ilDateTime($start_dt,IL_CAL_FKT_GETDATE,$this->timezone);
			$this->app->setStart($start);

			$end_dt['year'] = (int) $_POST['event']['end']['date']['y'];
			$end_dt['mon'] = (int) $_POST['event']['end']['date']['m'];
			$end_dt['mday'] = (int) $_POST['event']['end']['date']['d'];
			$end_dt['hours'] = (int) $_POST['event']['end']['time']['h'];
			$end_dt['minutes'] = (int) $_POST['event']['end']['time']['m'];
			$end = new ilDateTime($end_dt,IL_CAL_FKT_GETDATE,$this->timezone);
			$this->app->setEnd($end);
		}
		$this->loadNotificationRecipients();
		$this->loadRecurrenceSettings($a_as_milestone = false);
	}

	protected function loadNotificationRecipients()
	{
		$this->notification->setRecipients(array());

		foreach((array) $_POST['notu'] as $rcp)
		{
			$rcp = trim(ilUtil::stripSlashes($rcp));
			$usr_id = ilObjUser::_loginExists($rcp);

			if(strlen($rcp) == 0)
			{
				continue;
			}

			if($usr_id)
			{
				$this->notification->addRecipient(
					ilCalendarUserNotification::TYPE_USER,
					$usr_id
				);
			}
			else
			{
				$this->notification->addRecipient(
					ilCalendarUserNotification::TYPE_EMAIL,
					0,
					$rcp
				);
			}
		}
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
			case '':
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
	
	/**
	 * Register to an appointment
	 * @return 
	 */
	protected function confirmRegister()
	{
		global $tpl;
		
		$entry = new ilCalendarEntry((int) $_GET['app_id']);
		$start = ilDatePresentation::formatDate(
			new ilDateTime($_GET['dstart'],IL_CAL_UNIX),
			new ilDateTime($_GET['dend'],IL_CAL_UNIX)
		);
			
		
		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI;
		
		$this->ctrl->setParameter($this,'dstart',(int) $_REQUEST['dstart']);
		$this->ctrl->setParameter($this,'dend',(int) $_REQUEST['dend']);
		
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->lng->txt('cal_confirm_reg_info'));
		$conf->setConfirm($this->lng->txt('cal_reg_register'), 'register');
		$conf->setCancel($this->lng->txt('cancel'), 'cancel');
		$conf->addItem('app_id', $entry->getEntryId(), $entry->getTitle().' ('.$start.')');
		
		$tpl->setContent($conf->getHTML());
	}
	
	/**
	 * Register
	 * @return 
	 */
	protected function register()
	{
		global $ilUser;
		
		include_once './Services/Calendar/classes/class.ilCalendarRegistration.php';
		$reg = new ilCalendarRegistration((int) $_POST['app_id']);
		$reg->register(
			$ilUser->getId(),
			new ilDateTime((int) $_REQUEST['dstart'],IL_CAL_UNIX),
			new ilDateTime((int) $_REQUEST['dend'],IL_CAL_UNIX)
		);

		ilUtil::sendSuccess($this->lng->txt('cal_reg_registered'),true);
		$this->ctrl->returnToParent($this);
	}

	/**
	 * Confirmation screen to unregister calendar
	 */
	public function confirmUnregister()
	{
		global $tpl;
		
		
		$entry = new ilCalendarEntry((int) $_GET['app_id']);
		$start = ilDatePresentation::formatDate(
			$dstart = new ilDateTime($_GET['dstart'],IL_CAL_UNIX),
			$dend = new ilDateTime($_GET['dend'],IL_CAL_UNIX)
		);
			
		
		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI;
		
		$this->ctrl->setParameter($this,'dstart',(int) $_REQUEST['dstart']);
		$this->ctrl->setParameter($this,'dend',(int) $_REQUEST['dend']);
		
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->lng->txt('cal_confirm_unreg_info'));
		$conf->setConfirm($this->lng->txt('cal_reg_unregister'), 'unregister');
		$conf->setCancel($this->lng->txt('cancel'), 'cancel');
		$conf->addItem('app_id', $entry->getEntryId(), $entry->getTitle().' ('.$start.')');
		
		$tpl->setContent($conf->getHTML());
	}

	/**
	 * Unregister calendar, was confirmed
	 * @return 
	 */
	protected function unregister()
	{
		global $ilUser;
		
		include_once './Services/Calendar/classes/class.ilCalendarRegistration.php';
		$reg = new ilCalendarRegistration((int) $_POST['app_id']);
		$reg->unregister(
			$ilUser->getId(),
			new ilDateTime((int) $_REQUEST['dstart'],IL_CAL_UNIX),
			new ilDateTime((int) $_REQUEST['dend'],IL_CAL_UNIX)
		);

		ilUtil::sendSuccess($this->lng->txt('cal_reg_unregistered'),true);
		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * Confirmation screen for booking of consultation appointment
	 */
	public function book()
    {
		global $ilUser, $tpl;
		
		$entry = (int)$_GET['app_id'];
		$user = (int)$_GET['bkid'];

		$this->ctrl->saveParameter($this,'app_id');
		
		include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
		include_once 'Services/Booking/classes/class.ilBookingEntry.php';
		$entry = new ilCalendarEntry($entry);

		$form = $this->initFormConfirmBooking();
		$form->getItemByPostVar('date')->setValue(ilDatePresentation::formatPeriod($entry->getStart(), $entry->getEnd()));
		$form->getItemByPostVar('title')->setValue($entry->getTitle()." (".ilObjUser::_lookupFullname($user).')');
		
		$tpl->setContent($form->getHTML());
		return true;
	}
	
	/**
	 * 
	 * @return ilPropertyFormGUI
	 */
	protected function initFormConfirmBooking()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton('bookconfirmed',$this->lng->txt('cal_confirm_booking'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
		$date = new ilNonEditableValueGUI($this->lng->txt('appointment'),'date');
		$form->addItem($date);
		
		$title = new ilNonEditableValueGUI($this->lng->txt('title'),'title');
		$form->addItem($title);
		
		$message = new ilTextAreaInputGUI($this->lng->txt('cal_ch_booking_message_tbl'), 'comment');
		$message->setRows(5);
		$form->addItem($message);
		
		return $form;
	}

	/**
	 * Book consultation appointment, was confirmed
	 */
	public function bookconfirmed()
	{
		global $ilUser;

		$entry = (int) $_REQUEST['app_id'];
		$user = (int) $_REQUEST['bkid'];
		
		$form = $this->initFormConfirmBooking();
		if($form->checkInput())
		{
			// check if appointment is bookable
			include_once './Services/Calendar/classes/class.ilCalendarEntry.php';
			$cal_entry = new ilCalendarEntry($entry);
			
			include_once './Services/Booking/classes/class.ilBookingEntry.php';
			$booking = new ilBookingEntry($cal_entry->getContextId());
			
			if(!$booking->isAppointmentBookableForUser($entry, $GLOBALS['ilUser']->getId()))
			{
				ilUtil::sendFailure($this->lng->txt('cal_booking_failed_info'), true);
				$this->ctrl->returnToParent($this);
			}
			
			include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourUtils.php';
			ilConsultationHourUtils::bookAppointment($ilUser->getId(), $entry);
			
			include_once './Services/Booking/classes/class.ilBookingEntry.php';
			ilBookingEntry::writeBookingMessage($entry, $ilUser->getId(), $form->getInput('comment'));
		}
		ilUtil::sendSuccess($this->lng->txt('cal_booking_confirmed'),true);
		$this->ctrl->returnToParent($this);
	}

	/**
	 * Confirmation screen to cancel consultation appointment or ressource booking
	 * depends on calendar category
	 */
	public function cancelBooking()
	{
		global $ilUser, $tpl;

		$entry = (int)$_GET['app_id'];
	
		include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
		$entry = new ilCalendarEntry($entry);

		$category = $this->calendarEntryToCategory($entry);
		if($category->getType() == ilCalendarCategory::TYPE_CH)
		{
			include_once 'Services/Booking/classes/class.ilBookingEntry.php';
			$booking = new ilBookingEntry($entry->getContextId());
			if(!$booking->hasBooked($entry->getEntryId()))
			{
				$this->ctrl->returnToParent($this);
				return false;
			}

			$entry_title = ' '.$entry->getTitle()." (".ilObjUser::_lookupFullname($booking->getObjId()).')';
		}
		else if($category->getType() == ilCalendarCategory::TYPE_BOOK)
		{
			$entry_title = ' '.$entry->getTitle();
		}
		else
		{
			$this->ctrl->returnToParent($this);
			return false;
		}

		$title = ilDatePresentation::formatPeriod($entry->getStart(), $entry->getEnd());
		
		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI;
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->lng->txt('cal_cancel_booking_info'));
		$conf->setConfirm($this->lng->txt('cal_cancel_booking'), 'cancelconfirmed');
		$conf->setCancel($this->lng->txt('cancel'), 'cancel');
		$conf->addItem('app_id', $entry->getEntryId(), $title.' - '.$entry_title);

		$tpl->setContent($conf->getHTML());
	}

	/**
	 * Cancel consultation appointment or ressource booking, was confirmed
	 * This will delete the calendar entry
	 */
	public function cancelConfirmed()
	{
		global $ilUser;

		$entry = (int)$_POST['app_id'];
		$user = (int)$_GET['bkid'];

		include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
		$entry = new ilCalendarEntry($entry);
		
		$category = $this->calendarEntryToCategory($entry);
		if($category->getType() == ilCalendarCategory::TYPE_CH)
		{			
			// find cloned calendar entry in user calendar
			include_once 'Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';
			$GLOBALS['ilLog']->dump($entry->getStart());
			$apps = ilConsultationHourAppointments::getAppointmentIds(
					$ilUser->getId(), 
					$entry->getContextId(), 
					$entry->getStart(),
					ilCalendarCategory::TYPE_CH,
					false);
			$GLOBALS['ilLog']->dump($apps);

			// Fix for wrong, old entries
			foreach((array) $apps as $own_app)
			{
				$ref_entry = new ilCalendarEntry($own_app);
				$ref_entry->delete();
			}
			
			include_once 'Services/Booking/classes/class.ilBookingEntry.php';
			$booking = new ilBookingEntry($entry->getContextId());
			$booking->cancelBooking($entry->getEntryId());

			// do NOT delete original entry
		}
		else if($category->getType() == ilCalendarCategory::TYPE_BOOK)
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
			$booking = new ilBookingReservation($entry->getContextId());
			$booking->setStatus(ilBookingReservation::STATUS_CANCELLED);
			$booking->update();
			
			$entry->delete();
		}

		ilUtil::sendSuccess($this->lng->txt('cal_cancel_booking_confirmed'),true);
		$this->ctrl->returnToParent($this);
	}

	/**
	 * Get category object of given calendar entry
	 * @param ilCalendarEntry $entry
	 * @return ilCalendarCategory
	 */
	protected function calendarEntryToCategory(ilCalendarEntry $entry)
	{
		include_once 'Services/Calendar/classes/class.ilCalendarCategoryAssignments.php';
		include_once 'Services/Calendar/classes/class.ilCalendarCategory.php';
		$assignment = new ilCalendarCategoryAssignments($entry->getEntryId());
		$assignment = $assignment->getFirstAssignment();
		return new ilCalendarCategory($assignment);
	}

	/**
	 * Do auto completion
	 * @return void
	 */
	protected function doUserAutoComplete()
	{
		if(!isset($_GET['autoCompleteField']))
		{
			$a_fields = array('login','firstname','lastname','email');
		}
		else
		{
			$a_fields = array((string) $_GET['autoCompleteField']);
		}

		$GLOBALS['ilLog']->write(print_r($a_fields,true));
		include_once './Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields($a_fields);
		$auto->enableFieldSearchableCheck(true);
		echo $auto->getList($_REQUEST['query']);
		exit();
	}

}
?>