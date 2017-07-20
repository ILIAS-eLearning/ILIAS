<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';

/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationGroupGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationGroupGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{

	public function collectPropertiesAndActions()
	{
		include_once "./Modules/Group/classes/class.ilObjGroup.php";

		$a_app = $this->appointment;

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		$grp = new ilObjGroup($cat_info['obj_id'], false);

		$refs = ilObject::_getAllReferences($cat_info['obj_id']);
		$grp_ref_id = current($refs);

		$description_text = $cat_info['title'] . ", " . ilObject::_lookupDescription($cat_info['obj_id']);
		$this->addInfoSection($cat_info['title']);

		if ($a_app['event']->getDescription()) {
			$this->addInfoProperty($this->lng->txt("description"), ilUtil::makeClickable(nl2br($a_app['event']->getPresentationTitle())));
		}
		$this->addInfoProperty($this->lng->txt(ilObject::_lookupType($cat_info['obj_id'])), $description_text);

		if($grp->getInformation())
		{
			$this->addInfoSection($this->lng->txt("cal_".(ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));
			$this->addInfoProperty($this->lng->txt("crs_important_info"), $grp->getInformation());
		}

		//example download all files
		$this->addAction($this->lng->txt("cal_download_all_files"), "www.ilias.de");

		$this->addAction($this->lng->txt("cal_crs_open"), ilLink::_getStaticLink($grp_ref_id, "crs"));
	}

}