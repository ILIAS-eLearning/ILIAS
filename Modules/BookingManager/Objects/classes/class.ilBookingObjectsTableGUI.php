<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * List booking objects (for booking type)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingObjectsTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilObjUser
     */
    protected $user;

    protected $ref_id; // [int]
    protected $pool_id;	// [int]
    protected $has_schedule;	// [bool]
    protected $may_edit;	// [bool]
    protected $may_assign; // [bool]
    protected $overall_limit;	// [int]
    protected $reservations = array();	// [array]
    protected $current_bookings; // [int]
    protected $advmd; // [array]
    protected $filter; // [array]
    protected $ui_factory;
    protected $ui_renderer;

    /**
     * @var bool
     */
    protected $active_management;

    /**
     * Constructor
     * @param	object	$a_parent_obj
     * @param	string	$a_parent_cmd
     * @param	int		$a_ref_id
     * @param	int		$a_pool_id
     * @param	bool	$a_pool_has_schedule
     * @param	int		$a_pool_overall_limit
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_ref_id,
        $a_pool_id,
        $a_pool_has_schedule,
        $a_pool_overall_limit,
        bool $active_management = true
    ) {
        global $DIC;

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

        $this->setTitle($lng->txt("book_objects_list"));

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
        
        $this->initFilter();
        $this->getItems();
    }
        
    /**
     * needed for advmd filter handling
     *
     * @return ilAdvancedMDRecordGUI
     */
    protected function getAdvMDRecordGUI()
    {
        // #16827
        return $this->record_gui;
    }
    
    public function initFilter()
    {
        $lng = $this->lng;
        
        // title/description
        $title = $this->addFilterItemByMetaType(
            "title",
            ilTable2GUI::FILTER_TEXT,
            false,
            $lng->txt("title") . "/" . $lng->txt("description")
        );
        $this->filter["title"] = $title->getValue();
        
        // #18651
        if ($this->has_schedule) {
            // booking period
            $period = $this->addFilterItemByMetaType(
                "period",
                ilTable2GUI::FILTER_DATE_RANGE,
                false,
                $lng->txt("book_period")
            );
            $this->filter["period"] = $period->getValue();
        }
    }
    
    /**
     * Gather data and build rows
     */
    public function getItems()
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
        
        foreach ($data as $item) {
            $item_id = $item["booking_object_id"];
            
            // available for given period?
            if (is_object($this->filter["period"]["from"]) ||
                is_object($this->filter["period"]["to"])) {
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
                ilUtil::sendInfo($this->lng->txt("book_overall_limit_warning"));
            }
        }
        
        if ($this->advmd) {
            // advanced metadata
            $this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_FILTER, "book", $this->pool_id, "bobj");
            $this->record_gui->setTableGUI($this);
            $this->record_gui->parse();
            
            $data = ilAdvancedMDValues::queryForRecords(
                $this->ref_id,
                "book",
                "bobj",
                $this->pool_id,
                "bobj",
                $data,
                "pool_id",
                "booking_object_id",
                $this->record_gui->getFilterElements()
            );
        }
        
        $this->setMaxCount(sizeof($data));
        $this->setData($data);
    }
    
    public function numericOrdering($a_field)
    {
        if (substr($a_field, 0, 3) == "md_") {
            $md_id = (int) substr($a_field, 3);
            if ($this->advmd[$md_id]["type"] == ilAdvancedMDFieldDefinition::TYPE_DATE) {
                return true;
            }
        }
        return false;
    }
    
    public function getSelectableColumns()
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

    /**
     * Fill table row
     * @param	array	$a_set
     */
    protected function fillRow($a_set)
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
        
        if (in_array("description", $selected)) {
            $this->tpl->setVariable("TXT_DESC", nl2br($a_set["description"]));
        }
        
        if ($a_set["full_up"]) {
            $this->tpl->setVariable("NOT_YET", $a_set["full_up"]);
            $booking_possible = false;
            $assign_possible = false;
        } elseif ($a_set["not_yet"]) {
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
            if (is_object($this->filter['period']['from'])) {
                $ilCtrl->setParameter($this->parent_obj, 'sseed', $this->filter['period']['from']->get(IL_CAL_DATE));
            }

            $items[] = $this->ui_factory->button()->shy(
                $lng->txt('book_book'),
                $ilCtrl->getLinkTargetByClass("ilbookingprocessgui", 'book')
            );

            $ilCtrl->setParameter($this->parent_obj, 'sseed', '');
        }
        
        // #16663
        if (!$this->has_schedule && $has_booking) {
            if (trim($a_set['post_text']) || $a_set['post_file']) {
                $items[] = $this->ui_factory->button()->shy(
                    $lng->txt('book_post_booking_information'),
                    $ilCtrl->getLinkTargetByClass("ilbookingprocessgui", 'displayPostInfo')
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
                if (is_object($this->filter['period']['from'])) {
                    $ilCtrl->setParameterByClass(
                        "ilbookingprocessgui",
                        'sseed',
                        $this->filter['period']['from']->get(IL_CAL_DATE)
                    );
                }

                $items[] = $this->ui_factory->button()->shy(
                    $lng->txt('book_assign_participant'),
                    $ilCtrl->getLinkTargetByClass("ilbookingprocessgui", 'assignParticipants')
                );

                $ilCtrl->setParameterByClass("ilbookingprocessgui", 'sseed', '');
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
                if (isset($a_set["md_" . $advmd_id . "_presentation"])) {
                    $pb = $a_set["md_" . $advmd_id . "_presentation"]->getList();
                    if ($pb) {
                        $val = $pb;
                    }
                }
                
                $this->tpl->setCurrentBlock("advmd_bl");
                $this->tpl->setVariable("ADVMD_VAL", $val);
                $this->tpl->parseCurrentBlock();
            }
        }

        if (sizeof($items)) {
            $actions_dropdown = $this->ui_factory->dropdown()->standard($items)->withLabel($this->lng->txt('actions'));
            $this->tpl->setVariable("ACTION_DROPDOWN", $this->ui_renderer->render($actions_dropdown));
        }
    }
}
