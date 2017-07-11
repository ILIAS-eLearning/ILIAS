<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';

/**
 * ilAppointmentPresentationUserGUI class presents modal information for personal and public appointments.
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationUserGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationUserGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
	protected $seed;

	/**
	 * Get seed date
	 */
	public function getSeed()
	{
		return $this->seed;
	}

	public function getHTML()
	{
		global $lng, $ilCtrl;

		$a_infoscreen = $this->getInfoScreen();
		$a_app = $this->appointment;

		global $lng, $ilCtrl, $DIC;

		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		$a_infoscreen->addSection($a_app['event']->getTitle());

		if($a_app['event']->getDescription())
		{
			$a_infoscreen->addProperty($lng->txt("description"), ilUtil::makeClickable(nl2br($a_app['event']->getDescription())));
		}

		$ilCtrl->setParameterByClass("ilcalendarcategorygui",'category_id', $cat_id);
		$ilCtrl->setParameterByClass("ilcalendarcategorygui",'seed',$this->getSeed());

		// link to calendar
		$a_infoscreen->addProperty($lng->txt("cal_origin"),	$r->render($f->button()->shy($cat_info['title'],
			$ilCtrl->getLinkTargetByClass(array("ilPersonalDesktopGUI", "ilCalendarPresentationGUI", "ilcalendarcategorygui"), 'details'))));

		// link to user profile: todo: check if profile is really public
		include_once('./Services/Link/classes/class.ilLink.php');
		$href = ilLink::_getStaticLink($cat_info['obj_id'], "usr");
		$a_infoscreen->addProperty($lng->txt("cal_owner"),$r->render($f->button()->shy($lng->txt("link_cal_owner"), $href)));

		$a_infoscreen->addSection($lng->txt((ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id']))."_info"));

		if($a_app['event']->getLocation())
		{
			$a_infoscreen->addProperty($lng->txt("location"), ilUtil::makeClickable(nl2br($a_app['event']->getLocation())));
		}

		$a_infoscreen->addProperty($lng->txt("cal_user_notification"), "DUMMY TEXT, I DON'T KNOW WHAT IS THIS USER NOTIFICATION INFO.");
	}

}