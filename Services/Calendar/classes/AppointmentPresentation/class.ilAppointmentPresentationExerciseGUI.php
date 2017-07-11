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
		$ctrl = $DIC->ctrl();

		$a_infoscreen = $this->getInfoScreen();
		$a_app = $this->appointment;

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		$exc_obj = new ilObjExercise($cat_info['obj_id'], false);
		//$exc_ref = $exc_obj->getRefId(); //emtpy...
		$exc_ref = current(ilObject::_getAllReferences($exc_obj->getId()));

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

		//TODO Work instructions, Instruction files, pass mode.
		//var_dump($a_app); exit;
		$ass_id = $a_app["event"]->getContextId() / 10;			// see ilExAssignment->handleCalendarEntries $dl parameter

		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$assignment = new ilExAssignment($ass_id);
		if($assignment->getInstruction() != "")
		{
			$a_infoscreen->addProperty($this->lng->txt("cal_exc_instruction"), $assignment->getInstruction());
		}
		$files = $assignment->getFiles();
		if(count($files) > 0)
		{
			$str_files = "";
			//TODO Fix this Links and show only one infoscreen property.
			foreach($files as $file)
			{
				$ctrl->setParameterByClass("ilexsubmissiongui", "ref_if", $exc_ref);
				$ctrl->setParameterByClass("ilexsubmissiongui", "file", urlencode($file["name"]));
				$ctrl->setParameterByClass("ilexsubmissiongui", "ass_id", $ass_id);
				$url = $ctrl->getLinkTargetByClass(array("ilExerciseHandlerGUI","ilobjexercisegui", "ilexsubmissiongui"), "downloadFile");
				$ctrl->setParameterByClass("ilexsubmissiongui", "ass_id", "");
				$ctrl->setParameterByClass("ilexsubmissiongui", "file", "");
				$ctrl->setParameterByClass("ilexsubmissiongui", "ref_if", "");
				$a_infoscreen->addProperty($file["name"], $this->lng->txt("cal_download"), $url);
			}
		}

		//pass mode
		if($assignment->getMandatory()) {
			$a_infoscreen->addProperty($this->lng->txt("cal_exc_pass_mode"), $this->lng->txt("cal_exc_pass_mode_mandatory"));
		}
		else {
			$a_infoscreen->addProperty($this->lng->txt("cal_exc_pass_mode"), $this->lng->txt("cal_exc_pass_mode_no_mandatory"));
		}

		// fill toolbar
		$toolbar = $this->getToolbar();

		//example download all files
		$btn_download = ilLinkButton::getInstance();
		$btn_download->setCaption($this->lng->txt("cal_download_all_files"));
		$btn_download->setUrl("www.ilias.de");
		$toolbar->addButtonInstance($btn_download);

		//todo: hand in, create team
		/**
		 * FROM THE WIKI ENTRY (all to the same view?)
		 * "Hand in" will take user to the view page of this assignment.
		 *	"Create Team" will take user to the view page of this assignment.
		 * "Open Assignment" will take user to the view page of this assignment.
		 */

		//go to the exercise.
		$btn_open = ilLinkButton::getInstance();
		$btn_open->setCaption($this->lng->txt("cal_exc_open"));
		$btn_open->setUrl(ilLink::_getStaticLink($exc_ref, "exc"));
		$toolbar->addButtonInstance($btn_open);

	}
}