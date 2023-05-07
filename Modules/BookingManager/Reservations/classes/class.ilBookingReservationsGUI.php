<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Reservations screen
 *
 * @author killing@leifos.de
 * @ingroup ModulesBookingManager
 */
class ilBookingReservationsGUI
{
    /**
     * @var ilBookingHelpAdapter
     */
    protected $help;

    /**
     * @var int
     */
    protected $context_obj_id;

    /**
     * ilBookingReservationsGUI constructor.
     * @param ilObjBookingPool $pool
     * @param ilBookingHelpAdapter $help
     * @param int $context_obj_id filter ui for a context object (e.g. course)
     * @throws ilException
     */
    public function __construct(ilObjBookingPool $pool, ilBookingHelpAdapter $help, int $context_obj_id = 0)
    {
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
        $this->service = $DIC->bookingManager()->internal();
        $this->ui = $DIC->ui();
        $this->toolbar = $DIC->toolbar();

        $this->book_obj_id = (int) $_REQUEST['object_id'];

        $this->context_obj_id = $context_obj_id;

        // user who's reservation is being tackled (e.g. canceled)
        $this->booked_user = (int) $_REQUEST['bkusr'];
        if ($this->booked_user == 0) {
            $this->booked_user = $DIC->user()->getId();
        }
        // we get this from the reservation screen
        $this->reservation_id = ilUtil::stripSlashes($_GET["reservation_id"]);

        $this->ctrl->saveParameter($this, ["object_id", "bkusr"]);

        if ((int) $_REQUEST['object_id'] > 0 && ilBookingObject::lookupPoolId((int) $_REQUEST['object_id']) != $this->pool->getId()) {
            throw new ilException("Booking Object ID does not match Booking Pool.");
        }
    }

    /**
     * Reservations IDs as currently provided from
     * @return array
     */
    protected function getLogReservationIds()
    {
        if (is_array($_POST["mrsv"])) {
            return $_POST["mrsv"];
        } elseif ($this->reservation_id > 0) {
            return array($this->reservation_id);
        }
        return [];
    }


    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("log");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("log", "logDetails", "changeStatusObject", "rsvConfirmCancelUser", "rsvCancelUser",
                    "applyLogFilter", "resetLogFilter", "rsvConfirmCancel", "rsvCancel", "back", "rsvConfirmDelete", "rsvDelete", "confirmResetRun", "resetRun"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * @param string $a_id
     */
    protected function setHelpId(string $a_id)
    {
        $this->help->setHelpId($a_id);
    }

    /**
     *  List reservations
     */
    public function log()
    {
        $tpl = $this->tpl;
        $this->showRerunPreferenceAssignment();
        $table = $this->getReservationsTable();
        $tpl->setContent($table->getHTML());
    }

    /**
     * Get reservationsTable
     *
     * @param string $reservation_id
     * @return ilTableGUI
     */
    protected function getReservationsTable($reservation_id = null)
    {
        $show_all = ($this->checkPermissionBool('write') || $this->pool->hasPublicLog());

        $filter = null;
        if ($this->book_obj_id > 0) {
            $filter["object"] = $this->book_obj_id;
        }
        // coming from participants tab to cancel reservations.
        if ($_GET['user_id']) {
            $filter["user_id"] = (int) $_GET['user_id'];
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
            ($this->pool->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE),
            $filter,
            $reservation_id,
            $context_filter
        );
    }


    public function logDetails()
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
    public function changeStatusObject()
    {
        if (!$_POST['reservation_id']) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->log();
        }

        if ($this->checkPermissionBool('write')) {
            ilBookingReservation::changeStatus($_POST['reservation_id'], (int) $_POST['tstatus']);
        }

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'log');
    }

    /**
     * Apply filter from reservations table gui
     */
    public function applyLogFilter()
    {
        $table = $this->getReservationsTable();
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->log();
    }

    /**
     * Reset filter in reservations table gui
     */
    public function resetLogFilter()
    {
        $table = $this->getReservationsTable();
        $table->resetOffset();
        $table->resetFilter();
        $this->log();
    }

    /**
     * Check permission
     *
     * @param $a_perm
     * @return mixed
     */
    protected function checkPermissionBool($a_perm)
    {
        $ilAccess = $this->access;

        return $ilAccess->checkAccess($a_perm, "", $this->ref_id);
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
    public function rsvConfirmCancelUser()
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
    public function rsvCancelUser()
    {
        $lng = $this->lng;

        $id = $this->book_obj_id;
        $user_id = $this->booked_user;

        if (!$id || !$user_id) {
            return;
        }

        $id = ilBookingReservation::getObjectReservationForUser($id, $user_id);
        $obj = new ilBookingReservation($id);
        if ($obj->getUserId() != $user_id) {
            ilUtil::sendFailure($lng->txt('permission_denied'), true);
            $this->back();
        }

        $obj->setStatus(ilBookingReservation::STATUS_CANCELLED);
        $obj->update();

        ilUtil::sendSuccess($lng->txt('settings_saved'), true);
        $this->back();
    }

    /**
     * Back to reservation list
     */
    protected function back()
    {
        $this->ctrl->redirect($this, "log");
    }

    /**
     * (C2) Confirmation screen for canceling booking from reservations screen (with and without schedule)
     */
    public function rsvConfirmCancel()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilUser = $this->user;

        $ids = $this->getLogReservationIds();
        if (!is_array($ids) || !sizeof($ids)) {
            $this->back();
        }

        $max = array();
        foreach ($ids as $idx => $id) {
            if (!is_numeric($id)) {
                list($obj_id, $user_id, $from, $to) = explode("_", $id);

                $valid_ids = array();
                foreach (ilBookingObject::getList($this->pool->getId()) as $item) {
                    $valid_ids[$item["booking_object_id"]] = $item["title"];
                }

                if (($this->checkPermissionBool("write") || $user_id == $ilUser->getId()) &&
                    $from > time() &&
                    in_array($obj_id, array_keys($valid_ids))) {
                    $rsv_ids = ilBookingReservation::getCancelDetails($obj_id, $user_id, $from, $to);
                    if (!sizeof($rsv_ids)) {
                        unset($ids[$idx]);
                    }
                    if (sizeof($rsv_ids) > 1) {
                        $max[$id] = sizeof($rsv_ids);
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

        if (!is_array($ids) || !sizeof($ids)) {
            $this->ctrl->redirect($this, 'log');
        }

        // show form instead
        if (sizeof($max) && max($max) > 1) {
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
            if ($this->pool->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE) {
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
     * @param array|null $a_ids
     */
    public function rsvConfirmCancelAggregation(array $a_ids = null)
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
        ilUtil::sendQuestion($lng->txt("book_confirm_cancel"));

        $form = $this->rsvConfirmCancelAggregationForm($a_ids);

        $tpl->setContent($form->getHTML());
    }

    /**
     * Form being used in (C2.a)
     *
     * @param $a_ids
     * @return ilPropertyFormGUI
     */
    public function rsvConfirmCancelAggregationForm($a_ids)
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "rsvCancel"));
        $form->setTitle($this->lng->txt("book_confirm_cancel_aggregation"));

        ilDatePresentation::setUseRelativeDates(false);

        foreach ($a_ids as $idx => $ids) {
            if (is_array($ids)) {
                $first = $ids;
                $first = array_shift($first);
            } else {
                $first = $ids;
            }

            $rsv = new ilBookingReservation($first);
            $obj = new ilBookingObject($rsv->getObjectId());

            $caption = $obj->getTitle() . ", " . ilDatePresentation::formatPeriod(
                new ilDateTime($rsv->getFrom(), IL_CAL_UNIX),
                new ilDateTime($rsv->getTo() + 1, IL_CAL_UNIX)
            );

            // #17869
            if (is_array($ids)) {
                $caption .= " (" . sizeof($ids) . ")";
            }

            $item = new ilNumberInputGUI($caption, "rsv_id_" . $idx);
            $item->setRequired(true);
            $item->setMinValue(0);
            $item->setSize(4);
            $form->addItem($item);

            if (is_array($ids)) {
                $item->setMaxValue(sizeof($ids));

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

            if ($_POST["rsv_id_" . $idx]) {
                $item->setValue((int) $_POST["rsv_id_" . $idx]);
            }
        }

        $form->addCommandButton("rsvCancel", $this->lng->txt("confirm"));
        $form->addCommandButton("log", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * (C2.b) Cancel reservations (coming from C2 or C2.a)
     */
    public function rsvCancel()
    {
        $ilUser = $this->user;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        // simple version of reservation id
        $ids = $_POST["rsv_id"];

        // aggregated version: determine reservation ids
        if ($_POST["rsv_aggr"]) {
            $form = $this->rsvConfirmCancelAggregationForm($_POST["rsv_aggr"]);
            if (!$form->checkInput()) {
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $lng->txt("back"),
                    $ilCtrl->getLinkTarget($this, "log")
                );

                return $tpl->setContent($form->getHTML());
            }

            $ids = array();
            foreach ($_POST["rsv_aggr"] as $idx => $aggr_ids) {
                $max = (int) $_POST["rsv_id_" . $idx];
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
                $cancel_allowed_per_read = ($this->checkPermissionBool("read") && ($res->getUserId() == $ilUser->getId()));
                $cancel_allowed_per_write = ($this->checkPermissionBool("write"));
                if (!$cancel_allowed_per_read && !$cancel_allowed_per_write) {
                    ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
                    $this->ctrl->redirect($this, 'log');
                }

                $res->setStatus(ilBookingReservation::STATUS_CANCELLED);
                $res->update();

                if ($this->pool->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE) {
                    // remove user calendar entry (#11086)
                    $cal_entry_id = $res->getCalendarEntry();
                    if ($cal_entry_id) {
                        $entry = new ilCalendarEntry($cal_entry_id);
                        $entry->delete();
                    }
                }
            }
        }

        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->log();
        return "";
    }

    public function rsvConfirmDelete()
    {
        global $DIC;
        if (!$this->checkPermissionBool("write")) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirect($this, 'log');
        }

        $ids = $this->getLogReservationIds();
        if (!is_array($ids) || !sizeof($ids)) {
            $this->back();
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

        if ($this->pool->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            foreach ($ids as $idx => $id) {
                list($obj_id, $user_id, $from, $to) = explode("_", $id);
                $rsv_ids = ilBookingReservation::getCancelDetails($obj_id, $user_id, $from, $to);
                $rsv_id = $rsv_ids[0];

                $rsv = new ilBookingReservation($rsv_id);
                $obj = new ilBookingObject($rsv->getObjectId());

                $details = sprintf($this->lng->txt('X_reservations_of'), count($rsv_ids)) . ' ' . $obj->getTitle();
                $details .= ", " . ilDatePresentation::formatPeriod(
                    new ilDateTime($rsv->getFrom(), IL_CAL_UNIX),
                    new ilDateTime($rsv->getTo() + 1, IL_CAL_UNIX)
                );
                $conf->addItem('mrsv[]', $id, $details);
            }
        } else {
            foreach ($ids as $idx => $rsv_id) {
                $rsv = new ilBookingReservation($rsv_id);
                $obj = new ilBookingObject($rsv->getObjectId());
                $details = sprintf($this->lng->txt('X_reservations_of'), 1) . ' ' . $obj->getTitle();
                $conf->addItem('mrsv[]', $rsv_id, $details);
            }
        }
        $this->tpl->setContent($conf->getHTML());
    }

    public function rsvDelete()
    {
        global $DIC;
        $get = $DIC->http()->request()->getParsedBody()['mrsv'];
        if ($get) {
            foreach ($get as $id) {
                if ($this->pool->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE) {
                    list($obj_id, $user_id, $from, $to) = explode("_", $id);
                    $rsv_ids = ilBookingReservation::getCancelDetails($obj_id, $user_id, $from, $to);
                } else {
                    $rsv_ids = [$id];
                }
                foreach ($rsv_ids as $rsv_id) {
                    $res = new ilBookingReservation($rsv_id);
                    $obj = new ilBookingObject($res->getObjectId());
                    if ($obj->getPoolId() != $this->pool->getId() || !$this->checkPermissionBool("write")) {
                        ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
                        $this->ctrl->redirect($this, 'log');
                    }
                    if ($this->pool->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE) {
                        $cal_entry_id = $res->getCalendarEntry();
                        if ($cal_entry_id) {
                            include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
                            $entry = new ilCalendarEntry($cal_entry_id);
                            $entry->delete();
                        }
                    }
                    $res->delete();
                }
            }
        }
        ilUtil::sendSuccess($this->lng->txt('reservation_deleted'), true);
        $this->ctrl->redirect($this, 'log');
    }

    protected function showRerunPreferenceAssignment() : void
    {
        if (!$this->checkPermissionBool('write')) {
            return;
        }
        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES) {
            $pref_manager = $this->service->domain()->preferences($this->pool);
            if ($pref_manager->hasRun()) {
                $this->toolbar->addComponent($this->ui->factory()->button()->standard(
                    $this->lng->txt("book_rerun_assignments"),
                    $this->ctrl->getLinkTarget($this, "confirmResetRun")
                ));
            }
        }
    }

    protected function confirmResetRun()
    {
        if (!$this->checkPermissionBool('write')) {
            return;
        }
        $this->tabs_gui->activateTab("log");
        $mess = $this->ui->factory()->messageBox()->confirmation($this->lng->txt("book_rerun_confirmation"))->withButtons(
            [
                $this->ui->factory()->button()->standard(
                    $this->lng->txt("book_rerun_assignments"),
                    $this->ctrl->getLinkTarget($this, "resetRun")
                ),
                $this->ui->factory()->button()->standard(
                    $this->lng->txt("cancel"),
                    $this->ctrl->getLinkTarget($this, "log")
                )
            ]
        );
        $this->tpl->setContent(
            $this->ui->renderer()->render($mess)
        );
    }

    protected function resetRun()
    {
        if (!$this->checkPermissionBool('write')) {
            return;
        }
        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES
            && $this->access->checkAccess("write", "", $this->pool->getRefId())) {
            $pref_manager = $this->service->domain()->preferences($this->pool);
            $repo = $this->service->repo()->getPreferencesRepo();
            $pref_manager->resetRun();
            $pref_manager->storeBookings(
                $repo->getPreferences($this->pool->getId())
            );
        }
        $this->ctrl->redirect($this, "log");
    }

}
