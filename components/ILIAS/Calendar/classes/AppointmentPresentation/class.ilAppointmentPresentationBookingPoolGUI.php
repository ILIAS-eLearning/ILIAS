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

/**
 * @author            Jesús López Reyes <lopez@leifos.com>
 * @ilCtrl_IsCalledBy ilAppointmentPresentationBookingPoolGUI: ilCalendarAppointmentPresentationGUI
 * @ingroup           ServicesCalendar
 */
class ilAppointmentPresentationBookingPoolGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
    public function collectPropertiesAndActions(): void
    {
        global $DIC;

        $a_app = $this->appointment;
        $cat_info = $this->getCatInfo();

        $this->lng->loadLanguageModule("book");

        // context id is reservation id (see ilObjBookingPoolGUI->processBooking)
        $res_id = $a_app['event']->getContextId();
        $res = new ilBookingReservation($res_id);
        $b_obj = new ilBookingObject($res->getObjectId());
        $obj_id = $b_obj->getPoolId();
        $objects_manager = $DIC->bookingManager()->internal()->domain()->objects($obj_id);

        $refs = $this->getReadableRefIds($obj_id);

        // add common section (title, description, object/calendar, location)
        //$this->addCommonSection($a_app, $obj_id, $cat_info);

        if (count($refs) > 0) {
            $this->addInfoSection($b_obj->getTitle());

            // object description
            if ($b_obj->getDescription()) {
                $this->addInfoProperty($this->lng->txt("description"), $b_obj->getDescription());
            }

            $this->addObjectLinks($obj_id, $this->appointment);

            //object info (course, grp...)
            //$this->addContainerInfo($obj_id);

            //link to personal bookings
            $this->addCalendarInfo($cat_info);

            // section: booking information
            if ($b_obj->getDescription() || $objects_manager->hasObjectInfo($b_obj->getId())) {
                $this->addInfoSection($this->lng->txt("book_booking_information"));
            }

            $ref_id = current($refs);

            $this->ctrl->setParameterByClass("ilObjBookingPoolGUI", "ref_id", $ref_id);
            $this->ctrl->setParameterByClass("ilbookingobjectgui", "object_id", $res->getObjectId());

            // info file
            if ($objects_manager->hasObjectInfo($b_obj->getId())) {
                $this->has_files = true;
                $link = $this->ctrl->getLinkTargetByClass(array("ilRepositoryGUI",
                                                                "ilObjBookingPoolGUI",
                                                                "ilbookingobjectgui"
                ), "deliverInfo");

                $link = $this->ui->renderer()->render(
                    $this->ui->factory()->button()->shy($objects_manager->getObjectInfoFilename($b_obj->getId()), $link)
                );

                $this->addInfoProperty($this->lng->txt("book_additional_info_file"), $link);
            }

            // post file
            $array_info = array();
            if (($text = $b_obj->getPostText()) != "") {
                $period = ilDatePresentation::formatPeriod(
                    $a_app['event']->getStart(),
                    $a_app['event']->getEnd()
                );
                $text = str_replace("[OBJECT]", $b_obj->getTitle(), $text);
                $text = str_replace("[PERIOD]", $period, $text);
                $array_info[] = $text;
            }
            if ($objects_manager->hasBookingInfo($b_obj->getId())) {
                $this->has_files = true;

                $link = $this->ctrl->getLinkTargetByClass(array("ilRepositoryGUI",
                                                                "ilObjBookingPoolGUI",
                                                                "ilbookingobjectgui",
                                                                "ilBookingProcessWithScheduleGUI"
                ), "deliverPostFile");

                $array_info[] = $this->ui->renderer()->render(
                    $this->ui->factory()->button()->shy($objects_manager->getBookingInfoFilename($b_obj->getId()), $link)
                );
            }
            if ($array_info) {
                $this->addInfoProperty($this->lng->txt("book_post_booking_information"), implode("<br>", $array_info));
            }
        }

        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());
        $this->addAction(
            $this->lng->txt("cal_ch_cancel_booking"),
            $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'cancelBooking')
        );

        if (count($refs) > 0) {
            $this->addAction($this->lng->txt("book_open"), ilLink::_getStaticLink(current($refs)));
        }

        $this->addMetaData('book', $obj_id, "bobj", $res->getObjectId());
    }
}
