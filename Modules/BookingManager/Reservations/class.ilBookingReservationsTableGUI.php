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

use ILIAS\BookingManager\Reservation\ReservationTableSessionRepository;

/**
 * List booking objects
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBookingReservationsTableGUI extends ilTable2GUI
{
    protected \ILIAS\BookingManager\InternalGUIService $gui;
    protected ilObjBookingPool $pool;
    protected \ILIAS\BookingManager\Schedule\ScheduleManager $schedule_manager;
    protected \ILIAS\BookingManager\Reservations\ReservationDBRepository $reservation_repo;
    protected ReservationTableSessionRepository $table_repo;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected int $ref_id;
    protected array $filter;
    protected int $pool_id;
    protected bool $show_all;
    protected bool $has_schedule;
    protected array $objects;
    protected ?int $group_id;
    protected array $advmd;
    protected bool $has_items_with_host_context = false;
    protected ilTree $tree;
    /** @var int[] ids of context objects (e.g. course ids) */
    protected ?array $context_obj_ids;
    protected ?ilAdvancedMDRecordGUI $record_gui = null;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_ref_id,
        ilObjBookingPool $pool,
        bool $a_show_all,
        array $a_filter_pre = null,
        int $a_group_id = null,
        array $context_obj_ids = null
    ) {
        global $DIC;

        $this->gui = $DIC->bookingManager()->internal()->gui();
        $this->pool = $pool;
        $a_pool_id = $pool->getId();
        $a_has_schedule = ($pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE);
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilUser = $DIC->user();
        $this->tree = $DIC->repositoryTree();
        $this->reservation_repo = $DIC->bookingManager()
            ->internal()
            ->repo()
            ->reservation();
        $this->schedule_manager = $DIC
            ->bookingManager()
            ->internal()
            ->domain()
            ->schedules($a_pool_id);

        $this->context_obj_ids = $context_obj_ids;
        $this->pool_id = $a_pool_id;
        $this->ref_id = $a_ref_id;
        $this->show_all = $a_show_all;
        $this->has_schedule = $a_has_schedule;
        $this->group_id = $a_group_id;

        $this->table_repo = $DIC->bookingManager()
            ->internal()
            ->repo()
            ->reservationTable();

        $this->advmd = ilObjBookingPool::getAdvancedMDFields($a_ref_id);

        $this->setId("bkrsv" . $a_ref_id);

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("book_reservations_list"));

        $this->addColumn("", "", 1);
        $this->addColumn($this->lng->txt("title"), "title");

        $selected = $this->getSelectedColumns();
        $cols = $this->getSelectableColumns();

        if ($this->has_schedule) {
            $this->lng->loadLanguageModule("dateplaner");

            $this->addColumn($this->lng->txt("date"), "date");
            if (in_array("week", $selected, true)) {
                $this->addColumn($this->lng->txt("wk_short"), "week");
                unset($cols["week"]);
            }
            if (in_array("weekday", $selected, true)) {
                $this->addColumn($this->lng->txt("cal_weekday"), "weekday");
                unset($cols["weekday"]);
            }
            $this->addColumn($this->lng->txt("book_schedule_slot"), "slot");
            $this->addColumn($this->lng->txt("book_no_of_objects"), "counter");

            $this->setDefaultOrderField("date");
        } else {
            $this->addColumn($this->lng->txt("status"), "status");

            $this->setDefaultOrderField("title");
        }
        if ($this->showMessages()) {
            $this->addColumn($this->lng->txt("book_message"));
        }

        $this->setDefaultOrderDirection("asc");

        // non-user columns
        $user_cols = $this->getSelectableUserColumns();
        foreach ($this->getSelectedColumns() as $col) {
            if (array_key_exists($col, $cols)) {
                if (!isset($user_cols[$col])) {
                    $this->addColumn($cols[$col]["txt"], $col);
                }
            }
        }



        $this->initFilter($a_filter_pre);
        if ($this->group_id) {
            $this->setLimit(9999);
            $this->disable("numinfo");
            $this->filters = array();
        } else {
            $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
        }
        $this->getItems($this->getCurrentFilter());

        if ($this->has_items_with_host_context) {
            $this->addColumn($this->lng->txt("book_booked_in"), "context_obj_title");
        }

        $this->addColumn($this->lng->txt("user"), "user_name");

        // user columns
        foreach ($this->getSelectedColumns() as $col) {
            if (array_key_exists($col, $cols)) {
                if (isset($user_cols[$col])) {
                    $this->addColumn($cols[$col]["txt"], $col);
                }
            }
        }

        $this->addColumn($this->lng->txt("actions"));

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.booking_reservation_row.html", "Modules/BookingManager/Reservations");
        $this->setResetCommand("resetLogFilter");
        $this->setFilterCommand("applyLogFilter");
        $this->setDisableFilterHiding(true);


        if ($ilUser->getId() !== ANONYMOUS_USER_ID) {
            $this->addMultiCommand('rsvConfirmCancel', $lng->txt('book_set_cancel'));
            if ($this->access->checkAccess('write', '', $this->ref_id)) {
                $this->addMultiCommand('rsvConfirmDelete', $lng->txt('delete'));
            }
            $this->setSelectAllCheckbox('mrsv');
        }


        ilDatePresentation::setUseRelativeDates(false);
    }

    protected function showMessages(): bool
    {
        return $this->pool->usesMessages() &&
            $this->access->checkAccess('write', '', $this->ref_id);
    }

    public function getSelectableColumns(): array
    {
        $cols = array();

        if ($this->has_schedule) {
            $this->lng->loadLanguageModule("dateplaner");

            $cols["week"] = array(
                "txt" => $this->lng->txt("wk_short"),
                "default" => true
            );

            $cols["weekday"] = array(
                "txt" => $this->lng->txt("cal_weekday"),
                "default" => true
            );
        }

        foreach ($this->advmd as $field) {
            $cols["advmd" . $field["id"]] = array(
                "txt" => $field["title"],
                "default" => false
            );
        }

        $cols = array_merge($cols, $this->getSelectableUserColumns());

        return $cols;
    }

    /**
     * Get selectable user fields
     */
    protected function getSelectableUserColumns(): array
    {
        $cols = [];
        // additional user fields
        if (($parent = $this->getParentGroupCourse()) !== null) {
            if ($this->access->checkAccess("manage_members", "", $parent["ref_id"])) {
                $ef = ilExportFieldsInfo::_getInstanceByType($parent["type"]);
                foreach ($ef->getSelectableFieldsInfo(ilObject::_lookupObjectId($parent["ref_id"])) as $k => $v) {
                    if ($k !== "login") {
                        $cols[$k] = $v;
                    }
                }
            }
        }
        return $cols;
    }

    protected function getSelectedUserColumns(): array
    {
        $user_cols = $this->getSelectableUserColumns();
        $sel = [];
        foreach ($this->getSelectedColumns() as $col) {
            if (isset($user_cols[$col])) {
                $sel[] = $col;
            }
        }
        return $sel;
    }

    protected function getParentGroupCourse(): ?array
    {
        $tree = $this->tree;
        if (($par_ref_id = $tree->checkForParentType($this->ref_id, "grp")) > 0) {
            return [
                "ref_id" => $par_ref_id,
                "type" => "grp"
            ];
        }
        if (($par_ref_id = $tree->checkForParentType($this->ref_id, "crs")) > 0) {
            return [
                "ref_id" => $par_ref_id,
                "type" => "crs"
            ];
        }
        return null;
    }

    public function initFilter(
        array $a_filter_pre = null
    ): void {
        if (is_array($a_filter_pre) &&
            isset($a_filter_pre["object"])) {
            $this->table_repo->setObjectFilter(
                $this->getId(),
                serialize($a_filter_pre["object"])
            );
        }

        $this->objects = array();
        foreach (ilBookingObject::getList($this->pool_id) as $item) {
            $this->objects[$item["booking_object_id"]] = $item["title"];
        }
        $item = $this->addFilterItemByMetaType("object", ilTable2GUI::FILTER_SELECT);
        if ($item !== null) {
            $item->setOptions(array("" => $this->lng->txt('book_all')) + $this->objects);
            $this->filter["object"] = $item->getValue();
            $title = $this->addFilterItemByMetaType(
                "title",
                ilTable2GUI::FILTER_TEXT,
                false,
                $this->lng->txt("object") . " " . $this->lng->txt("title") . "/" . $this->lng->txt("description")
            );
            if ($title !== null) {
                $this->filter["title"] = $title->getValue();
            }

            if ($this->has_schedule) {
                // default period: from:today [ to:(today + n days) ]
                if (!$this->table_repo->hasFromToFilter($this->getId())) {
                    $from = new ilDateTime(date("Y-m-d"), IL_CAL_DATE); // today
                    $to = null;

                    // add period end from pool settings?
                    $bpool = new ilObjBookingPool($this->pool_id, false);
                    $period = $bpool->getReservationFilterPeriod();
                    if ($period !== null) {
                        $to = clone $from;
                        if ($period) {
                            $to->increment(ilDateTime::DAY, $period);
                        }
                        $to = serialize($to);
                    }

                    $this->table_repo->setFromToFilter(
                        $this->getId(),
                        serialize(array(
                            "from" => serialize($from),
                            "to" => $to
                        ))
                    );
                }
                $item = $this->addFilterItemByMetaType("fromto", ilTable2GUI::FILTER_DATE_RANGE, false, $this->lng->txt('book_fromto'));
                $this->filter["fromto"] = $item->getDate();

                // only needed for full log
                if ($this->show_all) {
                    // see ilObjBookingPoolGUI::buildDatesBySchedule()
                    $map = array_flip(array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'));

                    $options = array("" => $this->lng->txt('book_all'));

                    // schedule to slot
                    foreach ($this->schedule_manager->getScheduleList() as $id => $title) {
                        $schedule = new ilBookingSchedule($id);
                        foreach ($schedule->getDefinition() as $day => $slots) {
                            $day_caption = ilCalendarUtil::_numericDayToString((int) $map[$day], false);

                            foreach ($slots as $slot) {
                                $idx = $map[$day] . "_" . $slot;
                                $options[$idx] = $day_caption . ", " . $slot;
                            }
                        }
                    }

                    ksort($options);

                    $item = $this->addFilterItemByMetaType("book_schedule_slot", ilTable2GUI::FILTER_SELECT);
                    $item->setOptions($options);
                    $this->filter["slot"] = $item->getValue();
                }
            }

            $item = new ilCheckboxInputGUI($this->lng->txt("book_filter_past_reservations"), "past");
            $this->addFilterItem($item);
            $item->readFromSession();

            // if period starts in the past we have to include past reservations
            // :TODO: to be discussed
            if (isset($this->filter["fromto"]["from"]) &&
                $this->filter["fromto"]["from"]->get(IL_CAL_DATE) < date("Y-m-d")) {
                $item->setChecked(true);
            }

            $this->filter["past"] = $item->getChecked();

            // status
            $valid_status = array(-ilBookingReservation::STATUS_CANCELLED,
                ilBookingReservation::STATUS_CANCELLED);
            if (!$this->has_schedule) {
                $options = array("" => $this->lng->txt('book_all'));
            } else {
                $options = array();
            }
            foreach ($valid_status as $loop) {
                if ($loop > 0) {
                    $options[$loop] = $this->lng->txt('book_reservation_status_' . $loop);
                } else {
                    $options[$loop] = $this->lng->txt('book_not') . ' ' . $this->lng->txt('book_reservation_status_' . -$loop);
                }
            }
            $item = $this->addFilterItemByMetaType("status", ilTable2GUI::FILTER_SELECT);
            $item->setOptions($options);
            $this->filter["status"] = $item->getValue();

            // only needed for full log
            if ($this->show_all) {
                $options = array("" => $this->lng->txt('book_all')) +
                    ilBookingReservation::getUserFilter(array_keys($this->objects));
                $item = $this->addFilterItemByMetaType("user", ilTable2GUI::FILTER_SELECT);
                $item->setOptions($options);
                if (is_array($a_filter_pre) && isset($a_filter_pre["user_id"])) {
                    $item->setValue($a_filter_pre["user_id"]);
                    $this->filter["user_id"] = $a_filter_pre["user_id"];
                } else {
                    $this->filter["user_id"] = $item->getValue();
                }
            }
        }
    }

    /**
     * Get current filter settings
     */
    public function getCurrentFilter(): array
    {
        $filter = array();
        if ($this->filter["object"]) {
            $filter["object"] = $this->filter["object"];
        }
        if ($this->filter["title"]) {
            $filter["title"] = $this->filter["title"];
        }
        if ($this->filter["status"]) {
            $filter["status"] = $this->filter["status"];
        }
        if (isset($this->filter["user_id"])) {
            $filter["user_id"] = $this->filter["user_id"];
        }
        if (!is_null($this->context_obj_ids)) {
            $filter["context_obj_ids"] = $this->context_obj_ids;
        }

        if ($this->has_schedule) {
            if (!isset($filter["status"])) {
                // needs distinct status because of aggregation
                $filter["status"] = -ilBookingReservation::STATUS_CANCELLED;
            }
            if (isset($this->filter["slot"])) {
                $filter["slot"] = $this->filter["slot"];
            }

            if ($this->filter["fromto"]["from"] || $this->filter["fromto"]["to"]) {
                if ($this->filter["fromto"]["from"]) {
                    $filter["from"] = $this->filter["fromto"]["from"]->get(IL_CAL_UNIX);
                }
                if ($this->filter["fromto"]["to"]) {
                    $day_end = new ilDateTime($this->filter["fromto"]["to"]->get(IL_CAL_DATE) . " 23:59:59", IL_CAL_DATETIME);
                    $filter["to"] = $day_end->get(IL_CAL_UNIX);
                }
            }

            $filter["past"] = (bool) $this->filter["past"];
        }
        return $filter;
    }

    public function numericOrdering(string $a_field): bool
    {
        return in_array($a_field, array("counter", "date", "week", "weekday"));
    }

    /**
     * Gather data and build rows
     */
    public function getItems(array $filter): void
    {
        $ilUser = $this->user;

        $this->has_items_with_host_context = false;

        if (!isset($filter["object"])) {
            $ids = array_keys($this->objects);
        } else {
            $ids = array($filter["object"]);
        }

        if (!$this->show_all) {
            $filter["user_id"] = $ilUser->getId();
        }

        $repo = $this->reservation_repo;
        $data = $repo->getListByDate($this->has_schedule, $ids, $filter);

        if ($this->advmd) {
            // advanced metadata
            $this->record_gui = new ilAdvancedMDRecordGUI(
                ilAdvancedMDRecordGUI::MODE_FILTER,
                "book",
                $this->pool_id,
                "bobj"
            );
            $this->record_gui->setTableGUI($this);
            $this->record_gui->parse();

            foreach (array_keys($data) as $idx) {
                $data[$idx]["pool_id"] = $this->pool_id;
            }

            $data = ilAdvancedMDValues::queryForRecords(
                $this->ref_id,
                "book",
                "bobj",
                [$this->pool_id],
                "bobj",
                $data,
                "pool_id",
                "object_id",
                $this->record_gui->getFilterElements()
            );
        }

        if (count($this->getSelectedUserColumns()) > 0) {
            // get additional user data
            $user_ids = array_unique(array_map(static function ($d) {
                return $d['user_id'];
            }, $data));

            $user_columns = [];
            $odf_ids = [];
            foreach ($this->getSelectedUserColumns() as $field) {
                if (strpos($field, 'odf') === 0) {
                    $odf_ids[] = substr($field, 4);
                } else {
                    $user_columns[] = $field;
                }
            }

            // see ilCourseParticipantsTableGUI
            $user_columns = array_diff(
                $user_columns,
                ['consultation_hour', 'prtf', 'roles', 'org_units']
            );

            // user data fields
            $query = new ilUserQuery();
            $query->setLimit(9999);
            $query->setAdditionalFields($user_columns);
            $query->setUserFilter($user_ids);
            $ud = $query->query();
            $usr_data = [];
            foreach ($ud["set"] as $v) {
                foreach ($user_columns as $c) {
                    $usr_data[$v["usr_id"]][$c] = $v[$c];
                }
            }
            foreach ($data as $key => $v) {
                if (isset($usr_data[$v["user_id"]])) {
                    $data[$key] = array_merge($v, $usr_data[$v["user_id"]]);
                }
            }

            // object specific user data fields of parent course or group
            if ($odf_ids) {
                $parent = $this->getParentGroupCourse();
                $parent_obj_id = ilObject::_lookupObjectId($parent['ref_id']);
                $parent_obj_type = ilObject::_lookupType($parent_obj_id);

                $confirmation_required = ($parent_obj_type === 'crs')
                    ? ilPrivacySettings::getInstance()->courseConfirmationRequired()
                    : ilPrivacySettings::getInstance()->groupConfirmationRequired();
                if ($confirmation_required) {
                    $user_ids = array_diff($user_ids, ilMemberAgreement::lookupAcceptedAgreements($parent_obj_id));
                }
                $odf_data = ilCourseUserData::_getValuesByObjId($parent_obj_id);

                $usr_data = [];
                foreach ($odf_data as $usr_id => $fields) {
                    if (in_array($usr_id, $user_ids, true)) {
                        foreach ($fields as $field_id => $value) {
                            if (in_array($field_id, $odf_ids, true)) {
                                $usr_data[$usr_id]['odf_' . $field_id] = $value;
                            }
                        }
                    }
                }

                foreach ($data as $key => $v) {
                    if (isset($usr_data[$v["user_id"]])) {
                        $data[$key] = array_merge($v, $usr_data[$v["user_id"]]);
                    }
                }
            }
        }

        foreach ($data as $k => $d) {
            if ($d["context_obj_id"] > 0) {
                $this->has_items_with_host_context = true;
                $data[$k]["context_obj_title"] = ilObject::_lookupTitle($d["context_obj_id"]);
            }
        }

        $this->setData($data);
    }

    public function getAdvMDRecordGUI(): ?ilAdvancedMDRecordGUI
    {
        return $this->record_gui;
    }

    public function getOrderField(): string
    {
        $field = parent::getOrderField();

        // #16560 - this will enable matchting slot sorting to date/week
        if (in_array($field, array("date", "week"))) {
            $field = "_sortdate";
        }

        return $field;
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $f = $this->gui->ui()->factory();

        $selected = $this->getSelectedColumns();

        $dd_items = [];

        if ($this->has_items_with_host_context) {
            $this->tpl->setCurrentBlock("context");
            $this->tpl->setVariable("VALUE_CONTEXT_TITLE", ($a_set["context_obj_title"] ?? "") . " ");
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);

        $can_be_cancelled = (($ilAccess->checkAccess('write', '', $this->ref_id) ||
            $a_set['user_id'] == $ilUser->getId()) &&
            $a_set["can_be_cancelled"]);

        if ($can_be_cancelled) {
            $this->tpl->setVariable("MULTI_ID", $a_set["booking_reservation_id"]);
        }

        // #11995
        $uname = $a_set["user_name"];
        if (!trim($uname)) {
            $uname = "[" . $lng->txt("user_deleted") . "]";
        } else {
            $uname = ilUserUtil::getNamePresentation($a_set['user_id'], false, true, "", true);
        }
        $this->tpl->setVariable("TXT_CURRENT_USER", $uname);

        if ($this->has_schedule) {
            $this->tpl->setVariable("VALUE_DATE", ilDatePresentation::formatDate(new ilDate($a_set["date"], IL_CAL_DATE)));
            if (in_array("week", $selected, true)) {
                $this->tpl->setVariable("VALUE_WEEK", $a_set["week"]);
            }
            if (in_array("weekday", $selected, true)) {
                $this->tpl->setVariable("VALUE_WEEKDAY", ilCalendarUtil::_numericDayToString((int) $a_set["weekday"], false));
            }
            $this->tpl->setVariable("VALUE_SLOT", $a_set["slot"]);
            $this->tpl->setVariable("VALUE_COUNTER", $a_set["counter"]);
        } elseif (in_array(
            $a_set['status'],
            array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE)
        )) {
            $this->tpl->setVariable("TXT_STATUS", $lng->txt('book_reservation_status_' . $a_set['status']));
        } else {
            $this->tpl->setVariable("TXT_STATUS", "&nbsp;");
        }
        if ($this->showMessages()) {
            $this->tpl->setCurrentBlock("message");
            $this->tpl->setVariable("MESSAGE", ilStr::shortenTextExtended($a_set["message"] . " ", 20, true));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->advmd) {
            foreach ($this->advmd as $item) {
                $advmd_id = (int) $item["id"];

                if (!in_array("advmd" . $advmd_id, $selected, true)) {
                    continue;
                }

                $val = " ";
                $key = "md_" . $advmd_id . "_presentation";
                if (isset($a_set[$key])) {
                    $pb = $a_set[$key]->getList();
                    if ($pb) {
                        $val = $pb;
                    }
                }

                $this->tpl->setCurrentBlock("advmd_bl");
                $this->tpl->setVariable("VALUE_ADVMD", $val);
                $this->tpl->parseCurrentBlock();
            }
        }

        // additional user fields
        $user_cols = $this->getSelectableUserColumns();
        foreach ($this->getSelectedColumns() as $col) {
            if (isset($user_cols[$col])) {
                $this->tpl->setCurrentBlock("user_col");
                $this->tpl->setVariable("VALUE_USER_COL", $a_set[$col] . " ");
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($can_be_cancelled) {
            $ilCtrl->setParameter($this->parent_obj, 'reservation_id', $a_set['booking_reservation_id']);
            $dd_items[] = $f->button()->shy(
                $lng->txt('book_set_cancel'),
                $ilCtrl->getLinkTarget($this->parent_obj, 'rsvConfirmCancel')
            );
            $ilCtrl->setParameter($this->parent_obj, 'reservation_id', "");
        }


        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilCtrl->setParameter($this->parent_obj, 'reservation_id', $a_set['booking_reservation_id']);
            $dd_items[] = $f->button()->shy(
                $lng->txt('delete'),
                $ilCtrl->getLinkTarget($this->parent_obj, 'rsvConfirmDelete')
            );
            $ilCtrl->setParameter($this->parent_obj, 'reservation_id', "");
        }

        $render_items = [];
        if ($this->showMessages() && $a_set["message"] !== "") {
            $c = $this->gui->modal(
                $this->lng->txt("book_message"),
                $this->lng->txt("close")
            )
                ->legacy(nl2br($a_set["message"]))
                ->getTriggerButtonComponents(
                    $this->lng->txt("book_show_message"),
                    true
                );
            $dd_items[] = $c["button"];
            $render_items[] = $c["modal"];
        }

        if (count($dd_items) > 0) {
            $render_items[] = $f->dropdown()->standard($dd_items);
            $this->tpl->setVariable("ACTIONS", $this->gui->ui()->renderer()->render($render_items));
        }
    }

    protected function getAdditionalExportCols(): array
    {
        $add_cols = [];
        $cols = $this->getSelectableColumns();

        unset($cols["week"], $cols["weekday"]);

        // non-user columns
        $user_cols = $this->getSelectableUserColumns();
        foreach ($this->getSelectedColumns() as $col) {
            if (array_key_exists($col, $cols)) {
                if (!isset($user_cols[$col])) {
                    $add_cols[$col] = $cols[$col]["txt"];
                }
            }
        }

        $add_cols["user_name"] = $this->lng->txt("user");
        $add_cols["login"] = $this->lng->txt("login");

        // user columns
        foreach ($this->getSelectedColumns() as $col) {
            if (array_key_exists($col, $cols)) {
                if (isset($user_cols[$col])) {
                    $add_cols[$col] = $cols[$col]["txt"];
                }
            }
        }

        return $add_cols;
    }

    protected function fillHeaderExcel(
        ilExcel $a_excel,
        int &$a_row
    ): void {
        $a_excel->setCell($a_row, 0, $this->lng->txt("title"));
        $col = 0;
        if ($this->has_schedule) {
            $a_excel->setCell($a_row, ++$col, $this->lng->txt("date"));
            $a_excel->setCell($a_row, ++$col, $this->lng->txt("wk_short"));
            $a_excel->setCell($a_row, ++$col, $this->lng->txt("cal_weekday"));
            $a_excel->setCell($a_row, ++$col, $this->lng->txt("book_schedule_slot"));
            $a_excel->setCell($a_row, ++$col, $this->lng->txt("book_no_of_objects"));
        } else {
            $a_excel->setCell($a_row, ++$col, $this->lng->txt("status"));
        }
        if ($this->showMessages()) {
            $a_excel->setCell($a_row, ++$col, $this->lng->txt("book_message"));
        }


        foreach ($this->getAdditionalExportCols() as $txt) {
            $a_excel->setCell($a_row, ++$col, $txt);
        }

        $a_excel->setBold("A" . $a_row . ":" . $a_excel->getColumnCoord($col) . $a_row);
    }

    protected function fillRowExcel(
        ilExcel $a_excel,
        int &$a_row,
        array $a_set
    ): void {
        $a_excel->setCell($a_row, 0, $a_set["title"]);
        $col = 0;
        if ($this->has_schedule) {
            $a_excel->setCell($a_row, ++$col, new ilDate($a_set["date"], IL_CAL_DATE));
            $a_excel->setCell($a_row, ++$col, $a_set["week"]);
            $a_excel->setCell($a_row, ++$col, ilCalendarUtil::_numericDayToString((int) $a_set["weekday"], false));
            $a_excel->setCell($a_row, ++$col, $a_set["slot"]);
            $a_excel->setCell($a_row, ++$col, $a_set["counter"]);
        } else {
            $status = "";
            if (in_array($a_set['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE))) {
                $status = $this->lng->txt('book_reservation_status_' . $a_set['status']);
            }
            $a_excel->setCell($a_row, ++$col, $status);
        }
        if ($this->showMessages()) {
            $a_excel->setCell($a_row, ++$col, $a_set["message"]);
        }

        foreach ($this->getAdditionalExportCols() as $colid => $txt) {
            if (str_starts_with($colid, "advmd")) {
                $val = " ";
                $key = "md_" . (int) substr($colid, 5) . "_presentation";
                if (isset($a_set[$key])) {
                    $pb = $a_set[$key]->getList();
                    if ($pb) {
                        $val = $pb;
                    }
                }
                $a_excel->setCell($a_row, ++$col, $val);
            } else {
                $a_excel->setCell($a_row, ++$col, $a_set[$colid]);
            }
        }
    }

    protected function fillHeaderCSV(
        ilCSVWriter $a_csv
    ): void {
        $a_csv->addColumn($this->lng->txt("title"));
        if ($this->has_schedule) {
            $a_csv->addColumn($this->lng->txt("date"));
            $a_csv->addColumn($this->lng->txt("wk_short"));
            $a_csv->addColumn($this->lng->txt("cal_weekday"));
            $a_csv->addColumn($this->lng->txt("book_schedule_slot"));
            $a_csv->addColumn($this->lng->txt("book_no_of_objects"));
        } else {
            $a_csv->addColumn($this->lng->txt("status"));
        }
        if ($this->showMessages()) {
            $a_csv->addColumn($this->lng->txt("book_message"));
        }

        foreach ($this->getAdditionalExportCols() as $txt) {
            $a_csv->addColumn($txt);
        }

        $a_csv->addRow();
    }

    protected function fillRowCSV(
        ilCSVWriter $a_csv,
        array $a_set
    ): void {
        $a_csv->addColumn($a_set["title"]);
        if ($this->has_schedule) {
            $a_csv->addColumn(ilDatePresentation::formatDate(new ilDate($a_set["date"], IL_CAL_DATE)));
            $a_csv->addColumn($a_set["week"]);
            $a_csv->addColumn(ilCalendarUtil::_numericDayToString((int) $a_set["weekday"], false));
            $a_csv->addColumn($a_set["slot"]);
            $a_csv->addColumn($a_set["counter"]);
        } else {
            $status = "";
            if (in_array($a_set['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE))) {
                $status = $this->lng->txt('book_reservation_status_' . $a_set['status']);
            }
            $a_csv->addColumn($status);
        }
        if ($this->showMessages()) {
            $a_csv->addColumn($a_set["message"]);
        }

        foreach ($this->getAdditionalExportCols() as $colid => $txt) {
            if (str_starts_with($colid, "advmd")) {
                $val = " ";
                $key = "md_" . (int) substr($colid, 5) . "_presentation";
                if (isset($a_set[$key])) {
                    $pb = $a_set[$key]->getList();
                    if ($pb) {
                        $val = $pb;
                    }
                }
                $a_csv->addColumn($val);
            } else {
                $a_csv->addColumn($a_set[$colid]);
            }
        }

        $a_csv->addRow();
    }
}
