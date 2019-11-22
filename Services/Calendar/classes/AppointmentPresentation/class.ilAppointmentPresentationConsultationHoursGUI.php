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
		$cat_info = $this->getCatInfo($cat_id);
		$context_id = $a_app['event']->getContextId();

		$this->addCommonSection($a_app, $cat_info['obj_id']);

		//objects
		include_once 'Services/Booking/classes/class.ilBookingEntry.php';
		$booking = new ilBookingEntry($context_id);

		if($manager = ilConsultationHourAppointments::getManager(true, true, $booking->getObjId()))
		{
			$this->addInfoProperty($this->lng->txt("cal_ch_manager"), $manager);
		}

		if ($booking->isOwner())
		{
			$buttons = array();
			foreach ($booking->getTargetObjIds() as $obj_id)
			{
				//$this->addObjectLinks($obj_id, $this->appointment);

				$title = ilObject::_lookupTitle($obj_id);
				$refs = $this->getReadableRefIds($obj_id);
				reset($refs);

				foreach ($refs as $ref_id)
				{
					$link_title = $title;
					if (count($refs) > 1)
					{
						$par_ref = $this->tree->getParentId($ref_id);
						$link_title .= " (" . ilObject::_lookupTitle(ilObject::_lookupObjId($par_ref)) . ")";
					}
					$buttons[] = $this->ui->renderer()->render(
						$this->ui->factory()->button()->shy($link_title, ilLink::_getStaticLink($ref_id)));
				}
			}
			if (count($buttons) > 0)
			{
				$this->addInfoProperty($this->lng->txt("cal_repo_obj"), implode("<br>", $buttons));
			}
		}

		// owner
		$this->addInfoProperty($this->lng->txt('cal_ch_booking_owner'), ilObjUser::_lookupFullname($booking->getObjId()));

		if($deadline = $booking->getDeadlineHours())
		{
			$limit = $a_app['dstart'] - ($deadline * 60 * 60);

			if( time() > $limit)
			{
				$this->addInfoProperty($this->lng->txt("cal_ch_deadline"),$this->lng->txt("exc_time_over_short"));
				//$this->addListItemProperty($this->lng->txt("cal_ch_deadline"),$this->lng->txt("exc_time_over_short"));
			}
			else
			{
				//appointment starts at -> $a_app['dstart']
				//limit registration  -> $a_app['dstart'] - $deadline

				//$string = ilUtil::period2String(new ilDateTime($limit, IL_CAL_UNIX));
				$string = ilDatePresentation::formatDate(new ilDateTime($limit, IL_CAL_UNIX));

				$this->addInfoProperty($this->lng->txt("cal_ch_deadline"),$string);
				$this->addListItemProperty($this->lng->txt("cal_ch_deadline"),$string);
			}
		}

		// max nr of bookings
		$this->addInfoProperty($this->lng->txt('cal_ch_num_bookings'), $booking->getNumberOfBookings());
		$this->addListItemProperty($this->lng->txt('cal_ch_num_bookings'), $booking->getNumberOfBookings());

		// for the following code
		// see ilCalendarAppointmentPanelGUI in ILIAS 5.2 (getHTML())
		$is_owner = $booking->isOwner();
		$user_entry = ($cat_info['obj_id'] == $this->user->getId());

		if($user_entry && !$is_owner)
		{
			// find source calendar entry in owner calendar
			include_once 'Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';
			$apps = ilConsultationHourAppointments::getAppointmentIds($booking->getObjId(), $a_app['event']->getContextId(), $a_app['event']->getStart());
			$ref_event = $apps[0];
		}
		else
		{
			$ref_event = $a_app['event']->getEntryId();
		}

		$cb = $booking->getCurrentNumberOfBookings($ref_event);
		if(!$is_owner)
		{
			$this->addInfoProperty($this->lng->txt('cal_ch_current_bookings'), $cb);
		}
		$this->addListItemProperty($this->lng->txt('cal_ch_current_bookings'), $cb);

		if(!$is_owner)
		{
			if($booking->hasBooked($ref_event))
			{
				if(ilDateTime::_after($a_app['event']->getStart(),new ilDateTime(time(),IL_CAL_UNIX)))
				{
					$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$ref_event);
					//$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->getSeed()->get(IL_CAL_DATE));
					$this->addAction($this->lng->txt('cal_ch_cancel_booking'),
						$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','cancelBooking'));
				}
			}
			elseif($booking->isAppointmentBookableForUser($ref_event,$GLOBALS['DIC']['ilUser']->getId()))
			{
				$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$ref_event);
				//$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->getSeed()->get(IL_CAL_DATE));
				$this->addAction($this->lng->txt('cal_ch_book'),
					$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','book'));
			}
		}
		else
		{
			// list booking users
			$link_users = true;
			if(ilCalendarCategories::_getInstance()->getMode() == ilCalendarCategories::MODE_PORTFOLIO_CONSULTATION)
			{
				$link_users = false;
			}
			include_once './Services/Link/classes/class.ilLink.php';
			$users = array();
			foreach($booking->getCurrentBookings($a_app['event']->getEntryId()) as $user_id)
			{
				if($link_users)
				{
					$users[] = $this->getUserName($user_id);
				}
				else
				{
					$users[] = ilObjUser::_lookupFullname($user_id);
				}
			}
			if($users) {
				$this->addInfoProperty($this->lng->txt('cal_ch_current_bookings'), implode('<br>', $users));
			}
		}

		// last edited
		$this->addLastUpdate($a_app);
	}
}