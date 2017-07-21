<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';

/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationBookingPoolGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationBookingPoolGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{

	public function collectPropertiesAndActions()
	{
		$a_app = $this->appointment;

		include_once "./Modules/Course/classes/class.ilObjBookingPool.php";

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		$description_text = $cat_info['title'] . ", " . ilObject::_lookupDescription($cat_info['obj_id']);
		$this->addInfoSection($cat_info['title']);

		if ($a_app['event']->getDescription()) {
			$this->addInfoProperty($this->lng->txt("cal_description"), ilUtil::makeClickable(nl2br($a_app['event']->getDescription())));
		}
		$this->addInfoProperty($this->lng->txt(ilObject::_lookupType($cat_info['obj_id'])), $description_text);

		$this->addInfoSection($this->lng->txt("cal_".(ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));
	}
}