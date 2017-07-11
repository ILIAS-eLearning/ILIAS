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

	public function getHTML()
	{
		include_once "./Modules/Group/classes/class.ilObjGroup.php";

		$a_infoscreen = $this->getInfoScreen();
		$a_app = $this->appointment;

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		$grp = new ilObjGroup($cat_info['obj_id'], false);

		$refs = ilObject::_getAllReferences($cat_info['obj_id']);
		$grp_ref_id = current($refs);

		$description_text = $cat_info['title'] . ", " . ilObject::_lookupDescription($cat_info['obj_id']);
		$a_infoscreen->addSection($cat_info['title']);

		if ($a_app['event']->getDescription()) {
			$a_infoscreen->addProperty($this->lng->txt("description"), ilUtil::makeClickable(nl2br($a_app['event']->getPresentationTitle())));
		}
		$a_infoscreen->addProperty($this->lng->txt(ilObject::_lookupType($cat_info['obj_id'])), $description_text);

		if($grp->getInformation())
		{
			$a_infoscreen->addSection($this->lng->txt((ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));
			$a_infoscreen->addProperty($this->lng->txt("crs_important_info"), $grp->getInformation());
		}

		// fill toolbar
		$toolbar = $this->getToolbar();

		//example download all files
		$btn_download = ilLinkButton::getInstance();
		$btn_download->setCaption($this->lng->txt("cal_download_all_files"));
		$btn_download->setUrl("www.ilias.de");
		$toolbar->addButtonInstance($btn_download);

		$btn_open = ilLinkButton::getInstance();
		$btn_open->setCaption($this->lng->txt("cal_grp_open"));
		$btn_open->setUrl(ilLink::_getStaticLink($grp_ref_id, "grp"));
		$toolbar->addButtonInstance($btn_open);

	}

}