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

/**
 * Reservations screen
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingReservationsGUI
{
    protected array $raw_post_data;
    protected \ILIAS\BookingManager\StandardGUIRequest $book_request;
    protected ilBookingHelpAdapter $help;
    protected int $context_obj_id;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilTabsGUI $tabs_gui;
    protected ilObjUser $user;
    protected ilObjBookingPool $pool;
    protected int $ref_id;
    protected int $book_obj_id;
    protected int $pbooked_user;
    protected string $reservation_id;  // see BookingReservationDBRepo, obj_user_(slot)_context
    protected int $booked_user;

    public function __construct(
        ilObjBookingPool $pool,
        ilBookingHelpAdapter $help,
        int $context_obj_id = 0
    ) {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->pool = $pool;
        $this->ctrl = $DIC->ctrl();
        $this->ref_id = $pool->getRefId();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tabs_gui = $DIC->tabs();
        $this->help = $help;
        $this->user = $DIC->user();
        $this->book_request = $DIC->bookingManager()
                                  ->internal()
                                  ->gui()
                                  ->standardRequest();

        $this->book_obj_id = $this->book_request->getObjectId();

        $this->context_obj_id = $context_obj_id;

        // user who's reservation is being tackled (e.g. canceled)
        $this->booked_user = $this->book_request->getBookedUser();
        if ($this->booked_user === 0) {
            $this->booked_user = $DIC->user()->getId();
        }
        // we get this from the reservation screen
        $this->reservation_id = $this->book_request->getReservationId();

        $this->ctrl->saveParameter($this, ["object_id", "bkusr"]);

        if ($this->book_request->getObjectId() > 0 && ilBookingObject::lookupPoolId($this->book_request->getObjectId()) !== $this->pool->getId()) {
            throw new ilException("Booking Object ID does not match Booking Pool.");
        }

        $this->raw_post_data = $DIC->http()->request()->getParsedBody();
    }

    /**
     * Reservations IDs as currently provided from
     */
    protected function getLogReservationIds() : array
    {
        $mrsv = $this->book_request->getReservationIds();
        if (count($mrsv) > 0) {
            return $mrsv;
        }
        if ($this->reservation_id > 0) {
            return array($this->reservation_id);
        }
        return [];
    }

    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("log");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("log", "logDetails", "changeStatusObject", "rsvConfirmCancelUser", "rsvCancelUser",
                    "applyLogFilter", "resetLogFilter", "rsvConfirmCancel", "rsvCancel", "back", "rsvConfirmDelete", "rsvDelete"))) {
                    $this->$cmd();
                }
        }
    }

    protected function setHelpId(string $a_id) : void
    {
        $this->help->setHelpId($a_id);
    }

    /**
     *  List reservations
     */
    public function log() : void
    {
        $tpl = $this->tpl;
        $table = $this->getReservationsTable();
        $tpl->setContent($table->getHTML());
    }

    /**
     * Get reservationsTable
     */
    protected function getReservationsTable(
        ?string $reservation_id = null
    ) : ilBookingReservationsTableGUI {
        $show_all = ($this->checkPermissionBool('write') || $this->pool->hasPublicLog());

        $filter = null;
        if ($this->book_obj_id > 0) {
            $filter["object"] = $this->book_obj_id;
        }
        // coming from participants tab to cancel reservations.
        if ($this->book_request->getUserId() > 0) {
            $filter["user_id"] = $this->book_request->getUserId();
        }
        $context_filter = ($this->context_obj_id > 0)
            ? [$this->context_obj_id]
            : null;

        return new ilBookingReservationsTableGUI(
            $this,
            'log',
            $this->ref_id,
            $this->pool->getId(),
            $show_all,
            ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE),
            $filter,
            $reservation_id,
            $context_filter
        );
    }

    public function logDetails() : void
    {
        $tpl = $this->tpl;

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "log")
        );

        $table = $this->getReservationsTable($this->reservation_id);
        $tpl->setContent($table->getHTML());
    }

    /**
     * Change status of given reservations
     */
    public function changeStatusObject() : void
    {
        $rsv_ids = $this->book_request->getReservationIds();
        if (count($rsv_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->log();
        }

        if ($this->checkPermissionBool('write')) {
            ilBookingReservation::changeStatus(
                $rsv_ids,
                $this->book_request->getStatus()
            );
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'log');
    }

    public function applyLogFilter() : void
    {
        $table = $this->getReservationsTable();
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->log();
    }

    public function resetLogFilter() : void
    {
        $table = $this->getReservationsTable();
        $table->resetOffset();
        $table->resetFilter();
        $this->log();
    }

    protected function checkPermissionBool(string $a_perm) : bool
    {
        return $this->access->checkAccess($a_perm, "", $this->ref_id);
    }

    //
    // Cancelation reservations
    //

    /**
     * (C1) Confirmation screen for canceling booking without schedule from booking objects screen
     * or from participants screen, if only one object has been selected.
     *
     * If the process is started form the booking objects screen, the current user
     * is the owner of the reservation.
     *
     * From the participants screen the user id is provided as bkusr
     */
    public function rsvConfirmCancelUser() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $id = $this->book_obj_id;
        if (!$id) {
            return;
        }

        $this->setHelpId("cancel_booking");

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($ilCtrl->getFormAction($this));
        $conf->setHeaderText($lng->txt('book_confirm_cancel'));

        $type = new ilBookingObject($id);
        $conf->addItem('object_id', $id, $type->getTitle());
        $conf->setConfirm($lng->txt('book_set_cancel'), 'rsvCancelUser');
        $conf->setCancel($lng->txt('cancel'), 'back');

        $tpl->setContent($conf->getHTML());
    }

    /**
     * (C1.a) Confirmed (C1)
     */
    public function rsvCancelUser() : void
    {
        $lng = $this->lng;

        $id = $this->book_obj_id;
        $user_id = $this->booked_user;

        if (!$id || !$user_id) {
            return;
        }

        $ids = ilBookingReservation::getObjectReservationForUser($id, $user_id);
        $id = current($ids);
        $obj = new ilBookingReservation($id);
        if ($obj->getUserId() !== $user_id) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt('permission_denied'), true);
            $this->back();
        }

        $obj->setStatus(ilBookingReservation::STATUS_CANCELLED);
        $obj->update();

        $this->tpl->setOnScreenMessage('success', $lng->txt('settings_saved'), true);
        $this->back();
    }

    /**
     * Back to reservation list
     */
    protected function back() : void
    {
        $this->ctrl->redirect($this, "log");
    }

    /**
     * (C2) Confirmation screen for canceling booking from reservations screen (with and without schedule)
     */
    public function rsvConfirmCancel() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilUser = $this->user;

        $ids = $this->getLogReservationIds();
        if (count($ids) === 0) {
            $this->back();
        }

        $max = array();
        foreach ($ids as $idx => $id) {
            if (!is_numeric($id)) {
                [$obj_id, $user_id, $from, $to] = explode("_", $id);

                $valid_ids = array();
                foreach (ilBookingObject::getList($this->pool->getId()) as $item) {
                    $valid_ids[$item["booking_object_id"]] = $item["title"];
                }

                if (array_key_exists($obj_id, $valid_ids) && $from > time() && ($this->checkPermissionBool("write") || $user_id === $ilUser->getId())) {
                    $rsv_ids = ilBookingReservation::getCancelDetails($obj_id, $user_id, $from, $to);
                    if (!count($rsv_ids)) {
                        unset($ids[$idx]);
                    }
                    if (count($rsv_ids) > 1) {
                        $max[$id] = count($rsv_ids);
                        $ids[$idx] = $rsv_ids;
                    } else {
                        // only 1 in group?  treat as normal reservation
                        $ids[$idx] = array_shift($rsv_ids);
                    }
                } else {
                    unset($ids[$idx]);
                }
            }
        }

        if (!is_array($ids) || !count($ids)) {
            $this->ctrl->redirect($this, 'log');
        }

        // show form instead
        if (count($max) && max($max) > 1) {
            $this->rsvConfirmCancelAggregation($ids);
            return;
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTargetByClass("ilBookingReservationsGUI", "")
        );

        $this->setHelpId("cancel_booking");

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($ilCtrl->getFormAction($this, 'rsvCancel'));
        $conf->setHeaderText($lng->txt('book_confirm_cancel'));
        $conf->setConfirm($lng->txt('book_set_cancel'), 'rsvCancel');
        $conf->setCancel($lng->txt('cancel'), 'back');

        foreach ($ids as $id) {
            $rsv = new ilBookingReservation($id);
            $obj = new ilBookingObject($rsv->getObjectId());

            $details = $obj->getTitle();
            if ($this->pool->getScheduleType() !== ilObjBookingPool::TYPE_NO_SCHEDULE) {
                $details .= ", " . ilDatePresentation::formatPeriod(
                    new ilDateTime($rsv->getFrom(), IL_CAL_UNIX),
                    new ilDateTime($rsv->getTo() + 1, IL_CAL_UNIX)
                );
            }

            $conf->addItem('rsv_id[]', $id, $details);
        }

        $tpl->setContent($conf->getHTML());
    }


    /**
     * (C2.a) Cancel aggregated booking from reservations screen (with and without schedule)
     *        called in (C2)
     */
    public function rsvConfirmCancelAggregation(array $a_ids = null) : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "log")
        );

        $this->setHelpId("cancel_booking");

        // #13511
        $this->tpl->setOnScreenMessage('question', $lng->txt("book_confirm_cancel"));

        $form = $this->rsvConfirmCancelAggregationForm($a_ids);

        $tpl->setContent($form->getHTML());
    }

    /**
     * Form being used in (C2.a)
     */
    public function rsvConfirmCancelAggregationForm(
        array $a_ids
    ) : ilPropertyFormGUI {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "rsvCancel"));
        $form->setTitle($this->lng->txt("book_confirm_cancel_aggregation"));

        ilDatePresentation::setUseRelativeDates(false);

        foreach ($a_ids as $idx => $ids) {
            $first = $ids;
            if (is_array($ids)) {
                $first = array_shift($first);
            }

            $rsv = new ilBookingReservation($first);
            $obj = new ilBookingObject($rsv->getObjectId());

            $caption = $obj->getTitle() . ", " . ilDatePresentation::formatPeriod(
                new ilDateTime($rsv->getFrom(), IL_CAL_UNIX),
                new ilDateTime($rsv->getTo() + 1, IL_CAL_UNIX)
            );

            // #17869
            if (is_array($ids)) {
                $caption .= " (" . count($ids) . ")";
            }

            $item = new ilNumberInputGUI($caption, "rsv_id_" . $idx);
            $item->setRequired(true);
            $item->setMinValue(0);
            $item->setSize(4);
            $form->addItem($item);

            if (is_array($ids)) {
                $item->setMaxValue(count($ids));

                foreach ($ids as $id) {
                    $hidden = new ilHiddenInputGUI("rsv_aggr[" . $idx . "][]");
                    $hidden->setValue($id);
                    $form->addItem($hidden);
                }
            } else {
                $item->setMaxValue(1);

                $hidden = new ilHiddenInputGUI("rsv_aggr[" . $idx . "]");
                $hidden->setValue($ids);
                $form->addItem($hidden);
            }

            if ($this->book_request->getCancelNr($idx)) {
                $item->setValue($this->book_request->getCancelNr($idx));
            }
        }

        $form->addCommandButton("rsvCancel", $this->lng->txt("confirm"));
        $form->addCommandButton("log", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * (C2.b) Cancel reservations (coming from C2 or C2.a)
     */
    public function rsvCancel() : void
    {
        $ilUser = $this->user;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        // simple version of reservation id
        $ids = $this->book_request->getReservationIds();

        $rsv_aggr = $this->raw_post_data["rsv_aggr"] ?? null;
        // aggregated version: determine reservation ids
        if (!is_null($rsv_aggr)) {
            $form = $this->rsvConfirmCancelAggregationForm($rsv_aggr);
            if (!$form->checkInput()) {
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $lng->txt("back"),
                    $ilCtrl->getLinkTarget($this, "log")
                );

                $tpl->setContent($form->getHTML());
                return;
            }

            $ids = array();
            foreach ($rsv_aggr as $idx => $aggr_ids) {
                $max = $this->book_request->getCancelNr($idx);
                if ($max) {
                    if (!is_array($aggr_ids)) {
                        $ids[] = $aggr_ids;
                    } else {
                        $aggr_ids = array_slice($aggr_ids, 0, $max);
                        $ids = array_merge($ids, $aggr_ids);
                    }
                }
            }
        }

        // for all reservation ids -> set reservation status to cancelled (and remove calendar entry)
        if ($ids) {
            foreach ($ids as $id) {
                $res = new ilBookingReservation($id);

                // either write permission or own booking
                $cancel_allowed_per_read = ($this->checkPermissionBool("read") && ($res->getUserId() === $ilUser->getId()));
                $cancel_allowed_per_write = ($this->checkPermissionBool("write"));
                if (!$cancel_allowed_per_read && !$cancel_allowed_per_write) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
                    $this->ctrl->redirect($this, 'log');
                }

                $res->setStatus(ilBookingReservation::STATUS_CANCELLED);
                $res->update();

                if ($this->pool->getScheduleType() !== ilObjBookingPool::TYPE_NO_SCHEDULE) {
                    // remove user calendar entry (#11086)
                    $cal_entry_id = $res->getCalendarEntry();
                    if ($cal_entry_id) {
                        $entry = new ilCalendarEntry($cal_entry_id);
                        $entry->delete();
                    }
                }
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->log();
    }

    public function rsvConfirmDelete() : void
    {
        global $DIC;
        if (!$this->checkPermissionBool("write")) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirect($this, 'log');
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "log")
        );

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this, 'rsvDelete'));
        $conf->setHeaderText($this->lng->txt('book_confirm_delete'));
        $conf->setConfirm($this->lng->txt('book_set_delete'), 'rsvDelete');
        $conf->setCancel($this->lng->txt('cancel'), 'log');

        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            [$obj_id, $user_id, $from, $to] = explode("_", $DIC->http()->request()->getQueryParams()['reservation_id']);
            $ids = ilBookingReservation::getCancelDetails($obj_id, $user_id, $from, $to);
            $rsv_id = $ids[0];
        } else {
            $rsv_id = $DIC->http()->request()->getQueryParams()['reservation_id'];
            $ids = [$rsv_id];
        }
        $rsv = new ilBookingReservation($rsv_id);
        $obj = new ilBookingObject($rsv->getObjectId());

        $details = sprintf($this->lng->txt('X_reservations_of'), count($ids)) . ' ' . $obj->getTitle();
        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $details .= ", " . ilDatePresentation::formatPeriod(
                new ilDateTime($rsv->getFrom(), IL_CAL_UNIX),
                new ilDateTime($rsv->getTo() + 1, IL_CAL_UNIX)
            );
        }

        $conf->addItem('rsv_ids', implode(',', $ids), $details);
        $this->tpl->setContent($conf->getHTML());
    }

    public function rsvDelete() : void
    {
        global $DIC;
        $get = $DIC->http()->request()->getParsedBody()['rsv_ids'];
        if ($get) {
            foreach (explode(',', $get) as $id) {
                $res = new ilBookingReservation($id);
                $obj = new ilBookingObject($res->getObjectId());
                if (!$this->checkPermissionBool("write") || $obj->getPoolId() !== $this->pool->getId()) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
                    $this->ctrl->redirect($this, 'log');
                }
                if ($this->pool->getScheduleType() !== ilObjBookingPool::TYPE_NO_SCHEDULE) {
                    $cal_entry_id = $res->getCalendarEntry();
                    if ($cal_entry_id) {
                        $entry = new ilCalendarEntry($cal_entry_id);
                        $entry->delete();
                    }
                }
                $res->delete();
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('reservation_deleted'), true);
        $this->ctrl->redirect($this, 'log');
    }
}
