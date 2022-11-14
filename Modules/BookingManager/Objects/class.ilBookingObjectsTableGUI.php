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
 * List booking objects (for booking type)
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBookingObjectsTableGUI extends ilTable2GUI
{
    protected string $process_class;
    protected \ILIAS\UI\Renderer $ui_renderer;
    protected \ILIAS\UI\Factory $ui_factory;
    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected int $ref_id;
    protected int $pool_id;
    protected bool $has_schedule;
    protected bool $may_edit;
    protected bool $may_assign;
    protected ?int $overall_limit = null;
    protected array $reservations = array();
    protected int $current_bookings;
    protected array $advmd;
    protected array $filter;
    protected ?ilAdvancedMDRecordGUI $record_gui = null;
    protected bool $active_management;
    protected \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_ref_id,
        int $a_pool_id,
        bool $a_pool_has_schedule,
        ?int $a_pool_overall_limit,
        bool $active_management = true
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->ref_id = $a_ref_id;
        $this->pool_id = $a_pool_id;
        $this->has_schedule = $a_pool_has_schedule;
        $this->overall_limit = $a_pool_overall_limit;
        $this->active_management = $active_management;
        $this->may_edit = ($this->active_management &&
            $ilAccess->checkAccess('write', '', $this->ref_id));
        $this->may_assign = ($this->active_management &&
            $ilAccess->checkAccess('write', '', $this->ref_id));

        $this->advmd = ilObjBookingPool::getAdvancedMDFields($this->ref_id);

        $this->setId("bkobj");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("book_booking_objects"));

        // $this->setLimit(9999);

        $this->addColumn($this->lng->txt("title"), "title");

        $cols = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col) {
            $this->addColumn($cols[$col]["txt"], $col);
        }

        if (!$this->has_schedule) {
            $this->addColumn($this->lng->txt("available"));
        }

        $this->addColumn($this->lng->txt("actions"));

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.booking_object_row.html", "Modules/BookingManager");
        $this->process_class = $DIC->bookingManager()
            ->internal()
            ->gui()
            ->process()
            ->getProcessClass($a_pool_has_schedule);
        
        $this->initFilter();
        $this->getItems();
    }

    /**
     * needed for advmd filter handling
     */
    protected function getAdvMDRecordGUI(): ?ilAdvancedMDRecordGUI
    {
        // #16827
        return $this->record_gui;
    }

    public function initFilter(): void
    {
        $lng = $this->lng;

        // title/description
        $title = $this->addFilterItemByMetaType(
            "title",
            ilTable2GUI::FILTER_TEXT,
            false,
            $lng->txt("title") . "/" . $lng->txt("description")
        );
        if ($title !== null) {
            $this->filter["title"] = $title->getValue();
        }

        // #18651
        if ($this->has_schedule) {
            // booking period
            $period = $this->addFilterItemByMetaType(
                "period",
                ilTable2GUI::FILTER_DATE_RANGE,
                false,
                $lng->txt("book_period")
            );
            if ($period !== null) {
                $this->filter["period"] = $period->getValue();
            }
        }
    }

    public function getItems(): void
    {
        $ilUser = $this->user;

        $data = ilBookingObject::getList($this->pool_id, $this->filter["title"]);


        // check schedule availability
        if ($this->has_schedule) {
            $now = time();
            $limit = strtotime("+1year");
            foreach ($data as $idx => $item) {
                $schedule = new ilBookingSchedule($item["schedule_id"]);
                $av_from = ($schedule->getAvailabilityFrom() && !$schedule->getAvailabilityFrom()->isNull())
                    ? $schedule->getAvailabilityFrom()->get(IL_CAL_UNIX)
                    : null;
                $av_to = ($schedule->getAvailabilityTo() && !$schedule->getAvailabilityTo()->isNull())
                    ? strtotime($schedule->getAvailabilityTo()->get(IL_CAL_DATE) . " 23:59:59")
                    : null;
                if (($av_from && $av_from > $limit)) {
                    unset($data[$idx]);
                }
                if ($av_from > $now) {
                    $data[$idx]["not_yet"] = ilDatePresentation::formatDate(new ilDate($av_from, IL_CAL_UNIX));
                }
                if ($av_to) {
                    // #18658
                    if (!ilBookingReservation::isObjectAvailableInPeriod($item["booking_object_id"], $schedule, $av_from, $av_to)) {
                        $this->lng->loadLanguageModule("dateplaner");
                        $data[$idx]["full_up"] = $this->lng->txt("cal_booked_out");
                    }
                }
            }
        }

        foreach ($data as $idx => $item) {
            $item_id = $item["booking_object_id"];

            // available for given period?
            if (isset($this->filter["period"]["from"]) ||
                isset($this->filter["period"]["to"])) {
                $from = is_object($this->filter["period"]["from"])
                    ? strtotime($this->filter["period"]["from"]->get(IL_CAL_DATE) . " 00:00:00")
                    : null;
                $to = is_object($this->filter["period"]["to"])
                    ? strtotime($this->filter["period"]["to"]->get(IL_CAL_DATE) . " 23:59:59")
                    : null;

                $bobj = new ilBookingObject($item_id);
                $schedule = new ilBookingSchedule($bobj->getScheduleId());

                if (!ilBookingReservation::isObjectAvailableInPeriod($item_id, $schedule, $from, $to)) {
                    unset($data[$idx]);
                    continue;
                }
            }

            // cache reservations
            $item_rsv = ilBookingReservation::getList(array($item_id), 1000, 0, array());
            $this->reservations[$item_id] = $item_rsv["data"];
        }

        if (!$this->has_schedule &&
            $this->overall_limit) {
            $this->current_bookings = 0;
            foreach ($this->reservations as $obj_rsv) {
                foreach ($obj_rsv as $item) {
                    if ($item["status"] != ilBookingReservation::STATUS_CANCELLED) {
                        if ($item["user_id"] == $ilUser->getId()) {
                            $this->current_bookings++;
                        }
                    }
                }
            }

            if ($this->current_bookings >= $this->overall_limit) {
                $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("book_overall_limit_warning"));
            }
        }

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

            $data = ilAdvancedMDValues::queryForRecords(
                $this->ref_id,
                "book",
                "bobj",
                [$this->pool_id],
                "bobj",
                $data,
                "pool_id",
                "booking_object_id",
                $this->record_gui->getFilterElements()
            );
        }

        $this->setMaxCount(count($data));
        $this->setData($data);
    }

    public function numericOrdering(string $a_field): bool
    {
        if (str_starts_with($a_field, "md_")) {
            $md_id = (int) substr($a_field, 3);
            if ($this->advmd[$md_id]["type"] === ilAdvancedMDFieldDefinition::TYPE_DATE) {
                return true;
            }
        }
        return false;
    }

    public function getSelectableColumns(): array
    {
        $cols = array();

        $cols["description"] = array(
            "txt" => $this->lng->txt("description"),
            "default" => true
        );

        foreach ($this->advmd as $field) {
            $cols["advmd" . $field["id"]] = array(
                "txt" => $field["title"],
                "default" => false
            );
        }

        return $cols;
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $has_booking = false;
        $booking_possible = true;
        $assign_possible = true;
        $has_reservations = false;

        $selected = $this->getSelectedColumns();

        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);

        if (in_array("description", $selected, true)) {
            $this->tpl->setVariable("TXT_DESC", nl2br($a_set["description"]));
        }

        if (isset($a_set["full_up"])) {
            $this->tpl->setVariable("NOT_YET", $a_set["full_up"]);
            $booking_possible = false;
            $assign_possible = false;
        } elseif (isset($a_set["not_yet"])) {
            $this->tpl->setVariable("NOT_YET", $a_set["not_yet"]);
        }

        if (!$this->has_schedule) {
            $cnt = 0;
            foreach ($this->reservations[$a_set["booking_object_id"]] as $item) {
                if ($item["status"] != ilBookingReservation::STATUS_CANCELLED) {
                    $cnt++;

                    if ($item["user_id"] == $ilUser->getId()) {
                        $has_booking = true;
                    }

                    $has_reservations = true;
                }
            }

            $this->tpl->setVariable("VALUE_AVAIL", $a_set["nr_items"] - $cnt);
            $this->tpl->setVariable("VALUE_AVAIL_ALL", $a_set["nr_items"]);

            if ($a_set["nr_items"] <= $cnt || ($this->overall_limit && $this->current_bookings && $this->current_bookings >= $this->overall_limit)) {
                $booking_possible = false;
            }
            if ($has_booking) {
                $booking_possible = false;
            }
            if ($a_set["nr_items"] <= $cnt) {
                $assign_possible = false;
            }
        } elseif (!$this->may_edit) {
            foreach ($this->reservations[$a_set["booking_object_id"]] as $item) {
                if ($item["status"] != ilBookingReservation::STATUS_CANCELLED &&
                    $item["user_id"] == $ilUser->getId()) {
                    $has_booking = true;
                }
            }
        }

        //Actions
        $items = array();

        $ilCtrl->setParameter($this->parent_obj, 'object_id', $a_set['booking_object_id']);

        if ($booking_possible) {
            if (isset($this->filter['period']['from'])) {
                $ilCtrl->setParameter($this->parent_obj, 'sseed', $this->filter['period']['from']->get(IL_CAL_DATE));
            }

            $items[] = $this->ui_factory->button()->shy(
                $lng->txt('book_book'),
                $ilCtrl->getLinkTargetByClass($this->process_class, 'book')
            );

            $ilCtrl->setParameter($this->parent_obj, 'sseed', '');
        }

        // #16663
        if (!$this->has_schedule && $has_booking) {
            if ($a_set['post_file'] || trim($a_set['post_text'])) {
                $items[] = $this->ui_factory->button()->shy(
                    $lng->txt('book_post_booking_information'),
                    $ilCtrl->getLinkTargetByClass($this->process_class, 'displayPostInfo')
                );
            }
            $ilCtrl->setParameterByClass("ilbookingreservationsgui", 'object_id', $a_set['booking_object_id']);
            $items[] = $this->ui_factory->button()->shy($lng->txt('book_set_cancel'), $ilCtrl->getLinkTargetByClass("ilbookingreservationsgui", 'rsvConfirmCancelUser'));
            $ilCtrl->setParameterByClass("ilbookingreservationsgui", 'object_id', "");
        }

        if ($this->may_edit || $has_booking) {
            $ilCtrl->setParameterByClass('ilBookingReservationsGUI', 'object_id', $a_set['booking_object_id']);
            $items[] = $this->ui_factory->button()->shy(
                $lng->txt('book_log'),
                $ilCtrl->getLinkTargetByClass('ilBookingReservationsGUI', 'log')
            );
            $ilCtrl->setParameterByClass('ilBookingReservationsGUI', 'object_id', '');
        }

        if ($this->may_assign && $assign_possible) {
            // note: this call is currently super expensive
            // see #26388, it has been performed even for users without edit permissions before
            // now the call has been moved here, but still this needs improvement
            // EDIT: deactivated for now due to performance reasons
            //if (!empty(ilBookingParticipant::getAssignableParticipants($a_set["booking_object_id"]))) {
            if (isset($this->filter['period']['from'])) {
                $ilCtrl->setParameterByClass(
                    $this->process_class,
                    'sseed',
                    $this->filter['period']['from']->get(IL_CAL_DATE)
                );
            }

            $items[] = $this->ui_factory->button()->shy(
                $lng->txt('book_assign_participant'),
                $ilCtrl->getLinkTargetByClass($this->process_class, 'assignParticipants')
            );

            $ilCtrl->setParameterByClass($this->process_class, 'sseed', '');
            //}
        }

        if ($a_set['info_file']) {
            $items[] = $this->ui_factory->button()->shy($lng->txt('book_download_info'), $ilCtrl->getLinkTarget($this->parent_obj, 'deliverInfo'));
        }

        if ($this->may_edit) {
            $items[] = $this->ui_factory->button()->shy($lng->txt('edit'), $ilCtrl->getLinkTarget($this->parent_obj, 'edit'));

            // #10890
            if (!$has_reservations) {
                $items[] = $this->ui_factory->button()->shy($lng->txt('delete'), $ilCtrl->getLinkTarget($this->parent_obj, 'confirmDelete'));
            }
        }

        if ($this->advmd) {
            foreach ($this->advmd as $item) {
                $advmd_id = (int) $item["id"];

                if (!in_array("advmd" . $advmd_id, $selected)) {
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
                $this->tpl->setVariable("ADVMD_VAL", $val);
                $this->tpl->parseCurrentBlock();
            }
        }

        if (count($items)) {
            $actions_dropdown = $this->ui_factory->dropdown()->standard($items)->withLabel($this->lng->txt('actions'));
            $this->tpl->setVariable("ACTION_DROPDOWN", $this->ui_renderer->render($actions_dropdown));
        }
    }
}
