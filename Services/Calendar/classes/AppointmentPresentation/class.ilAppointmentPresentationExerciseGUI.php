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

		$this->lng->loadLanguageModule("exc");


		include_once "./Modules/Exercise/classes/class.ilObjExercise.php";
		include_once('./Services/Link/classes/class.ilLink.php');
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");

		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();
		$ctrl = $DIC->ctrl();

		$a_infoscreen = $this->getInfoScreen();
		$a_app = $this->appointment;

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		$exc_obj = new ilObjExercise($cat_info['obj_id'], false);
		//$exc_ref = $exc_obj->getRefId(); //emtpy...
		//is this safe?
		$exc_ref = current(ilObject::_getAllReferences($exc_obj->getId()));

		//Assignment title
		$a_infoscreen->addSection($a_app['event']->getPresentationTitle());

		$href = ilLink::_getStaticLink($exc_ref, "exc");
		$a_infoscreen->addProperty($this->lng->txt("obj_exc"),$r->render($f->button()->shy($exc_obj->getPresentationTitle(), $href)));

		$this->addContainerInfo($exc_ref);

		//Assignment title information
		$a_infoscreen->addSection($this->lng->txt("cal_".(ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));

		//TODO Work instructions, Instruction files, pass mode.
		//var_dump($a_app); exit;
		$ass_id = $a_app["event"]->getContextId() / 10;			// see ilExAssignment->handleCalendarEntries $dl parameter

		$assignment = new ilExAssignment($ass_id);
		if($assignment->getInstruction() != "")
		{
			$a_infoscreen->addProperty($this->lng->txt("exc_instruction"), $assignment->getInstruction());
		}
		$files = $assignment->getFiles();
		if(count($files) > 0)
		{
			$str_files = "";
			foreach($files as $file)
			{
				$ctrl->setParameterByClass("ilexsubmissiongui", "ref_id", $exc_ref);
				$ctrl->setParameterByClass("ilexsubmissiongui", "file", urlencode($file["name"]));
				$ctrl->setParameterByClass("ilexsubmissiongui", "ass_id", $ass_id);
				$url = $ctrl->getLinkTargetByClass(array("ilExerciseHandlerGUI","ilobjexercisegui", "ilexsubmissiongui"), "downloadFile");
				$ctrl->setParameterByClass("ilexsubmissiongui", "ass_id", "");
				$ctrl->setParameterByClass("ilexsubmissiongui", "file", "");
				$ctrl->setParameterByClass("ilexsubmissiongui", "ref_if", "");
				$btn_link = $f->button()->shy($file["name"],$url);
				$str_files .=$r->render($btn_link)."<br>";
			}
			$a_infoscreen->addProperty($this->lng->txt("exc_files"),$str_files);
		}

		//pass mode
		if($assignment->getMandatory()) {
			$a_infoscreen->addProperty($this->lng->txt("exc_mandatory"), $this->lng->txt("yes"));
		}
		else {
			$a_infoscreen->addProperty($this->lng->txt("exc_mandatory"), $this->lng->txt("no"));
		}

		// fill toolbar
		$toolbar = $this->getToolbar();

		//example download all files
		$btn_download = ilLinkButton::getInstance();
		$btn_download->setCaption("cal_download_all_files");
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
		$btn_open->setCaption("cal_exc_open");
		$btn_open->setUrl(ilLink::_getStaticLink($exc_ref, "exc"));
		$toolbar->addButtonInstance($btn_open);

	}
}