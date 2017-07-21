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
	public function collectPropertiesAndActions()
	{
		global $DIC;

		$this->lng->loadLanguageModule("exc");

		include_once "./Modules/Exercise/classes/class.ilObjExercise.php";
		include_once('./Services/Link/classes/class.ilLink.php');
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");

		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();
		$ctrl = $DIC->ctrl();

		$a_app = $this->appointment;

		$cat_info = $this->getCatInfo();

		$exc_obj = new ilObjExercise($cat_info['obj_id'], false);
		//$exc_ref = $exc_obj->getRefId(); //emtpy...
		//is this safe?
		$exc_ref = current(ilObject::_getAllReferences($exc_obj->getId()));

		// common section: title, location, parent info
		$this->addCommonSection($a_app, $cat_info['obj_id'], null, true);

		//Assignment title information
		$this->addInfoSection($this->lng->txt("cal_".(ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));

		//TODO Work instructions, Instruction files, pass mode.
		//var_dump($a_app); exit;
		$ass_id = $a_app["event"]->getContextId() / 10;			// see ilExAssignment->handleCalendarEntries $dl parameter

		$assignment = new ilExAssignment($ass_id);
		if($assignment->getInstruction() != "")
		{
			$this->addInfoProperty($this->lng->txt("exc_instruction"), $assignment->getInstruction());
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
				$str_files[] = $r->render($f->button()->shy($file["name"],$url));
			}
			$str_files = implode("<br>", $str_files);
			$this->addInfoProperty($this->lng->txt("exc_instruction_files"),$str_files);
			$this->addListItemProperty($this->lng->txt("exc_instruction_files"),str_replace("<br>", ", ", $str_files));
		}

		//pass mode
		if($assignment->getMandatory()) {
			$this->addInfoProperty($this->lng->txt("exc_mandatory"), $this->lng->txt("yes"));
			$this->addListItemProperty($this->lng->txt("exc_mandatory"), $this->lng->txt("yes"));
		}
		else {
			$this->addInfoProperty($this->lng->txt("exc_mandatory"), $this->lng->txt("no"));
			$this->addListItemProperty($this->lng->txt("exc_mandatory"), $this->lng->txt("no"));
		}

		//example download all files
		$this->addAction($this->lng->txt("cal_download_all_files"), "www.ilias.de");

		//go to the exercise.
		$this->addAction($this->lng->txt("cal_exc_open"), ilLink::_getStaticLink($exc_ref, "exc"));

	}
}