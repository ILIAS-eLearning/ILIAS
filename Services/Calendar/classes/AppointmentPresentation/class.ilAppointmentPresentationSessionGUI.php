<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';

/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationSessionGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationSessionGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{

	public function getHTML()
	{
		global $DIC;

		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();
		$crl = $DIC->ctrl();

		$a_infoscreen = $this->getInfoScreen();
		$a_app = $this->appointment;

		include_once "./Modules/Session/classes/class.ilObjSession.php";

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		//Title of the session (The title can be a date... which date? and why no the title of the session?)
		$a_infoscreen->addSection($cat_info['title']);

		//description
		$a_infoscreen->addProperty($this->lng->txt("description"), $a_app['event']->getDescription());

		//Contained in:
		$parent_title = ilObject::_lookupTitle(ilObject::_lookupObjectId($_GET['ref_id']));
		$a_infoscreen->addProperty($this->lng->txt("cal_contained_in"),$parent_title);

		//SESSION INFORMATION
		$a_infoscreen->addSection($this->lng->txt("cal_".(ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));

		$session_obj = new ilObjSession($cat_info['obj_id'],false);
		// safe? only one?
		$session_ref = current(ilObject2::_getAllReferences($session_obj->getId()));

		//location
		if($session_obj->getLocation()){
			$a_infoscreen->addProperty($this->lng->txt("cal_location"),ilUtil::makeClickable($session_obj->getLocation()));
		}
		//details/workflow
		if($session_obj->getDetails())
		{
			$a_infoscreen->addProperty($this->lng->txt("cal_details_workflow"),$session_obj->getDetails());
		}
		//lecturer name
		$str_lecturer = "";
		if($session_obj->getName())
		{
			$str_lecturer .= $session_obj->getName()."<br>";
		}
		//lecturer email
		if($session_obj->getEmail())
		{
			$str_lecturer .= $session_obj->getEmail()."<br>";
		}
		if($session_obj->getPhone())
		{
			$str_lecturer .= $this->lng->txt("phone").": ".$session_obj->getPhone()."<br>";
		}
		$a_infoscreen->addProperty($this->lng->txt("cal_info_lecturer"), $str_lecturer);

		$eventItems = ilObjectActivation::getItemsByEvent($cat_info['obj_id']);
		if(count($eventItems))
		{
			include_once('./Services/Link/classes/class.ilLink.php');
			$str = "";
			foreach ($eventItems as $file)
			{
				$href = ilLink::_getStaticLink($file['ref_id'], "file", true,"download");
				$str .= $r->render($f->button()->shy($file['title'], $href))."<br>";
			}
			$a_infoscreen->addProperty($this->lng->txt("files"),$str);
		}

		//[Download All Files] [Attend Session] [Open Session]
		// fill toolbar
		$toolbar = $this->getToolbar();

		//example download all files
		$btn_download = ilLinkButton::getInstance();
		$btn_download->setCaption($this->lng->txt("cal_download_all_files"));
		$btn_download->setUrl("www.ilias.de");
		$toolbar->addButtonInstance($btn_download);

		$btn_open = ilLinkButton::getInstance();
		$btn_open->setCaption($this->lng->txt("cal_sess_open"));
		$btn_open->setUrl(ilLink::_getStaticLink($session_ref, "sess"));
		$toolbar->addButtonInstance($btn_open);


/** working here */
		//TODO: BUTTON TO register/unregister to sessions. Relevant info: in ilObjSessionGUI method showJoinRequestButton
		//$this->showJoinRequestButton($session_obj, $toolbar);

		/*
			$ctrl->setParameter($this, 'file_id', $file->getFileId());
			$ctrl->setParameterByClass('ilobjcoursegui','file_id', $file->getFileId());
			$ctrl->setParameterByClass('ilobjcoursegui','ref_id', $crs_ref_id);
			$tpl->setVariable("DOWN_LINK",$this->ctrl->getLinkTargetByClass(array("ilRepositoryGUI","ilobjcoursegui"),'sendfile'));
		*/

	}
}