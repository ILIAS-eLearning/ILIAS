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

	public function collectPropertiesAndActions()
	{
		global $DIC;

		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();

		$this->lng->loadLanguageModule("crs");

		$a_app = $this->appointment;

		include_once "./Modules/Session/classes/class.ilObjSession.php";

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		//Title of the session (The title can be a date... which date? and why no the title of the session?)
		$this->addInfoSection($cat_info['title']);

		//description
		$this->addInfoProperty($this->lng->txt("description"), $a_app['event']->getDescription());

		//Contained in:
		$parent_title = ilObject::_lookupTitle(ilObject::_lookupObjectId($_GET['ref_id']));
		$this->addInfoProperty($this->lng->txt("cal_contained_in"),$parent_title);

		//SESSION INFORMATION
		$this->addInfoSection($this->lng->txt("cal_".(ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));

		$session_obj = new ilObjSession($cat_info['obj_id'],false);
		// safe? only one?
		$session_ref = current(ilObject2::_getAllReferences($session_obj->getId()));

		//location
		if($session_obj->getLocation()){
			$this->addInfoProperty($this->lng->txt("event_location"),ilUtil::makeClickable($session_obj->getLocation()));
		}
		//details/workflow
		if($session_obj->getDetails())
		{
			$this->addInfoProperty($this->lng->txt("event_details_workflow"),$session_obj->getDetails());
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
		$this->addInfoProperty($this->lng->txt("event_tutor_data"), $str_lecturer);

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
			$this->addInfoProperty($this->lng->txt("files"),$str);
		}

		//example download all files
		$this->addAction($this->lng->txt("cal_download_all_files"), "www.ilias.de");

		$this->addAction($this->lng->txt("cal_sess_open"), ilLink::_getStaticLink($session_ref, "crs"));

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