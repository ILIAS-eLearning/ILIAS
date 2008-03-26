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
	protected $app = null;
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
		$this->seed = $seed;
		$this->initTimeZone();
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
	protected function initForm($a_mode)
	{
		global $ilUser;
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		
		switch($a_mode)
		{
			case 'create':
				$this->form->setTitle($this->lng->txt('cal_new_app'));
				$this->ctrl->saveParameter($this,array('seed'));
				$this->form->setFormAction($this->ctrl->getFormAction($this));
				$this->form->addCommandButton('save',$this->lng->txt('cal_add_appointment'));
				$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				break;

			case 'edit':
				$this->form->setTitle($this->lng->txt('cal_edit_appointment'));
				$this->ctrl->saveParameter($this,array('seed','app_id'));
				$this->form->setFormAction($this->ctrl->getFormAction($this));
				$this->form->addCommandButton('update',$this->lng->txt('save'));
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
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		$calendar = new ilSelectInputGUI($this->lng->txt('cal_category_selection'),'calendar');
		if($_POST['category'])
		{
			$calendar->setValue((int) $_POST['calendar']);
		}
		elseif($this->app->getEntryId())
		{
			$ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
			$cat = $ass->getFirstAssignment();
			$calendar->setValue($cat);		
		}
		$calendar->setRequired(true);
		$calendar->setOptions(ilCalendarCategories::_prepareCategoriesOfUserForSelection($ilUser->getId()));
		$this->form->addItem($calendar);
		
		$start = new ilDateTimeInputGUI($this->lng->txt('cal_start'),'start');
		$start->setDate($this->app->getStart()->get(IL_CAL_DATE,'',$this->timezone));
		$start->setShowTime(true);
		$start->setMinuteStepSize(5);
		$start->setTime($this->app->getStart()->get(IL_CAL_FKT_DATE,'H:i:s',$this->timezone));
		$this->form->addItem($start);
		
		$end = new ilDateTimeInputGUI($this->lng->txt('cal_end'),'end');
		$end->setDate($this->app->getEnd()->get(IL_CAL_DATE,'',$this->timezone));
		$end->setTime($this->app->getEnd()->get(IL_CAL_FKT_DATE,'H:i:s',$this->timezone));
		$end->setShowTime(true);
		$end->setMinuteStepSize(5);
		$this->form->addItem($end);
		
		$fullday = new ilCheckboxInputGUI($this->lng->txt('cal_fullday'),'fullday');
		$fullday->setChecked($this->app->isFullday() ? true : false);
		$fullday->setOptionTitle($this->lng->txt('cal_fullday_title'));
		$this->form->addItem($fullday);
		
		$where = new ilTextInputGUI($this->lng->txt('cal_where'),'location');
		$where->setValue($this->app->getLocation());
		$where->setMaxLength(128);
		$where->setSize(32);
		$this->form->addItem($where);
		
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'description');
		$desc->setValue($this->app->getDescription());
		$desc->setCols(3);
		$this->form->addItem($desc);
		
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
	 * save appointment
	 *
	 * @access protected
	 */
	protected function save()
	{
		$this->load();
		
		if($this->app->validate())
		{
			$this->app->save();
			
			include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
			$ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
			$ass->addAssignment((int) $_POST['calendar']);
			
			ilUtil::sendInfo($this->lng->txt('cal_created_appointment'));
			$this->ctrl->returnToParent($this);
		}
		$this->add();
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
		global $tpl;
		
		$this->initForm('edit');
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * update
	 *
	 * @access protected
	 * @return
	 */
	protected function update()
	{
		$this->load();
		
		if($this->app->validate())
		{
			$this->app->update();
			
			include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
			$ass = new ilCalendarCategoryAssignments($this->app->getEntryId());
			$ass->deleteAssignments();
			$ass->addAssignment((int) $_POST['calendar']);
			
			ilUtil::sendInfo($this->lng->txt('settings_saved'));
			$this->ctrl->returnToParent($this);
		}
		$this->edit();
		
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
		
		$this->timezone = $ilUser->getUserTimeZone();
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
		$this->app = new ilCalendarEntry($a_app_id);
		
		if(!$a_app_id)
		{
			$start = new ilDateTime(
				$this->seed->get(IL_CAL_DATETIME),
				IL_CAL_DATETIME,
				$this->timezone);
			$this->app->setStart($start);

			$seed_end = clone $this->seed;
			$seed_end->increment(IL_CAL_DAY,1);
			$end = new ilDateTime(
				$seed_end->get(IL_CAL_DATETIME),
				IL_CAL_DATETIME,
				$this->timezone);
			$this->app->setEnd($end);

			$this->app->setFullday(true);
		}
		
	}
	
	/**
	 * load post
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function load()
	{$this->app->update();
		$this->app->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->app->setLocation(ilUtil::stripSlashes($_POST['location']));
		$this->app->setDescription(ilUtil::stripSlashes($_POST['description']));
		$this->app->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->app->setFullday(isset($_POST['fullday']) ? true : false);
		
		if($this->app->isFullday())
		{
			$start = new ilDate($_POST['start']['date']['y'].'-'.$_POST['start']['date']['m'].'-'.$_POST['start']['date']['d'],
				IL_CAL_DATE);
			$this->app->setStart($start);
				
			$end = new ilDate($_POST['end']['date']['y'].'-'.$_POST['end']['date']['m'].'-'.$_POST['end']['date']['d'],
				IL_CAL_DATE);
			$this->app->setEnd($end);
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
	}

}
?>