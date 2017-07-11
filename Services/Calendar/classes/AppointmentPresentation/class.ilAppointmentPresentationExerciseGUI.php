<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';

/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationExerciseGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationExerciseGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{

	public function getHTML()
	{
		global $DIC;

		include_once "./Modules/Exercise/classes/class.ilObjExercise.php";

		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();

		$a_infoscreen = $this->getInfoScreen();
		$a_app = $this->appointment;

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		$exc_obj = new ilObjExercise($cat_info['obj_id'], false);

		//Assignment title
		$a_infoscreen->addSection($a_app['event']->getPresentationTitle());

		//TODO: Fix this link
		include_once('./Services/Link/classes/class.ilLink.php');
		$href = ilLink::_getStaticLink($cat_info['obj_id'], "exc");
		$a_infoscreen->addProperty($this->lng->txt("cal_origin"),$r->render($f->button()->shy($exc_obj->getPresentationTitle(), $href)));

		//parent course or group title
		$parent_title = ilObject::_lookupTitle(ilObject::_lookupObjectId($_GET['ref_id']));
		$a_infoscreen->addProperty($this->lng->txt("cal_contained_in"),$parent_title);

		//Assignment title information
		$a_infoscreen->addSection($this->lng->txt("cal_".(ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));

		//TODO Work instructions of the assignment...
		//var_dump($a_app); exit;
		$ass_id = $a_app["event"]->getContextId() / 10;			// see ilExAssignment->handleCalendarEntries $dl parameter
		$a_infoscreen->addProperty($this->lng->txt("cal_exc_instruction"), "OK I NEED THE ASSIGNMENT $ass_id AND THEN GET THE WORK INSTRUCTIONS");
	}

}