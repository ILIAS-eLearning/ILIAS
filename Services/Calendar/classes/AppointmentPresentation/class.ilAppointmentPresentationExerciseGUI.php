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
        $exc_obj = new ilObjExercise($this->getObjIdForAppointment(), false);
        //is this safe?

        $refs = $this->getReadableRefIds($this->getObjIdForAppointment());
        $exc_ref = current($refs);

        // common section: title, location, parent info
        $this->addCommonSection($a_app, $this->getObjIdForAppointment(), null, true);

        //Assignment title information
        $this->addInfoSection($this->lng->txt("cal_exc_info"));

        //var_dump($a_app); exit;
        $ass_id = $a_app["event"]->getContextId() / 10;			// see ilExAssignment->handleCalendarEntries $dl parameter

        $assignment = new ilExAssignment($ass_id);
        $assignment_instructions = trim($assignment->getInstruction());
        if ($assignment_instructions != "") {
            #21517
            $is_html = (strlen($assignment_instructions) != strlen(strip_tags($assignment_instructions)));
            if (!$is_html) {
                $assignment_instructions = nl2br($assignment_instructions);
            }
            $this->addInfoProperty($this->lng->txt("exc_instruction"), $assignment_instructions);
        }
        $files = $assignment->getFiles();
        if (count($files) > 0) {
            $this->has_files = true;
            $str_files = array();
            foreach ($files as $file) {
                $ctrl->setParameterByClass("ilexsubmissiongui", "ref_id", $exc_ref);
                $ctrl->setParameterByClass("ilexsubmissiongui", "file", urlencode($file["name"]));
                $ctrl->setParameterByClass("ilexsubmissiongui", "ass_id", $ass_id);
                $url = $ctrl->getLinkTargetByClass(array("ilExerciseHandlerGUI","ilobjexercisegui", "ilexsubmissiongui"), "downloadFile");
                $ctrl->setParameterByClass("ilexsubmissiongui", "ass_id", "");
                $ctrl->setParameterByClass("ilexsubmissiongui", "file", "");
                $ctrl->setParameterByClass("ilexsubmissiongui", "ref_if", "");
                $str_files[$file["name"]] = $r->render($f->button()->shy($file["name"], $url));
            }
            ksort($str_files, SORT_NATURAL | SORT_FLAG_CASE);
            $str_files = implode("<br>", $str_files);
            $this->addInfoProperty($this->lng->txt("exc_instruction_files"), $str_files);
            $this->addListItemProperty($this->lng->txt("exc_instruction_files"), str_replace("<br>", ", ", $str_files));
        }

        //pass mode
        if ($assignment->getMandatory()) {
            $this->addInfoProperty($this->lng->txt("exc_mandatory"), $this->lng->txt("yes"));
            $this->addListItemProperty($this->lng->txt("exc_mandatory"), $this->lng->txt("yes"));
        } else {
            $this->addInfoProperty($this->lng->txt("exc_mandatory"), $this->lng->txt("no"));
            $this->addListItemProperty($this->lng->txt("exc_mandatory"), $this->lng->txt("no"));
        }

        // last edited
        $this->addLastUpdate($a_app);

        //go to the exercise.
        $this->addAction(
            $this->lng->txt("cal_exc_open"),
            "goto.php?target=exc_" . $exc_ref . "_" . $ass_id . "&client_id=" . CLIENT_ID
        );
    }

    /**
     * @inheritdoc
     */
    protected function buildDirectLinkForAppointment($a_ref_id, $a_appointment = null)
    {
        $ass_id = 0;
        if (is_array($a_appointment)) {
            $ass_id = $a_appointment["event"]->getContextId() / 10;
        }
        if (!$ass_id) {
            return parent::buildDirectLinkForAppointment($a_ref_id, $a_appointment);
        }
        return ilLink::_getLink(
            $a_ref_id,
            'exc',
            [],
            '_' . $ass_id
        );
    }
}
