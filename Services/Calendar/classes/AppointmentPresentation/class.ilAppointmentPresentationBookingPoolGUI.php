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

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		// context id is reservation id (see ilObjBookingPoolGUI->processBooking)
		$res_id = $a_app['event']->getContextId();
		include_once("./Modules/BookingManager/classes/class.ilBookingReservation.php");
		include_once("./Modules/BookingManager/classes/class.ilBookingObject.php");
		$res = new ilBookingReservation($res_id);
		$b_obj = new ilBookingObject($res->getObjectId());
		$obj_id = $b_obj->getPoolId();


		$refs = $this->getReadableRefIds($obj_id);

		// add common section (title, description, object/calendar, location)
		$this->addCommonSection($a_app, $obj_id, $cat_info);

		//example download all files
		$this->addAction($this->lng->txt("cal_download_all_files"), "www.ilias.de");

		if (count($refs) > 0)
		{
			$this->addAction($this->lng->txt("cal_book_open"), ilLink::_getStaticLink(current($refs)));
		}

	}
	
}