<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\UI\Component\Link\Standard as StandardLink;

/**
 * @author            Jesús López Reyes <lopez@leifos.com>
 * @ilCtrl_IsCalledBy ilAppointmentPresentationSessionGUI: ilCalendarAppointmentPresentationGUI
 * @ingroup           ServicesCalendar
 */
class ilAppointmentPresentationSessionGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
    public function collectPropertiesAndActions(): void
    {
        $file_infos = new ilObjFileInfoRepository();
        $f = $this->ui->factory();
        $r = $this->ui->renderer();
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
            $links = [];
            foreach ($eventItems as $file) {
                if ($file['type'] === "file") {
                    $file_ref_id = (int) $file['ref_id'];
                    $file_info = $file_infos->getByRefId($file_ref_id);
                    $this->has_files = true;
                    $href = ilLink::_getStaticLink($file_ref_id, "file", true, '_download');
                    $link = $f->link()->standard($file_info->getListTitle(), $href);
                    if ($file_info->shouldDeliverInline()) {
                        $link = $link->withOpenInNewViewport(true);
                    }
                    $links[] = $link;
                }
            }
            if ($this->has_files) {
                $rendered = $this->sortAndRenderLinks(...$links);
                $this->addInfoProperty($this->lng->txt("files"), implode("<br>", $rendered));
                $this->addListItemProperty($this->lng->txt("files"), implode(", ", $rendered));
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

    /**
     * @return string[]
     */
    protected function sortAndRenderLinks(StandardLink ...$links): array
    {
        usort($links, function (StandardLink $a, $b) {
            return $a->getLabel() <=> $b->getLabel();
        });
        $rendered = [];
        foreach ($links as $link) {
            $rendered[] = $this->ui->renderer()->render($link);
        }
        return $rendered;
    }
}
