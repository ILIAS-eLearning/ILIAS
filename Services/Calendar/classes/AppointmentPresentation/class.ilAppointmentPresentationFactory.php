<?php
/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationFactory
{
	public static function getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item)
	{
		global $lng;

		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');

		//get object info
		$cat_id = ilCalendarCategoryAssignments::_lookupCategory($a_appointment['event']->getEntryId());
		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
		$entry_obj_id = isset($cat_info['subitem_obj_ids'][$cat_id]) ?
			$cat_info['subitem_obj_ids'][$cat_id] :
			$cat_info['obj_id'];
		switch($cat_info['type'])
		{
			case ilCalendarCategory::TYPE_OBJ:
				$type = ilObject::_lookupType($cat_info['obj_id']);
				switch($type)
				{
					case "crs":
						require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationCourseGUI.php";
						return ilAppointmentPresentationCourseGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
						break;
					case "grp":
						require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGroupGUI.php";
						return ilAppointmentPresentationGroupGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
						break;
					case "sess":
						require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationSessionGUI.php";
						return ilAppointmentPresentationSessionGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
						break;
					case "exc":
						include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationExerciseGUI.php';
						return ilAppointmentPresentationExerciseGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
						break;
					default:
						include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';
						return ilAppointmentPresentationGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item); // title, description etc... link to generic object.
				}
				break;
			case ilCalendarCategory::TYPE_USR:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationUserGUI.php";
				return ilAppointmentPresentationUserGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
				break;
			//TYPE GLOBAL uses the same code/data as TYPE_USR
			case ilCalendarCategory::TYPE_GLOBAL:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationUserGUI.php";
				return ilAppointmentPresentationUserGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
				break;
			case ilCalendarCategory::TYPE_CH:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationUserGUI.php";
				return ilAppointmentPresentationUserGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
				break;
			case ilCalendarCategory::TYPE_BOOK:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationBookingPoolGUI.php";
				return ilAppointmentPresentationBookingPoolGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
			default:
				include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';
				return ilAppointmentPresentationGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);

		}
	}
}