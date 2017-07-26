<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';

/**
 * ilAppointmentPresentationMilestoneGUI class presents milestones information.
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 * @ilCtrl_IsCalledBy ilAppointmentPresentationMilestoneGUI: ilCalendarAppointmentPresentationGUI
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationMilestoneGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
	public function collectPropertiesAndActions()
	{
		$appointment = $this->appointment;
		$completion = $appointment['event']->getCompletion();
		$cat_info = $this->getCatInfo();

		$this->addCommonSection($appointment, 0, $cat_info);

		$this->addInfoSection($this->lng->txt("cal_app_info"));

		$this->addInfoProperty($this->lng->txt("cal_task_completion"),$completion." %");
		$this->addListItemProperty($this->lng->txt("cal_task_completion"),$completion." %");
	}
}