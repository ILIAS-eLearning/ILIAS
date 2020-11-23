<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
require_once "Services/Calendar/classes/class.ilCalendarUtil.php";

/**
 * List booking objects
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingReservationsTableGUI extends ilTable2GUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    protected $ref_id;	// int
    protected $filter;	// array
    protected $pool_id;	// int
    protected $show_all; // bool
    protected $has_schedule; // bool
    protected $objects; // array
    protected $group_id; // int
    protected $advmd; // [array]

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * Constructor
     * @param	object	$a_parent_obj
     * @param	string	$a_parent_cmd
     * @param	int		$a_ref_id
     * @param	int		$a_pool_id
     * @param	bool	$a_show_all
     * @param	bool	$a_has_schedule
     * @param	array	$a_filter_pre
     * @param	array	$a_group_id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id, $a_pool_id, $a_show_all, $a_has_schedule, array $a_filter_pre = null, $a_group_id = null)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilUser = $DIC->user();
        $ilAccess = $DIC->access();
        $this->tree = $DIC->repositoryTree();

        $this->pool_id = $a_pool_id;
        $this->ref_id = $a_ref_id;
        $this->show_all = $a_show_all;
        $this->has_schedule = (bool) $a_has_schedule;
        $this->group_id = $a_group_id;
        
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
            if (in_array("week", $selected)) {
                $this->addColumn($this->lng->txt("wk_short"), "week");
                unset($cols["week"]);
            }
            if (in_array("weekday", $selected)) {
                $this->addColumn($this->lng->txt("cal_weekday"), "weekday");
                unset($cols["weekday"]);
            }
            $this->addColumn($this->lng->txt("book_schedule_slot"), "slot");
            $this->addColumn($this->lng->txt("book_no_of_objects"), "counter");
            
            $this->setDefaultOrderField("date");
            $this->setDefaultOrderDirection("asc");
        } else {
            $this->addColumn($this->lng->txt("status"), "status");
            
            $this->setDefaultOrderField("title");
            $this->setDefaultOrderDirection("asc");
        }

        // non-user columns
        $user_cols = $this->getSelectableUserColumns();
        foreach ($this->getSelectedColumns() as $col) {
            if (array_key_exists($col, $cols)) {
                if (!isset($user_cols[$col])) {
                    $this->addColumn($cols[$col]["txt"], $col);
                }
            }
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
        $this->setRowTemplate("tpl.booking_reservation_row.html", "Modules/BookingManager");
        $this->setResetCommand("resetLogFilter");
        $this->setFilterCommand("applyLogFilter");
        $this->setDisableFilterHiding(true);
                
        $this->initFilter($a_filter_pre);

        if ($this->group_id) {
            $this->setLimit(9999);
            $this->disable("numinfo");
            $this->filters = array();
        } else {
            $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
        }
        
        if ($ilUser->getId() != ANONYMOUS_USER_ID) {
            /*
            if($ilAccess->checkAccess('write', '', $this->ref_id))
            {
                $this->addMultiCommand('rsvInUse', $lng->txt('book_set_in_use'));
                $this->addMultiCommand('rsvNotInUse', $lng->txt('book_set_not_in_use'));
            }
            */
            
            $this->addMultiCommand('rsvConfirmCancel', $lng->txt('book_set_cancel'));
            // $this->addMultiCommand('rsvUncancel', $lng->txt('book_set_not_cancel'));
            $this->setSelectAllCheckbox('mrsv');
        }
        
        $this->getItems($this->getCurrentFilter());
        
        ilDatePresentation::setUseRelativeDates(false);
    }
    
    public function getSelectableColumns($a_only_advmd = false, $a_include_user = true)
    {
        $cols = array();

        if ($this->has_schedule &&
            !(bool) $a_only_advmd) {
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

        if ($a_include_user) {
            $cols = array_merge($cols, $this->getSelectableUserColumns());
        }

        return $cols;
    }

    /**
     * Get selectable user fields
     *
     * @param
     * @return
     */
    protected function getSelectableUserColumns()
    {
        $cols = [];
        // additional user fields
        if (($parent = $this->getParentGroupCourse()) !== false) {
            if ($this->access->checkAccess("manage_members", "", $parent["ref_id"])) {
                include_once './Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php';
                $ef = ilExportFieldsInfo::_getInstanceByType($parent["type"]);
                foreach ($ef->getSelectableFieldsInfo(ilObject::_lookupObjectId($parent["ref_id"])) as $k => $v) {
                    if (!in_array($k, ["login"])) {
                        $cols[$k] = $v;
                    }
                }
            }
        }
        return $cols;
    }

    /**
     * Get selected user colimns
     *
     * @param
     * @return
     */
    protected function getSelectedUserColumns()
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


    /**
     * Get parent group or course
     *
     * @param
     * @return
     */
    protected function getParentGroupCourse()
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
        return false;
    }



    /**
    * Init filter
    */
    public function initFilter(array $a_filter_pre = null)
    {
        if (is_array($a_filter_pre) &&
            isset($a_filter_pre["object"])) {
            $_SESSION["form_" . $this->getId()]["object"] = serialize($a_filter_pre["object"]);
        }
        
        $this->objects = array();
        include_once "Modules/BookingManager/classes/class.ilBookingObject.php";
        foreach (ilBookingObject::getList($this->pool_id) as $item) {
            $this->objects[$item["booking_object_id"]] = $item["title"];
        }
        $item = $this->addFilterItemByMetaType("object", ilTable2GUI::FILTER_SELECT);
        $item->setOptions(array("" => $this->lng->txt('book_all')) + $this->objects);
        $this->filter["object"] = $item->getValue();
        
        $title = $this->addFilterItemByMetaType(
            "title",
            ilTable2GUI::FILTER_TEXT,
            false,
            $this->lng->txt("object") . " " . $this->lng->txt("title") . "/" . $this->lng->txt("description")
        );
        $this->filter["title"] = $title->getValue();

        if ($this->has_schedule) {
            // default period: from:today [ to:(today + n days) ]
            if (!$_SESSION["form_" . $this->getId()]["fromto"]) {
                $from = new ilDateTime(date("Y-m-d"), IL_CAL_DATE); // today
                $to = null;
                
                // add period end from pool settings?
                include_once "Modules/BookingManager/classes/class.ilObjBookingPool.php";
                $bpool = new ilObjBookingPool($this->pool_id, false);
                $period = $bpool->getReservationFilterPeriod();
                if ($period !== null) {
                    $to = clone $from;
                    if ($period) {
                        $to->increment(ilDateTime::DAY, $period);
                    }
                    $to = serialize($to);
                }
                
                $_SESSION["form_" . $this->getId()]["fromto"] = serialize(array(
                    "from" => serialize($from),
                    "to" => $to
                ));
            }
            $item = $this->addFilterItemByMetaType("fromto", ilTable2GUI::FILTER_DATE_RANGE, false, $this->lng->txt('book_fromto'));
            $this->filter["fromto"] = $item->getDate();
            
            // only needed for full log
            if ($this->show_all) {
                // see ilObjBookingPoolGUI::buildDatesBySchedule()
                $map = array_flip(array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'));
                
                $options = array("" => $this->lng->txt('book_all'));
                
                // schedule to slot
                require_once "Modules/BookingManager/classes/class.ilBookingSchedule.php";
                foreach (ilBookingSchedule::getList($this->pool_id) as $def) {
                    $schedule = new ilBookingSchedule($def["booking_schedule_id"]);
                    foreach ($schedule->getDefinition() as $day => $slots) {
                        $day_caption = ilCalendarUtil::_numericDayToString($map[$day], false);
                    
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
        if (is_object($this->filter["fromto"]["from"]) &&
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

    /**
     * Get current filter settings
     * @return	array
     */
    public function getCurrentFilter()
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
        if ($this->filter["user_id"]) {
            $filter["user_id"] = $this->filter["user_id"];
        }
        
        if ($this->has_schedule) {
            if (!$filter["status"]) {
                // needs distinct status because of aggregation
                $filter["status"] = -ilBookingReservation::STATUS_CANCELLED;
            }
            if ($this->filter["slot"]) {
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
    
    public function numericOrdering($a_field)
    {
        return in_array($a_field, array("counter", "date", "week", "weekday"));
    }
    
    /**
     * Gather data and build rows
     * @param	array	$filter
     */
    public function getItems(array $filter)
    {
        $ilUser = $this->user;
        
        if (!$filter["object"]) {
            $ids = array_keys($this->objects);
        } else {
            $ids = array($filter["object"]);
        }
        
        if (!$this->show_all) {
            $filter["user_id"] = $ilUser->getId();
        }
    
        include_once "Modules/BookingManager/classes/class.ilBookingReservation.php";
        $data = ilBookingReservation::getListByDate($this->has_schedule, $ids, $filter);
        
        if ($this->advmd) {
            // advanced metadata
            include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
            $this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_FILTER, "book", $this->pool_id, "bobj");
            $this->record_gui->setTableGUI($this);
            $this->record_gui->parse();
            
            foreach (array_keys($data) as $idx) {
                $data[$idx]["pool_id"] = $this->pool_id;
            }
            
            include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php");
            $data = ilAdvancedMDValues::queryForRecords(
                $this->ref_id,
                "book",
                "bobj",
                $this->pool_id,
                "bobj",
                $data,
                "pool_id",
                "object_id",
                $this->record_gui->getFilterElements()
            );
        }

        if (count($this->getSelectedUserColumns()) > 0) {
            // get additional user data
            $user_ids = array_unique(array_map(function ($d) {
                return $d['user_id'];
            }, $data));

            // user data fields
            $query = new ilUserQuery();
            $query->setLimit(9999);
            $query->setAdditionalFields($this->getSelectedUserColumns());
            $query->setUserFilter($user_ids);
            $ud = $query->query();
            $usr_data = [];
            foreach ($ud["set"] as $v) {
                foreach ($this->getSelectedUserColumns() as $c) {
                    $usr_data[$v["usr_id"]][$c] = $v[$c];
                }
            }
            foreach ($data as $key => $v) {
                $data[$key] = array_merge($v, $usr_data[$v["user_id"]]);
            }
        }

        $this->setData($data);
    }
    
    public function getAdvMDRecordGUI()
    {
        return $this->record_gui;
    }
    
    public function getOrderField()
    {
        $field = parent::getOrderField();
        
        // #16560 - this will enable matchting slot sorting to date/week
        if (in_array($field, array("date", "week"))) {
            $field = "_sortdate";
        }
        
        return $field;
    }

    /**
     * Fill table row
     * @param	array	$a_set
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        $selected = $this->getSelectedColumns();
        
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
            //$ilCtrl->setParameter($this->parent_obj, 'user_id', $a_set['user_id']);
            //$this->tpl->setVariable("HREF_PROFILE", $ilCtrl->getLinkTarget($this->parent_obj, 'showprofile'));
            //$ilCtrl->setParameter($this->parent_obj, 'user_id', '');
            include_once("./Services/User/classes/class.ilUserUtil.php");
            $uname = ilUserUtil::getNamePresentation($a_set['user_id'], false, true, "", true);
        }
        $this->tpl->setVariable("TXT_CURRENT_USER", $uname);

        if ($this->has_schedule) {
            $this->tpl->setVariable("VALUE_DATE", ilDatePresentation::formatDate(new ilDate($a_set["date"], IL_CAL_DATE)));
            if (in_array("week", $selected)) {
                $this->tpl->setVariable("VALUE_WEEK", $a_set["week"]);
            }
            if (in_array("weekday", $selected)) {
                $this->tpl->setVariable("VALUE_WEEKDAY", ilCalendarUtil::_numericDayToString($a_set["weekday"], false));
            }
            $this->tpl->setVariable("VALUE_SLOT", $a_set["slot"]);
            $this->tpl->setVariable("VALUE_COUNTER", $a_set["counter"]);
        } else {
            if (in_array($a_set['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE))) {
                $this->tpl->setVariable("TXT_STATUS", $lng->txt('book_reservation_status_' . $a_set['status']));
            } else {
                $this->tpl->setVariable("TXT_STATUS", "&nbsp;");
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
            $this->tpl->setVariable("URL_ACTION", $ilCtrl->getLinkTarget($this->parent_obj, 'rsvConfirmCancel'));
            $ilCtrl->setParameter($this->parent_obj, 'reservation_id', "");
            $this->tpl->setVariable("TXT_ACTION", $lng->txt('book_set_cancel'));
            $this->tpl->setCurrentBlock("action");
            $this->tpl->parseCurrentBlock();
        }

        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilCtrl->setParameter($this->parent_obj, 'reservation_id', $a_set['booking_reservation_id']);
            $this->tpl->setVariable("URL_ACTION", $ilCtrl->getLinkTarget($this->parent_obj, 'rsvConfirmDelete'));
            $ilCtrl->setParameter($this->parent_obj, 'reservation_id', "");
            $this->tpl->setVariable("TXT_ACTION", $lng->txt('delete'));
            $this->tpl->setCurrentBlock("action");
            $this->tpl->parseCurrentBlock();
        }
        
        /* advsellist version
        if (!$this->has_schedule || $date_to->get(IL_CAL_UNIX) > time())
        {
            include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($a_set['booking_reservation_id']);
            $alist->setListTitle($lng->txt("actions"));

            $ilCtrl->setParameter($this->parent_obj, 'reservation_id', $a_set['booking_reservation_id']);

            if(!$a_set['group_id'])
            {
                if($ilAccess->checkAccess('write', '', $this->ref_id))
                {
                    if($a_set['status'] == ilBookingReservation::STATUS_CANCELLED)
                    {
                        // can be uncancelled?
                        // if(ilBookingReservation::getAvailableObject(array($a_set['object_id']), $date_from->get(IL_CAL_UNIX), $date_to->get(IL_CAL_UNIX)))
                        // {
                        //	  $alist->addItem($lng->txt('book_set_not_cancel'), 'not_cancel', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvUncancel'));
                        // }
                    }
                    else if($a_set['status'] != ilBookingReservation::STATUS_IN_USE)
                    {
                        if($this->has_schedule)
                        {
                            $alist->addItem($lng->txt('book_set_in_use'), 'in_use', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvInUse'));
                        }
                        $alist->addItem($lng->txt('book_set_cancel'), 'cancel', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvConfirmCancel'));
                    }
                    else if($this->has_schedule)
                    {
                        $alist->addItem($lng->txt('book_set_not_in_use'), 'not_in_use', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvNotInUse'));
                    }
                }
                else if($a_set['user_id'] == $ilUser->getId() && $a_set['status'] != ilBookingReservation::STATUS_CANCELLED)
                {
                    $alist->addItem($lng->txt('book_set_cancel'), 'cancel', $ilCtrl->getLinkTarget($this->parent_obj, 'rsvConfirmCancel'));
                }
            }
            else if($ilAccess->checkAccess('write', '', $this->ref_id) || $a_set['user_id'] == $ilUser->getId())
            {
                $alist->addItem($lng->txt('details'), 'details', $ilCtrl->getLinkTarget($this->parent_obj, 'logDetails'));
            }

            if(sizeof($alist->getItems()))
            {
                if(!$a_set['group_id'])
                {
                    $this->tpl->setVariable('MULTI_ID', $a_set['booking_reservation_id']);
                }
                $this->tpl->setVariable('LAYER', $alist->getHTML());
            }
        }
        */
    }

    /**
     * Get additional export columns
     * @param
     * @return
     */
    protected function getAdditionalExportCols()
    {
        $add_cols = [];
        $cols = $this->getSelectableColumns();

        unset($cols["week"]);
        unset($cols["weekday"]);

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

    protected function fillHeaderExcel(ilExcel $a_excel, &$a_row)
    {
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
        
        foreach ($this->getAdditionalExportCols() as $txt) {
            $a_excel->setCell($a_row, ++$col, $txt);
        }

        $a_excel->setBold("A" . $a_row . ":" . $a_excel->getColumnCoord($col) . $a_row);
    }

    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $a_set)
    {
        $a_excel->setCell($a_row, 0, $a_set["title"]);
        $col = 0;
        if ($this->has_schedule) {
            $a_excel->setCell($a_row, ++$col, new ilDate($a_set["date"], IL_CAL_DATE));
            $a_excel->setCell($a_row, ++$col, $a_set["week"]);
            $a_excel->setCell($a_row, ++$col, ilCalendarUtil::_numericDayToString($a_set["weekday"], false));
            $a_excel->setCell($a_row, ++$col, $a_set["slot"]);
            $a_excel->setCell($a_row, ++$col, $a_set["counter"]);
        } else {
            $status = "";
            if (in_array($a_set['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE))) {
                $status = $this->lng->txt('book_reservation_status_' . $a_set['status']);
            }
            $a_excel->setCell($a_row, ++$col, $status);
        }


        foreach ($this->getAdditionalExportCols() as $colid => $txt) {
            if (substr($colid, 0, 5) == "advmd") {
                $advmd_id = (int) substr($colid, 5);
                $val = " ";
                if (isset($a_set["md_" . $advmd_id . "_presentation"])) {
                    $pb = $a_set["md_" . $advmd_id . "_presentation"]->getList();
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

    protected function fillHeaderCSV($a_csv)
    {
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
                
        foreach ($this->getAdditionalExportCols() as $txt) {
            $a_csv->addColumn($txt);
        }

        $a_csv->addRow();
    }

    protected function fillRowCSV($a_csv, $a_set)
    {
        $a_csv->addColumn($a_set["title"]);
        if ($this->has_schedule) {
            $a_csv->addColumn(ilDatePresentation::formatDate(new ilDate($a_set["date"], IL_CAL_DATE)));
            $a_csv->addColumn($a_set["week"]);
            $a_csv->addColumn(ilCalendarUtil::_numericDayToString($a_set["weekday"], false));
            $a_csv->addColumn($a_set["slot"]);
            $a_csv->addColumn($a_set["counter"]);
        } else {
            $status = "";
            if (in_array($a_set['status'], array(ilBookingReservation::STATUS_CANCELLED, ilBookingReservation::STATUS_IN_USE))) {
                $status = $this->lng->txt('book_reservation_status_' . $a_set['status']);
            }
            $a_csv->addColumn($status);
        }

        foreach ($this->getAdditionalExportCols() as $colid => $txt) {
            if (substr($colid, 0, 5) == "advmd") {
                $advmd_id = (int) substr($colid, 5);
                $val = " ";
                if (isset($a_set["md_" . $advmd_id . "_presentation"])) {
                    $pb = $a_set["md_" . $advmd_id . "_presentation"]->getList();
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
