<?php

declare(strict_types=1);

/**
 * @author            Jesús López Reyes <lopez@leifos.com>
 * @ilCtrl_IsCalledBy ilAppointmentPresentationSessionGUI: ilCalendarAppointmentPresentationGUI
 * @ingroup           ServicesCalendar
 */
class ilAppointmentPresentationSessionGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
    public function collectPropertiesAndActions(): void
    {
        global $DIC;

        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();
        $this->lng->loadLanguageModule("sess");
        $this->lng->loadLanguageModule("crs");

        $a_app = $this->appointment;
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
            $this->addInfoProperty(
                $this->lng->txt("event_location"),
                ilUtil::makeClickable(nl2br($session_obj->getLocation()))
            );
            $this->addListItemProperty(
                $this->lng->txt("event_location"),
                ilUtil::makeClickable(nl2br($session_obj->getLocation()))
            );
        }
        //details/workflow
        if ($session_obj->getDetails()) {
            $this->addInfoProperty(
                $this->lng->txt("event_details_workflow"),
                ilUtil::makeClickable(nl2br($session_obj->getDetails()))
            );
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
            $str = array();
            foreach ($eventItems as $file) {
                if ($file['type'] == "file") {
                    $this->has_files = true;
                    $href = ilLink::_getStaticLink((int)$file['ref_id'], "file", true, "download");
                    $link = $f->link()->standard($file['title'], $href);
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

        $others = $this->getOtherMaterials();
        if (count($others)) {
            $refs = ilObject::_getAllReferences($this->getObjIdForAppointment());
            $ref_id = end($refs);
            $materials_link = $r->render(
                $f->link()->standard(
                    $this->lng->txt('cal_app_other_materials_num'),
                    ilLink::_getLink($ref_id)
                )
            );
            $this->addInfoProperty(
                $this->lng->txt('cal_materials'),
                $materials_link
            );
        }
        $this->addAction($this->lng->txt("sess_open"), ilLink::_getStaticLink($ref_id));

        $this->addMetaData('sess', $this->getObjIdForAppointment());
    }

    /**
     * @return int[]
     */
    protected function getOtherMaterials(): array
    {
        global $DIC;

        $event_items = new ilEventItems($this->getObjIdForAppointment());
        $others = [];
        foreach ($event_items->getItems() as $ref_id) {
            $type = ilObject::_lookupType($ref_id, true);
            if ($type == 'file') {
                continue;
            }
            if ($this->access->checkAccess('read', '', $ref_id)) {
                $others[] = $ref_id;
            }
        }
        return $others;
    }
}
