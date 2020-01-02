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
        $this->lng->loadLanguageModule("sess");
        $this->lng->loadLanguageModule("crs");
        /**
         * @var ilCalendarEntry
         */
        $a_app = $this->appointment;
        include_once "./Modules/Session/classes/class.ilObjSession.php";

        $cat_info = $this->getCatInfo();

        $refs = $this->getReadableRefIds($this->getObjIdForAppointment());
        $ref_id = current($refs);

        // event title
        $this->addInfoSection($a_app["event"]->getTitle());

        // event description
        $this->addEventDescription($a_app);

        // event location
        $this->addEventLocation($a_app);

        //Contained in:
        $this->addContainerInfo($this->getObjIdForAppointment());

        //SESSION INFORMATION
        $this->addInfoSection(
            $this->lng->txt("cal_sess_info")
        );

        $session_obj = new ilObjSession($this->getObjIdForAppointment(), false);

        //location
        if ($session_obj->getLocation()) {
            $this->addInfoProperty($this->lng->txt("event_location"), ilUtil::makeClickable(nl2br($session_obj->getLocation())));
            $this->addListItemProperty($this->lng->txt("event_location"), ilUtil::makeClickable(nl2br($session_obj->getLocation())));
        }
        //details/workflow
        if ($session_obj->getDetails()) {
            $this->addInfoProperty($this->lng->txt("event_details_workflow"), ilUtil::makeClickable(nl2br($session_obj->getDetails())));
        }
        //lecturer name
        $str_lecturer = array();
        if ($session_obj->getName()) {
            $str_lecturer[] = $session_obj->getName();
        }
        //lecturer email
        if ($session_obj->getEmail()) {
            $str_lecturer[] = $session_obj->getEmail();
        }
        if ($session_obj->getPhone()) {
            $str_lecturer[] = $this->lng->txt("phone") . ": " . $session_obj->getPhone();
        }
        if (count($str_lecturer) > 0) {
            $this->addInfoProperty($this->lng->txt("event_tutor_data"), implode("<br>", $str_lecturer));
            $this->addListItemProperty($this->lng->txt("event_tutor_data"), implode(", ", $str_lecturer));
        }

        $eventItems = ilObjectActivation::getItemsByEvent($this->getObjIdForAppointment());
        if (count($eventItems)) {
            include_once('./Services/Link/classes/class.ilLink.php');
            $str = array();
            foreach ($eventItems as $file) {
                if ($file['type'] == "file") {
                    $this->has_files = true;
                    $href = ilLink::_getStaticLink($file['ref_id'], "file", true, "download");
                    $link = $f->link()->standard($file['title'], $href);
                    require_once('Modules/File/classes/class.ilObjFileAccess.php');
                    if (ilObjFileAccess::_isFileInline($file["title"])) {
                        $link = $link->withOpenInNewViewport(true);
                    }
                    $str[$file['title']] = $r->render($link);
                }
            }
            if ($this->has_files) {
                ksort($str, SORT_NATURAL | SORT_FLAG_CASE);
                $this->addInfoProperty($this->lng->txt("files"), implode("<br>", $str));
                $this->addListItemProperty($this->lng->txt("files"), implode(", ", $str));
            }
        }

        $this->addAction($this->lng->txt("sess_open"), ilLink::_getStaticLink($ref_id));

        $this->addMetaData('sess', $this->getObjIdForAppointment());
    }
}
