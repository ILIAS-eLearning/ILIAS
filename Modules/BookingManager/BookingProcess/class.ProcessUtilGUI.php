<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\BookingProcess;

use ILIAS\BookingManager\InternalDomainService;
use ILIAS\BookingManager\InternalGUIService;

/**
 * Common functions for process GUI classes
 * @author Alexander Killing <killing@leifos.de>
 */
class ProcessUtilGUI
{
    protected \ilObjBookingPool $pool;
    protected \ilBookingHelpAdapter $help;
    protected InternalGUIService $gui;
    protected InternalDomainService $domain;
    protected object $parent_gui;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service,
        \ilObjBookingPool $pool,
        object $parent_gui
    )
    {
        $this->gui = $gui_service;
        $this->domain = $domain_service;
        $this->help = $gui_service->bookingHelp($pool);
        $this->parent_gui = $parent_gui;
        $this->pool = $pool;
    }

    // Back to parent
    public function back() : void
    {
        $ctrl = $this->gui->ctrl();
        $ctrl->returnToParent($this->parent_gui);
    }

    public function setHelpId(string $a_id) : void
    {
        $this->help->setHelpId($a_id);
    }

    // Table to assign participants to an object.
    public function assignParticipants(int $book_obj_id) : void
    {
        $tabs = $this->gui->tabs();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $main_tpl = $this->gui->mainTemplate();

        $tabs->clearTargets();
        $tabs->setBackTarget($lng->txt('book_back_to_list'), $ctrl->getLinkTarget($this->parent_gui, 'back'));

        $table = new \ilBookingAssignParticipantsTableGUI($this->parent_gui, 'assignParticipants', $this->pool->getRefId(), $this->pool->getId(), $book_obj_id);
        $main_tpl->setContent($table->getHTML());
    }


    /*
    public function checkPermissionBool(string $a_perm) : bool
    {
        $access = $this->domain->access();
        if (!$access->checkAccess($a_perm, "", $this->pool->getRefId())) {
            return false;
        }
        return true;
    }

    protected function checkPermission(string $a_perm) : void
    {
        $main_tpl = $this->gui->mainTemplate();
        $lng = $this->domain->lng();

        if (!$this->checkPermissionBool($a_perm)) {
            $main_tpl->setOnScreenMessage('failure', $lng->txt("no_permission"), true);
            $this->back();
        }
    }*/

    public function handleBookingSuccess(
        int $a_obj_id,
        string $post_info_cmd,
        array $a_rsv_ids = null
    ) : void {
        $main_tpl = $this->gui->mainTemplate();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $request = $this->gui->standardRequest();

        $main_tpl->setOnScreenMessage('success', $lng->txt('book_reservation_confirmed'), true);

        // show post booking information?
        $obj = new \ilBookingObject($a_obj_id);
        $pfile = $obj->getPostFile();
        $ptext = $obj->getPostText();

        if (trim($ptext) || $pfile) {
            if (count($a_rsv_ids)) {
                $ctrl->setParameter(
                    $this->parent_gui,
                    'rsv_ids',
                    implode(";", $a_rsv_ids)
                );
            }
            $ctrl->redirect($this->parent_gui, $post_info_cmd);
        } else {
            if ($ctrl->isAsynch()) {
                $this->gui->send("<script>window.location.href = '" .
                    $ctrl->getLinkTarget($this->parent_gui, $request->getReturnCmd()) . "';</script>");
            } else {
                $this->back();
            }
        }
    }

    /**
     * Display post booking informations
     */
    public function displayPostInfo(
        int $book_obj_id,
        int $user_id,
        string $file_deliver_cmd
    ) : void
    {
        $main_tpl = $this->gui->mainTemplate();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $id = $book_obj_id;
        $request = $this->gui->standardRequest();

        if (!$id) {
            return;
        }

        $rsv_ids = $request->getReservationIdsFromString();

        // placeholder

        $book_ids = \ilBookingReservation::getObjectReservationForUser($id, $user_id);
        $tmp = array();
        foreach ($book_ids as $book_id) {
            if (in_array($book_id, $rsv_ids)) {
                $obj = new \ilBookingReservation($book_id);
                $from = $obj->getFrom();
                $to = $obj->getTo();
                if ($from > time()) {
                    $tmp[$from . "-" . $to] = $tmp[$from . "-" . $to] ?? 0;
                    $tmp[$from . "-" . $to]++;
                }
            }
        }

        $olddt = \ilDatePresentation::useRelativeDates();
        \ilDatePresentation::setUseRelativeDates(false);

        $period = array();
        ksort($tmp);
        foreach ($tmp as $time => $counter) {
            $time = explode("-", $time);
            $time = \ilDatePresentation::formatPeriod(
                new \ilDateTime($time[0], IL_CAL_UNIX),
                new \ilDateTime($time[1], IL_CAL_UNIX)
            );
            if ($counter > 1) {
                $time .= " (" . $counter . ")";
            }
            $period[] = $time;
        }
        $book_id = array_shift($book_ids);

        \ilDatePresentation::setUseRelativeDates($olddt);

        /*
        #23578 since Booking pool participants.
        $obj = new ilBookingReservation($book_id);
        if ($obj->getUserId() != $ilUser->getId())
        {
            return;
        }
        */

        $obj = new \ilBookingObject($id);
        $pfile = $obj->getPostFile();
        $ptext = $obj->getPostText();

        $mytpl = new \ilTemplate('tpl.booking_reservation_post.html', true, true, 'Modules/BookingManager/BookingProcess');
        $mytpl->setVariable("TITLE", $lng->txt('book_post_booking_information'));

        if ($ptext) {
            // placeholder
            $ptext = str_replace(
                ["[OBJECT]", "[PERIOD]"],
                [$obj->getTitle(), implode("<br />", $period)],
                $ptext
            );

            $mytpl->setVariable("POST_TEXT", nl2br($ptext));
        }

        if ($pfile) {
            $url = $ctrl->getLinkTarget($this->parent_gui, $file_deliver_cmd);

            $mytpl->setVariable("DOWNLOAD", $lng->txt('download'));
            $mytpl->setVariable("URL_FILE", $url);
            $mytpl->setVariable("TXT_FILE", $pfile);
        }

        $mytpl->setVariable("TXT_SUBMIT", $lng->txt('ok'));
        $mytpl->setVariable("URL_SUBMIT", $ctrl->getLinkTarget($this->parent_gui, "back"));

        $main_tpl->setContent($mytpl->get());
    }

    /**
     * Deliver post booking file
     */
    public function deliverPostFile(
        int $book_obj_id,
        int $user_id
    ) : void
    {
        $id = $book_obj_id;
        if (!$id) {
            return;
        }

        $book_ids = \ilBookingReservation::getObjectReservationForUser($id, $user_id);
        $book_id = current($book_ids);
        $obj = new \ilBookingReservation($book_id);
        if ($obj->getUserId() !== $user_id) {
            return;
        }

        $obj = new \ilBookingObject($id);
        $file = $obj->getPostFileFullPath();
        if ($file) {
            \ilFileDelivery::deliverFileLegacy($file, $obj->getPostFile());
        }
    }

}
