<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';

/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationConsultationHoursGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationConsultationHoursGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{

	public function collectPropertiesAndActions()
	{
		include_once('./Services/Link/classes/class.ilLink.php');

		$a_app = $this->appointment;

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
<<<<<<< HEAD
		$cat_info = $this->getCatInfo($cat_id);
		$context_id = $a_app['event']->getContextId();

		// TODO: Discuss this order info, using the common section.
		$this->addCommonSection($a_app, $cat_info['obj_id']);

		$this->addInfoProperty($this->lng->txt("cal_ch_manager"), ilConsultationHourAppointments::getManager(true));

		//objects
		include_once 'Services/Booking/classes/class.ilBookingEntry.php';
		$booking = new ilBookingEntry($context_id);

		$buttons = array();
		foreach($booking->getTargetObjIds() as $obj_id)
		{
			//$this->addObjectLinks($obj_id);

			$title = ilObject::_lookupTitle($obj_id);
			$refs = $this->getReadableRefIds($obj_id);
			reset($refs);

			foreach ($refs as $ref_id)
			{
				$link_title = $title;
				if (count($refs) > 1)
				{
					$par_ref = $this->tree->getParentId($ref_id);
					$link_title.= " (".ilObject::_lookupTitle(ilObject::_lookupObjId($par_ref)).")";
				}
				$buttons[] = $this->ui->renderer()->render(
					$this->ui->factory()->button()->shy($link_title, ilLink::_getStaticLink($ref_id)));
			}
		}
		if(count($buttons) > 0)
		{
			$this->addInfoProperty("Objects",implode("<br>",$buttons));
		}
	}
}