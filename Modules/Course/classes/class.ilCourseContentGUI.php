<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilCourseContentGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends ilObjectGUI
*
* @ilCtrl_Calls ilCourseContentGUI: ilColumnGUI, ilObjectCopyGUI
*
*/
class ilCourseContentGUI
{
    public $container_gui;
    public $container_obj;
    public $course_obj;

    public $tpl;
    public $ctrl;
    public $lng;
    public $tabs_gui;

    /**
     * @var ilFavouritesManager
     */
    protected $fav_manager;
    private \ilGlobalTemplateInterface $main_tpl;

    /**
     * Constructor
     * @access public
     * @param ilObjectGUI
     */
    public function __construct($container_gui_obj)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilTabs = $DIC['ilTabs'];

        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('crs');
        $this->tabs_gui = $ilTabs;

        $this->container_gui = &$container_gui_obj;
        $this->container_obj = &$this->container_gui->object;
        $this->fav_manager = new ilFavouritesManager();

        $this->__initCourseObject();
    }
    
    /**
    * Creates a combination of HTML selects for date inputs
    *
    * Creates a combination of HTML selects for date inputs
    * The select names are $prefix[y] for years, $prefix[m]
    * for months and $prefix[d] for days.
    *
    * @access	public
    * @param	string	$prefix Prefix of the select name
    * @param	integer	$year Default value for year select
    * @param	integer	$month Default value for month select
    * @param	integer	$day Default value for day select
    * @return	string	HTML select boxes
    * @author	Aresch Yavari <ay@databay.de>
    * @author Helmut Schottmüller <hschottm@tzi.de>
    * @static
    *
    */
    public static function makeDateSelect(
        $prefix,
        $year = "",
        $month = "",
        $day = "",
        $startyear = "",
        $a_long_month = true,
        $a_further_options = [],
        $emptyoption = false
    ) {
        global $DIC;
        
        $lng = $DIC->language();
        
        $disabled = '';
        if (isset($a_further_options['disabled']) and $a_further_options['disabled']) {
            $disabled = 'disabled="disabled" ';
        }
        
        $now = getdate();
        if (!$emptyoption) {
            if (!strlen($year)) {
                $year = $now["year"];
            }
            if (!strlen($month)) {
                $month = $now["mon"];
            }
            if (!strlen($day)) {
                $day = $now["mday"];
            }
        }
        
        $year = (int) $year;
        $month = (int) $month;
        $day = (int) $day;
        
        // build day select
        
        $sel_day .= '<select class="form-control" ';
        if (isset($a_further_options['select_attributes'])) {
            foreach ($a_further_options['select_attributes'] as $name => $value) {
                $sel_day .= ($name . '="' . $value . '" ');
            }
        }
        
        $sel_day .= $disabled . "name=\"" . $prefix . "[d]\" id=\"" . $prefix . "_d\">\n";
        
        if ($emptyoption) {
            $sel_day .= "<option value=\"0\">--</option>\n";
        }
        for ($i = 1; $i <= 31; $i++) {
            $sel_day .= "<option value=\"$i\">" . sprintf("%02d", $i) . "</option>\n";
        }
        $sel_day .= "</select>\n";
        $sel_day = preg_replace("/(value\=\"$day\")/", "$1 selected=\"selected\"", $sel_day);
        
        // build month select
        $sel_month = '<select class="form-control" ';
        if (isset($a_further_options['select_attributes'])) {
            foreach ($a_further_options['select_attributes'] as $name => $value) {
                $sel_month .= ($name . '="' . $value . '" ');
            }
        }
        $sel_month .= $disabled . "name=\"" . $prefix . "[m]\" id=\"" . $prefix . "_m\">\n";
        
        if ($emptyoption) {
            $sel_month .= "<option value=\"0\">--</option>\n";
        }
        for ($i = 1; $i <= 12; $i++) {
            if ($a_long_month) {
                $sel_month .= "<option value=\"$i\">" . $lng->txt(
                        "month_" . sprintf("%02d", $i) . "_long"
                    ) . "</option>\n";
            } else {
                $sel_month .= "<option value=\"$i\">" . $i . "</option>\n";
            }
        }
        $sel_month .= "</select>\n";
        $sel_month = preg_replace("/(value\=\"$month\")/", "$1 selected=\"selected\"", $sel_month);
        
        // build year select
        $sel_year = '<select class="form-control" ';
        if (isset($a_further_options['select_attributes'])) {
            foreach ($a_further_options['select_attributes'] as $name => $value) {
                $sel_year .= ($name . '="' . $value . '" ');
            }
        }
        $sel_year .= $disabled . "name=\"" . $prefix . "[y]\" id=\"" . $prefix . "_y\">\n";
        if ((strlen($startyear) == 0) || ($startyear > $year)) {
            if (!$emptyoption || $year != 0) {
                $startyear = $year - 5;
            }
        }
        
        if (($year + 5) < (date('Y', time()) + 5)) {
            $end_year = date('Y', time()) + 5;
        } else {
            $end_year = $year + 5;
        }
        
        if ($emptyoption) {
            $sel_year .= "<option value=\"0\">----</option>\n";
        }
        for ($i = $startyear; $i <= $end_year; $i++) {
            $sel_year .= "<option value=\"$i\">" . sprintf("%04d", $i) . "</option>\n";
        }
        $sel_year .= "</select>\n";
        $sel_year = preg_replace("/(value\=\"$year\")/", "$1 selected=\"selected\"", $sel_year);
        
        //$dateformat = $lng->text["lang_dateformat"];
        $dateformat = "d-m-Y";
        $dateformat = strtolower(preg_replace("/\W/", "", $dateformat));
        $dateformat = strtolower(preg_replace("/(\w)/", "%%$1", $dateformat));
        $dateformat = preg_replace("/%%d/", $sel_day, $dateformat);
        $dateformat = preg_replace("/%%m/", $sel_month, $dateformat);
        $dateformat = preg_replace("/%%y/", $sel_year, $dateformat);
        return $dateformat;
    }
    
    public function executeCommand()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilTabs = $DIC['ilTabs'];
        $ilCtrl = $DIC['ilCtrl'];

        if (!$ilAccess->checkAccess('read', '', $this->container_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->WARNING);
        }


        $this->__setSubTabs();
        $this->tabs_gui->setTabActive('view_content');
        $cmd = $this->ctrl->getCmd();

        switch ($this->ctrl->getNextClass($this)) {
            case 'ilcoursearchivesgui':
                $this->__forwardToArchivesGUI();
                break;

            case "ilcolumngui":
                $ilCtrl->saveParameterByClass("ilcolumngui", "col_return");
                $ilTabs->setSubTabActive("crs_content");
                $this->view();
                break;

            default:
                if (!$this->__checkStartObjects()) {
                    $this->showStartObjects();
                    break;
                }

                // forward to objective presentation
                if ((!$this->is_tutor and
                   $this->container_obj->getType() == 'crs' and
                   $this->container_obj->enabledObjectiveView()) ||
                   $_GET["col_return"] == "objectives") {
                    $this->use_objective_presentation = true;
                    $this->view();
                    //$this->__forwardToObjectivePresentation();
                    break;
                }


                if (!$cmd) {
                    $cmd = $this->__getDefaultCommand();
                }
                $this->$cmd();
                break;
        }
    }

    /**
     * @return ilObject
     */
    public function getContainerObject()
    {
        return $this->container_obj;
    }

    public function __getDefaultCommand()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];

        // edit timings if panel is on
        if ($_SESSION['crs_timings_panel'][$this->course_obj->getId()]) {
            return 'manageTimings';
        }
        if ($ilAccess->checkAccess('write', '', $this->container_obj->getRefId())) {
            return 'view';
        }
        if ($this->container_obj->getType() == 'crs' and
           $this->course_obj->getViewMode() == ilCourseConstants::IL_CRS_VIEW_TIMING) {
            return 'editUserTimings';
        }
        return 'view';
    }

    public function __checkStartObjects()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];

        if ($ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            return true;
        }
        $this->start_obj = new ilCourseStart($this->course_obj->getRefId(), $this->course_obj->getId());
        if (count($this->start_obj->getStartObjects()) and !$this->start_obj->allFullfilled($ilUser->getId())) {
            return false;
        }
        return true;
    }

    public function showStartObjects()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $this->tabs_gui->setSubTabActive('crs_content');

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_start_view.html", 'Modules/Course');
        $this->tpl->setVariable("INFO_STRING", $this->lng->txt('crs_info_start'));
        $this->tpl->setVariable("TBL_TITLE_START", $this->lng->txt('crs_table_start_objects'));
        $this->tpl->setVariable("HEADER_NR", $this->lng->txt('crs_nr'));
        $this->tpl->setVariable("HEADER_DESC", $this->lng->txt('description'));
        $this->tpl->setVariable("HEADER_EDITED", $this->lng->txt('crs_objective_accomplished'));


        $lm_continue = new ilCourseLMHistory($this->course_obj->getRefId(), $ilUser->getId());
        $continue_data = $lm_continue->getLMHistory();

        $counter = 0;
        foreach ($this->start_obj->getStartObjects() as $start) {
            $obj_id = $ilObjDataCache->lookupObjId($start['item_ref_id']);
            $ref_id = $start['item_ref_id'];
            $type = $ilObjDataCache->lookupType($obj_id);

            $conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($ref_id, $obj_id);

            $obj_link = ilLink::_getLink($ref_id, $type);
            $obj_frame = '';

            // Tmp fix for tests
            $obj_frame = $type == 'tst' ? '' : $obj_frame;

            $contentObj = false;

            if ($ilAccess->checkAccess('read', '', $ref_id)) {
                $this->tpl->setCurrentBlock("start_read");
                $this->tpl->setVariable("READ_TITLE_START", $ilObjDataCache->lookupTitle($obj_id));
                $this->tpl->setVariable("READ_TARGET_START", $obj_frame);
                $this->tpl->setVariable("READ_LINK_START", $obj_link . '&crs_show_result=' . $this->course_obj->getRefId());
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock("start_visible");
                $this->tpl->setVariable("VISIBLE_LINK_START", $ilObjDataCache->lookupTitle($obj_id));
                $this->tpl->parseCurrentBlock();
            }

            // CONTINUE LINK
            if (isset($continue_data[$ref_id])) {
                $this->tpl->setCurrentBlock("link");
                $this->tpl->setVariable("LINK_HREF", ilLink::_getLink($ref_id, '', array('obj_id',
                                                                                      $continue_data[$ref_id]['lm_page_id'])));
                #$this->tpl->setVariable("CONTINUE_LINK_TARGET",$target);
                $this->tpl->setVariable("LINK_NAME", $this->lng->txt('continue_work'));
                $this->tpl->parseCurrentBlock();
            }

            // add to desktop link
            if ($this->course_obj->getAboStatus()) {
                if (!$this->fav_manager->ifIsFavourite($ilUser->getId(), $ref_id)) {
                    if ($ilAccess->checkAccess('read', '', $ref_id)) {
                        $this->tpl->setCurrentBlock("link");
                        $this->ctrl->setParameterByClass(get_class($this->container_gui), 'item_ref_id', $ref_id);
                        $this->ctrl->setParameterByClass(get_class($this->container_gui), 'item_id', $ref_id);
                        $this->ctrl->setParameterByClass(get_class($this->container_gui), 'type', $type);

                        $this->tpl->setVariable(
                            "LINK_HREF",
                            $this->ctrl->getLinkTarget($this->container_gui, 'addToDesk')
                        );
                        $this->tpl->setVariable("LINK_NAME", $this->lng->txt("rep_add_to_favourites"));
                        $this->tpl->parseCurrentBlock();
                    }
                } else {
                    $this->tpl->setCurrentBlock("link");
                    $this->ctrl->setParameterByClass(get_class($this->container_gui), 'item_ref_id', $ref_id);
                    $this->ctrl->setParameterByClass(get_class($this->container_gui), 'item_id', $ref_id);
                    $this->ctrl->setParameterByClass(get_class($this->container_gui), 'type', $type);

                    $this->tpl->setVariable(
                        "LINK_HREF",
                        $this->ctrl->getLinkTarget($this->container_gui, 'removeFromDesk')
                    );
                    $this->tpl->setVariable("LINK_NAME", $this->lng->txt("rep_remove_from_favourites"));
                    $this->tpl->parseCurrentBlock();
                }
            }


            // Description
            if (strlen($ilObjDataCache->lookupDescription($obj_id))) {
                $this->tpl->setCurrentBlock("start_description");
                $this->tpl->setVariable("DESCRIPTION_START", $ilObjDataCache->lookupDescription($obj_id));
                $this->tpl->parseCurrentBlock();
            }


            if ($this->start_obj->isFullfilled($ilUser->getId(), $ref_id)) {
                $accomplished = 'accomplished';
                $icon = ilUtil::getImagePath("icon_ok.svg");
            } else {
                $accomplished = 'not_accomplished';
                $icon = ilUtil::getImagePath("icon_not_ok.svg");
            }
            $this->tpl->setCurrentBlock("start_row");
            $this->tpl->setVariable("EDITED_IMG", $icon);
            $this->tpl->setVariable("EDITED_ALT", $this->lng->txt('crs_objective_' . $accomplished));
            $this->tpl->setVariable("ROW_CLASS", 'option_value');
            $this->tpl->setVariable("ROW_CLASS_CENTER", 'option_value_center');
            $this->tpl->setVariable("OBJ_NR_START", ++$counter . '.');
            $this->tpl->parseCurrentBlock();
        }
        return true;
    }

    /**
    * Output course content
    */
    public function view()
    {
        // BEGIN ChangeEvent: record read event.
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $obj_id = ilObject::_lookupObjId($this->container_obj->getRefId());
        ilChangeEvent::_recordReadEvent(
            $this->container_obj->getType(),
            $this->container_obj->getRefId(),
            $obj_id,
            $ilUser->getId()
        );
        // END ChangeEvent: record read event.

        $this->getCenterColumnHTML();

        if (!$this->no_right_column) {
            $this->tpl->setRightContent($this->getRightColumnHTML());
        }
    }

    /**
    * Display right column
    */
    public function getRightColumnHTML()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];

        $ilCtrl->saveParameterByClass("ilcolumngui", "col_return");

        $obj_id = ilObject::_lookupObjId($this->container_obj->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);

        if ($column_gui->getScreenMode() == IL_SCREEN_FULL) {
            return "";
        }

        $this->setColumnSettings($column_gui);

        if ($ilCtrl->getNextClass() == "ilcolumngui" &&
            $column_gui->getCmdSide() == IL_COL_RIGHT &&
            $column_gui->getScreenMode() == IL_SCREEN_SIDE) {
            $html = $ilCtrl->forwardCommand($column_gui);
        } else {
            if (!$ilCtrl->isAsynch()) {
                $html = $ilCtrl->getHTML($column_gui);
            }
        }

        return $html;
    }

    public function setColumnSettings($column_gui)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];

        $column_gui->setRepositoryMode(true);
        $column_gui->setEnableEdit(false);
        $column_gui->setBlockProperty(
            "news",
            "title",
            $lng->txt("crs_news")
        );

        $grouped_items = array();
        foreach (ilObjectActivation::getItems($this->container_obj->getRefId()) as $item) {
            $grouped_items[$item["type"]][] = $item;
        }

        $column_gui->setRepositoryItems($grouped_items);
        if ($ilAccess->checkAccess("write", "", $this->container_obj->getRefId())) {
            $column_gui->setEnableEdit(true);
        }

        // Allow movement of blocks for tutors
        if ($this->is_tutor &&
            $this->container_gui->isActiveAdministrationPanel()) {
            $column_gui->setEnableMovement(true);
        }

        // Configure Settings, if administration panel is on
        if ($this->is_tutor) {
            $column_gui->setBlockProperty("news", "settings", true);
            //$column_gui->setBlockProperty("news", "public_notifications_option", true);
            $column_gui->setBlockProperty("news", "default_visibility_option", true);
            $column_gui->setBlockProperty("news", "hide_news_block_option", true);
        }

        if ($this->container_gui->isActiveAdministrationPanel()) {
            $column_gui->setAdminCommands(true);
        }
    }


    /**
    * Get columngui output
    */
    public function __forwardToColumnGUI()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];


        $obj_id = ilObject::_lookupObjId($this->container_obj->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        if (!$ilCtrl->isAsynch()) {
            //if ($column_gui->getScreenMode() != IL_SCREEN_SIDE)
            if (ilColumnGUI::getScreenMode() != IL_SCREEN_SIDE) {
                // right column wants center
                if (ilColumnGUI::getCmdSide() == IL_COL_RIGHT) {
                    $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);
                    $this->setColumnSettings($column_gui);
                    $html = $ilCtrl->forwardCommand($column_gui);
                }
                // left column wants center
                if (ilColumnGUI::getCmdSide() == IL_COL_LEFT) {
                    $column_gui = new ilColumnGUI($obj_type, IL_COL_LEFT);
                    $this->setColumnSettings($column_gui);
                    $html = $ilCtrl->forwardCommand($column_gui);
                }
            } else {
                {
                    $this->getDefaultView();
                }
            }
        }

        return $html;
    }


    /**
     * Manage timings
     * @global type $ilAccess
     * @global type $ilErr
     * @param type $failed_items
     */
    protected function manageTimings($failed_items = array())
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];
        $mainTemplate = $DIC->ui()->mainTemplate();

        if (!$ilAccess->checkAccess('write', '', $this->container_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_write'), $ilErr->WARNING);
        }
        $GLOBALS['DIC']['ilTabs']->setTabActive('timings_timings');
        $GLOBALS['DIC']['ilTabs']->clearSubTabs();

        $table = new ilTimingsManageTableGUI(
            $this,
            'manageTimings',
            $this->getContainerObject(),
            $this->course_obj
        );
        if (count($failed_items)) {
            $table->setFailureStatus(true);
        }
        $table->init();
        $table->parse(ilObjectActivation::getTimingsAdministrationItems($this->getContainerObject()->getRefId()), $failed_items);


        $mainTemplate->setContent($table->getHTML());
    }

    /**
     * Manage personal timings
     */
    protected function managePersonalTimings($failed = array())
    {
        global $ilErr, $ilAccess;

        if (!$ilAccess->checkAccess('read', '', $this->container_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->WARNING);
        }
        $GLOBALS['ilTabs']->setTabActive('timings_timings');
        $GLOBALS['ilTabs']->clearSubTabs();

        $table = new ilTimingsPersonalTableGUI(
            $this,
            'managePersonalTimings',
            $this->getContainerObject(),
            $this->course_obj
        );
        $table->setFailureStatus(count($failed));
        $table->setUserId($GLOBALS['ilUser']->getId());
        $table->init();
        $table->parse(
            ilObjectActivation::getItems(
                $this->getContainerObject()->getRefId(),
                false
            ),
            $failed
        );
        $GLOBALS['tpl']->setContent($table->getHTML());
    }


    /**
     * Update personal timings
     * @global type $ilAccess
     * @global type $ilErr
     */
    protected function updatePersonalTimings()
    {
        global $ilAccess,$ilErr;

        if (!$ilAccess->checkAccess('read', '', $this->container_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_write'), $ilErr->WARNING);
        }

        $this->tabs_gui->clearSubTabs();

        $failed = array();
        foreach ((array) $_POST['item'] as $ref_id => $data) {
            $sug_start_dt = ilCalendarUtil::parseIncomingDate($data['sug_start']);
            $sug_end_dt = ilCalendarUtil::parseIncomingDate($data['sug_end']);

            if (($sug_start_dt instanceof ilDate) and ($sug_end_dt instanceof ilDate)) {
                if (ilDateTime::_after($sug_start_dt, $sug_end_dt)) {
                    $failed[$ref_id] = 'crs_timing_err_start_end';
                    continue;
                }
                // update user date
                $tu = new ilTimingUser($ref_id, $GLOBALS['ilUser']->getId());
                $tu->getStart()->setDate($sug_start_dt->get(IL_CAL_UNIX), IL_CAL_UNIX);
                $tu->getEnd()->setDate($sug_end_dt->get(IL_CAL_UNIX), IL_CAL_UNIX);
                $tu->update();
            } else {
                $failed['ref_id'] = 'crs_timing_err_valid_dates';
                continue;
            }
        }
        // cognos-blu-patch: begin
        if (!$failed) {
            $this->main_tpl->setOnScreenMessage('success', $GLOBALS['lng']->txt('settings_saved'));
            $this->managePersonalTimings();
            return true;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->managePersonalTimings($failed);
            return true;
        }
        // cognos-blu-patch: end
    }







    public function editTimings()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];

        $this->lng->loadLanguageModule('meta');

        if (!$ilAccess->checkAccess('write', '', $this->container_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_write'), $ilErr->WARNING);
        }
        $this->tabs_gui->setTabActive('timings_timings');
        $this->tabs_gui->clearSubTabs();

        $this->cont_arr = ilObjectActivation::getTimingsAdministrationItems($this->container_obj->getRefId());

        $this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.crs_edit_items.html', 'Modules/Course');
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("HEADER_IMG", ilUtil::getImagePath('icon_crs.svg'));
        $this->tpl->setVariable("HEADER_ALT", $this->lng->txt('crs_materials'));
        $this->tpl->setVariable("BLOCK_HEADER_CONTENT", $this->lng->txt('edit_timings_list'));
        $this->tpl->setVariable("TXT_TITLE", $this->lng->txt('title'));


        $this->tpl->setVariable("TXT_DURATION", $this->lng->txt('crs_timings_time_frame'));
        $this->tpl->setVariable("TXT_INFO_DURATION", $this->lng->txt('crs_timings_in_days'));

        $this->tpl->setVariable("TXT_START_END", $this->lng->txt('crs_timings_short_start_end'));
        $this->tpl->setVariable("TXT_INFO_START_END", $this->lng->txt('crs_timings_start_end_info'));

        $this->tpl->setVariable("TXT_CHANGEABLE", $this->lng->txt('crs_timings_short_changeable'));

        $this->tpl->setVariable("TXT_INFO_LIMIT", $this->lng->txt('crs_timings_from_until'));
        $this->tpl->setVariable("TXT_LIMIT", $this->lng->txt('crs_timings_short_limit_start_end'));
        $this->tpl->setVariable("TXT_ACTIVE", $this->lng->txt('crs_timings_short_active'));
        $this->tpl->setVariable("TXT_INFO_ACTIVE", $this->lng->txt('crs_timings_info_active'));

        $counter = 0;
        foreach ($this->cont_arr as $item) {
            if ($item['type'] == 'itgr') {
                continue;
            }
            $item = $this->__loadFromPost($item);
            $item_prefix = "item[$item[ref_id]]";
            $item_change_prefix = "item_change[$item[ref_id]]";
            $item_active_prefix = "item_active[$item[ref_id]]";

            if ($item['type'] == 'grp' or
               $item['type'] == 'fold') {
                $this->tpl->setVariable("TITLE_LINK", ilLink::_getLink($item['ref_id'], $item['type']));
                $this->tpl->setVariable("TITLE_FRAME", ilFrameTargetInfo::_getFrame('MainContent', $item['type']));
                $this->tpl->setVariable("TITLE_LINK_NAME", $item['title']);
            } else {
                if (!$item['title'] &&
                    $item['type'] == 'sess') {
                    $app_info = ilSessionAppointment::_lookupAppointment(ilObject::_lookupObjId($item["ref_id"]));
                    $item['title'] = ilSessionAppointment::_appointmentToString($app_info['start'], $app_info['end'], $app_info['fullday']);
                }

                $this->tpl->setVariable("TITLE", $item['title']);
            }

            if (strlen($item['description'])) {
                $this->tpl->setCurrentBlock("item_description");
                $this->tpl->setVariable("DESC", $item['description']);
                $this->tpl->parseCurrentBlock();
            }

            if ($tlt = ilMDEducational::_getTypicalLearningTimeSeconds($item['obj_id'])) {
                $this->tpl->setCurrentBlock("tlt");
                $this->tpl->setVariable("TXT_TLT", $this->lng->txt('meta_typical_learning_time'));
                $this->tpl->setVariable("TLT_VAL", ilDatePresentation::secondsToString($tlt));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("container_standard_row");

            // Suggested
            if (is_array($_POST['item']["$item[ref_id]"]['sug_start'])) {
                $start = $this->__toUnix($_POST['item']["$item[ref_id]"]['sug_start']);
            } else {
                $start = $item['suggestion_start'];
            }
            $end = $item['suggestion_end'];
            $date = $this->__prepareDateSelect($start);
            $this->tpl->setVariable(
                "SUG_START",
                self::makeDateSelect(
                    $item_prefix . "[sug_start]",
                    $date['y'],
                    $date['m'],
                    $date['d'],
                    date('Y', time()),
                    false
                )
            );

            $this->tpl->setVariable("NAME_DURATION_A", $item_prefix . "[duration_a]");
            if (isset($_POST['item']["$item[ref_id]"]['duration_a'])) {
                $this->tpl->setVariable("VAL_DURATION_A", abs($_POST['item']["$item[ref_id]"]['duration_a']));
            } else {
                $this->tpl->setVariable("VAL_DURATION_A", intval(($end - $start) / (60 * 60 * 24)));
            }

            $this->tpl->setVariable('SUG_END', ilDatePresentation::formatDate(new ilDate($item['suggestion_end'], IL_CAL_UNIX)));


            $date = $this->__prepareDateSelect($end);
            $this->tpl->setVariable(
                "LIM_END",
                self::makeDateSelect(
                    $item_prefix . "[lim_end]",
                    $date['y'],
                    $date['m'],
                    $date['d'],
                    date('Y', time()),
                    false
                )
            );

            $this->tpl->setVariable("NAME_CHANGE", $item_change_prefix . "[change]");
            $this->tpl->setVariable("NAME_ACTIVE", $item_active_prefix . "[active]");

            if (isset($_POST['item'])) {
                $change = $_POST['item_change']["$item[ref_id]"]['change'];
                $active = $_POST['item_active']["$item[ref_id]"]['active'];
            } else {
                $change = $item['changeable'];
                $active = ($item['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING);
            }

            $this->tpl->setVariable("CHECKED_ACTIVE", $active ? 'checked="checked"' : '');
            $this->tpl->setVariable("CHECKED_CHANGE", $change ? 'checked="checked"' : '');

            if (isset($this->failed["$item[ref_id]"])) {
                $this->tpl->setVariable("ROWCLASS", 'tblrowmarked');
            } else {
                $this->tpl->setVariable("ROWCLASS", "");
            }
            $this->tpl->parseCurrentBlock();
        }

        // Select all
        $this->tpl->setVariable("SELECT_ALL", $this->lng->txt('select_all'));

        $this->tpl->setVariable("BTN_SAVE", $this->lng->txt('save'));
        $this->tpl->setVariable("BTN_CANCEL", $this->lng->txt('cancel'));
    }

    public function editUserTimings()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];

        if (!$ilAccess->checkAccess('read', '', $this->container_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->WARNING);
        }
        $this->tabs_gui->clearSubTabs();
        $this->tabs_gui->setTabActive('timings_timings');

        $_SESSION['crs_timings_user_hidden'] = isset($_GET['show_details']) ? $_GET['show_details'] : $_SESSION['crs_timings_user_hidden'];

        if (ilObjectActivation::hasChangeableTimings($this->course_obj->getRefId())) {
            $this->__editAdvancedUserTimings();
        } else {
            $this->__editUserTimings();
        }
    }

    public function returnToMembers()
    {
        $this->ctrl->returnToParent($this);
    }

    public function showUserTimings()
    {
        $this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.crs_user_timings.html', 'Modules/Course');
        $this->tabs_gui->clearSubTabs();
        $this->tabs_gui->setTabActive('members');

        if (!$_GET['member_id']) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->returnToParent($this);
        }


        // Back button
        $this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
        $this->tpl->setCurrentBlock("btn_cell");
        $this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, 'returnToMembers'));
        $this->tpl->setVariable("BTN_TXT", $this->lng->txt("back"));
        $this->tpl->parseCurrentBlock();


        $this->tpl->setVariable("HEADER_IMG", ilUtil::getImagePath('icon_usr.svg'));
        $this->tpl->setVariable("HEADER_ALT", $this->lng->txt('obj_usr'));
        $this->tpl->setVariable("TABLE_HEADER", $this->lng->txt('timings_of'));
        $name = ilObjUser::_lookupName($_GET['member_id']);
        $this->tpl->setVariable("USER_NAME", $name['lastname'] . ', ' . $name['firstname']);

        $this->tpl->setVariable("TXT_TITLE", $this->lng->txt('title'));
        $this->tpl->setVariable("TXT_START_END", $this->lng->txt('crs_timings_short_start_end'));
        $this->tpl->setVariable("TXT_INFO_START_END", $this->lng->txt('crs_timings_start_end_info'));
        $this->tpl->setVariable("TXT_CHANGED", $this->lng->txt('crs_timings_changed'));
        $this->tpl->setVariable("TXT_OWN_PRESETTING", $this->lng->txt('crs_timings_planed_start'));
        $this->tpl->setVariable("TXT_INFO_OWN_PRESETTING", $this->lng->txt('crs_timings_from_until'));

        $items = ilObjectActivation::getTimingsAdministrationItems($this->course_obj->getRefId());
        foreach ($items as $item) {
            if (($item['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) or
               ilObjectActivation::hasChangeableTimings($item['ref_id'])) {
                $this->__renderUserItem($item, 0);
            }
        }
    }

    public function __renderUserItem($item, $level)
    {
        $this->lng->loadLanguageModule('meta');

        $usr_planed = new ilTimingUser($item['ref_id'], (int) $_GET['member_id']);

        for ($i = 0;$i < $level;$i++) {
            $this->tpl->touchBlock('start_indent');
            $this->tpl->touchBlock('end_indent');
        }
        if (strlen($item['description'])) {
            $this->tpl->setCurrentBlock("item_description");
            $this->tpl->setVariable("DESC", $item['description']);
            $this->tpl->parseCurrentBlock();
        }
        if ($tlt = ilMDEducational::_getTypicalLearningTimeSeconds($item['obj_id'])) {
            $this->tpl->setCurrentBlock("tlt");
            $this->tpl->setVariable("TXT_TLT", $this->lng->txt('meta_typical_learning_time'));
            $this->tpl->setVariable("TLT_VAL", ilDatePresentation::secondsToString($tlt));
            $this->tpl->parseCurrentBlock();
        }

        if (!$item['title'] &&
            $item['type'] == 'sess') {
            $app_info = ilSessionAppointment::_lookupAppointment(ilObject::_lookupObjId($item["ref_id"]));
            $item['title'] = ilSessionAppointment::_appointmentToString($app_info['start'], $app_info['end'], $app_info['fullday']);
        }

        $this->tpl->setCurrentBlock("title_plain");
        $this->tpl->setVariable("TITLE", $item['title']);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("container_standard_row");

        $this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon($item['obj_id'], 'tiny', $item['type']));
        $this->tpl->setVariable("TYPE_ALT_IMG", $this->lng->txt('obj_' . $item['type']));

        if ($item['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) {
            if ($usr_planed->getStart()->get(IL_CAL_UNIX)) {
                $this->tpl->setVariable('SUG_START', $usr_planed->getStart()->get(IL_CAL_DATE));
            }
            if ($usr_planed->getEnd()->get(IL_CAL_UNIX)) {
                $this->tpl->setVariable('SUG_END', $usr_planed->getEnd()->get(IL_CAL_DATE));
            }
        }


        if (0 and $item['changeable'] and $item['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) {
            if ($usr_planed->getPlanedStartingTime()) {
                $start = $usr_planed->getPlanedStartingTime();
            } else {
                $start = $item['suggestion_start'];
            }
            $this->tpl->setVariable('OWN_START', ilDatePresentation::formatDate(new ilDate($start, IL_CAL_UNIX)));

            if ($usr_planed->getPlanedEndingTime() and 0) {
                $end = $usr_planed->getPlanedEndingTime();
            } else {
                $end = $item['suggestion_end'];
            }
            if ($start != $item['suggestion_start'] or $end != $item['suggestion_end']) {
                $this->tpl->setVariable("OK_IMG", ilUtil::getImagePath('icon_ok.svg'));
                $this->tpl->setVariable("OK_ALT", $this->lng->txt('crs_timings_changed'));
            } else {
                $this->tpl->setVariable("OK_IMG", ilUtil::getImagePath('icon_not_ok.svg'));
                $this->tpl->setVariable("OK_ALT", $this->lng->txt('crs_timings_not_changed'));
            }
            $this->tpl->setVariable('OWN_END', ilDatePresentation::formatDate(new ilDate($end, IL_CAL_UNIX)));
        }

        $this->tpl->parseCurrentBlock();

        foreach (ilObjectActivation::getTimingsAdministrationItems($item['ref_id']) as $item_data) {
            if (($item_data['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) or
               ilObjectActivation::hasChangeableTimings($item_data['ref_id'])) {
                $this->__renderUserItem($item_data, $level + 1);
            }
        }
    }



    public function __editAdvancedUserTimings()
    {
        $this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.crs_usr_edit_timings_adv.html', 'Modules/Course');

        $this->tabs_gui->clearSubTabs();

        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("HEADER_IMG", ilUtil::getImagePath('icon_crs.svg'));
        $this->tpl->setVariable("HEADER_ALT", $this->lng->txt('obj_crs'));
        $this->tpl->setVariable("BLOCK_HEADER_CONTENT", $this->lng->txt('timings_usr_edit'));

        if (!$_SESSION['crs_timings_user_hidden']) {
            $this->tpl->setVariable("SHOW_HIDE_TEXT", $this->lng->txt('show_details'));
            $this->ctrl->setParameter($this, 'show_details', 1);
            $this->tpl->setVariable("SHOW_HIDE_LINK", $this->ctrl->getLinkTarget($this, 'editUserTimings'));
        } else {
            $this->tpl->setVariable("SHOW_HIDE_TEXT", $this->lng->txt('hide_details'));
            $this->ctrl->setParameter($this, 'show_details', 0);
            $this->tpl->setVariable("SHOW_HIDE_LINK", $this->ctrl->getLinkTarget($this, 'editUserTimings'));
        }
        $this->ctrl->clearParameters($this);
        $this->tpl->setVariable("TXT_TITLE", $this->lng->txt('title'));
        $this->tpl->setVariable("TXT_START_END", $this->lng->txt('crs_timings_short_start_end'));
        $this->tpl->setVariable("TXT_INFO_START_END", $this->lng->txt('crs_timings_start_end_info'));


        $this->tpl->setVariable("TXT_OWN_PRESETTING", $this->lng->txt('crs_timings_planed_start'));
        $this->tpl->setVariable("TXT_INFO_OWN_PRESETTING", $this->lng->txt('crs_timings_start_end_info'));

        $this->tpl->setVariable("TXT_DURATION", $this->lng->txt('crs_timings_time_frame'));
        $this->tpl->setVariable("TXT_INFO_DURATION", $this->lng->txt('crs_timings_in_days'));

        $this->tpl->setVariable("TXT_BTN_UPDATE", $this->lng->txt('save'));
        $this->tpl->setVariable("TXT_CANCEL", $this->lng->txt('cancel'));

        $sorted_items = ilObjectActivation::getTimingsItems($this->course_obj->getRefId());

        $this->counter = 0;
        foreach ($sorted_items as $item) {
            switch ($item['type']) {
                case 'itgr':
                    break;

                default:
                    $this->__renderItem($item, 0);
                    break;
            }
        }
    }

    public function __editUserTimings()
    {
        $this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.crs_usr_edit_timings.html', 'Modules/Course');

        $this->tabs_gui->clearSubTabs();

        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("HEADER_IMG", ilUtil::getImagePath('icon_crs.svg'));
        $this->tpl->setVariable("HEADER_ALT", $this->lng->txt('obj_crs'));

        if (!$_SESSION['crs_timings_user_hidden']) {
            $this->tpl->setVariable("SHOW_HIDE_TEXT", $this->lng->txt('show_details'));
            $this->ctrl->setParameter($this, 'show_details', 1);
            $this->tpl->setVariable("SHOW_HIDE_LINK", $this->ctrl->getLinkTarget($this, 'editUserTimings'));
        } else {
            $this->tpl->setVariable("SHOW_HIDE_TEXT", $this->lng->txt('hide_details'));
            $this->ctrl->setParameter($this, 'show_details', 0);
            $this->tpl->setVariable("SHOW_HIDE_LINK", $this->ctrl->getLinkTarget($this, 'editUserTimings'));
        }
        $this->ctrl->clearParameters($this);

        $this->tpl->setVariable("BLOCK_HEADER_CONTENT", $this->lng->txt('timings_timings'));
        $this->tpl->setVariable("TXT_TITLE", $this->lng->txt('title'));
        $this->tpl->setVariable("TXT_START", $this->lng->txt('crs_timings_sug_begin'));
        $this->tpl->setVariable("TXT_END", $this->lng->txt('crs_timings_sug_end'));

        $sorted_items = ilObjectActivation::getTimingsItems($this->course_obj->getRefId());

        $this->counter = 0;
        foreach ($sorted_items as $item) {
            switch ($item['type']) {
                case 'itgr':
                    break;

                default:
                    $this->__renderItem($item, 0);
                    break;
            }
        }
    }

    public function __renderItem($item, $level)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];


        if (!$ilAccess->checkAccess('visible', '', $item['ref_id'])) {
            return false;
        }

        $this->lng->loadLanguageModule('meta');

        $usr_planed = new ilTimingPlaned($item['ref_id'], $ilUser->getId());

        for ($i = 0;$i < $level;$i++) {
            $this->tpl->touchBlock('start_indent');
            $this->tpl->touchBlock('end_indent');
        }
        if (strlen($item['description'])) {
            $this->tpl->setCurrentBlock("item_description");
            $this->tpl->setVariable("DESC", $item['description']);
            $this->tpl->parseCurrentBlock();
        }
        if ($tlt = ilMDEducational::_getTypicalLearningTimeSeconds($item['obj_id'])) {
            $this->tpl->setCurrentBlock("tlt");
            $this->tpl->setVariable("TXT_TLT", $this->lng->txt('meta_typical_learning_time'));
            $this->tpl->setVariable("TLT_VAL", ilDatePresentation::secondsToString($tlt));
            $this->tpl->parseCurrentBlock();
        }

        if (!$item['title'] &&
            $item['type'] == 'sess') {
            $app_info = ilSessionAppointment::_lookupAppointment(ilObject::_lookupObjId($item["ref_id"]));
            $item['title'] = ilSessionAppointment::_appointmentToString($app_info['start'], $app_info['end'], $app_info['fullday']);
        }

        if ($ilAccess->checkAccess('read', '', $item['ref_id'])) {
            $this->tpl->setCurrentBlock("title_as_link");
            $this->tpl->setVariable("TITLE_LINK", ilLink::_getLink($item['ref_id'], $item['type']));
            $this->tpl->setVariable("TITLE_NAME", $item['title']);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("title_plain");
            $this->tpl->setVariable("TITLE", $item['title']);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setCurrentBlock("container_standard_row");

        if (isset($this->invalid["$item[ref_id]"])) {
            $this->tpl->setVariable("ROWCLASS", 'tblrowmarked');
        }

        $this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon($item['obj_id'], 'small', $item['type']));
        $this->tpl->setVariable("TYPE_ALT_IMG", $this->lng->txt('obj_' . $item['type']));


        if ($item['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) {
            $this->tpl->setVariable('SUG_START', ilDatePresentation::formatDate(new ilDate($item['suggestion_start'], IL_CAL_UNIX)));
            $this->tpl->setVariable('SUG_END', ilDatePresentation::formatDate(new ilDate($item['suggestion_end'], IL_CAL_UNIX)));
        }

        if ($item['changeable']) {
            $item_prefix = "item[" . $item['ref_id'] . ']';

            if (is_array($_POST['item']["$item[ref_id]"]['own_start'])) {
                #echo "Start post<br>";
                $start = $this->__toUnix($_POST['item']["$item[ref_id]"]['own_start']);
            } elseif ($usr_planed->getPlanedStartingTime()) {
                #echo "Own start<br>";
                $start = $usr_planed->getPlanedStartingTime();
            } else {
                #echo "Empfehlung start<br>";
                $start = $item['suggestion_start'];
            }

            $date = $this->__prepareDateSelect($start);
            $this->tpl->setVariable(
                "OWN_START",
                self::makeDateSelect(
                    $item_prefix . "[own_start]",
                    $date['y'],
                    $date['m'],
                    $date['d'],
                    date('Y', time()),
                    false
                )
            );

            if ($usr_planed->getPlanedEndingTime()) {
                #echo "Own End<br>";
                $end = $usr_planed->getPlanedEndingTime();
            } else {
                #echo "Empfehlung end<br>";
                $end = $item['suggestion_end'];
            }
            $this->tpl->setVariable('OWN_END', ilDatePresentation::formatDate(new ilDate($end, IL_CAL_UNIX)));
            $this->tpl->setVariable("NAME_DURATION", $item_prefix . "[duration]");

            // Duration
            if (isset($_POST['item']["$item[ref_id]"]['duration'])) {
                $this->tpl->setVariable("VAL_DURATION", $_POST['item']["$item[ref_id]"]['duration']);
            } else {
                $this->tpl->setVariable("VAL_DURATION", intval(($end - $start) / (60 * 60 * 24)));
            }
        }

        $this->tpl->parseCurrentBlock();

        if (!$_SESSION['crs_timings_user_hidden']) {
            return true;
        }

        foreach (ilObjectActivation::getTimingsItems($item['ref_id']) as $item_data) {
            $this->__renderItem($item_data, $level + 1);
        }
    }


    public function updateUserTimings()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $this->tabs_gui->clearSubTabs();

        // Validate
        $this->invalid = array();
        foreach ($_POST['item'] as $ref_id => $data) {
            $tmp_planed = new ilTimingPlaned($ref_id, $ilUser->getId());

            $tmp_planed->setPlanedStartingTime($this->__toUnix($data['own_start']));
            if (isset($data['duration'])) {
                $data['own_start']['d'] += $data['duration'];
                $tmp_planed->setPlanedEndingTime($this->__toUnix($data['own_start'], array('h' => 23,'m' => 55)));
            } else {
                $tmp_planed->setPlanedEndingTime($this->__toUnix($data['own_start']), array('h' => 23,'m' => 55));
            }
            if (!$tmp_planed->validate()) {
                $this->invalid[$ref_id] = $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($ref_id));
            }
            $all_items[] = $tmp_planed;
        }
        if (count($this->invalid)) {
            $message = $this->lng->txt('crs_timings_update_error');
            $message .= ("<br />" . $this->lng->txt('crs_materials') . ': ');
            $message .= (implode(',', $this->invalid));
            $this->main_tpl->setOnScreenMessage('failure', $message);
            $this->editUserTimings();
            return false;
        }
        foreach ($all_items as $new_item_obj) {
            $new_item_obj->update();
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->editUserTimings();
        return true;
    }


    public function &__loadFromPost(&$item)
    {
        $obj_id = $item['obj_id'];

        if (!isset($_POST['item'][$obj_id])) {
            return $item;
        }
        $item['suggestion_start'] = $this->__toUnix($_POST['item'][$obj_id]['sug_start']);
        if (isset($_POST['item'][$obj_id]['sug_end'])) {
            // #9325
            $item['suggestion_end'] = $this->__toUnix($_POST['item'][$obj_id]['sug_end']);
        }
        $item['changeable'] = $_POST['item'][$obj_id]['change'];
        $item['timing_type'] = $_POST['item'][$obj_id]['active'] ? ilObjectActivation::TIMINGS_PRESETTING : $item['timing_type'];
        $item['duration_a'] = $_POST['item'][$obj_id]['duration_a'];
        $item['duration_b'] = $_POST['item'][$obj_id]['duration_b'];

        return $item;
    }

    /**
     * @return bool
     * @throws ilDateTimeException
     */
    protected function updateManagedTimings()
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];

        if (!$ilAccess->checkAccess('write', '', $this->container_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_write'), $ilErr->WARNING);
        }

        $this->tabs_gui->clearSubTabs();

        $failed = array();
        $all_items = array();
        foreach ((array) $_POST['item'] as $ref_id => $data) {
            $item_obj = new ilObjectActivation();
            $item_obj->read($ref_id);

            $item_obj->setTimingType($data['active'] ? 	ilObjectActivation::TIMINGS_PRESETTING : ilObjectActivation::TIMINGS_DEACTIVATED);
            #$item_obj->setTimingStart($old_data['timing_start']);
            #$item_obj->setTimingEnd($old_data['timing_end']);
            #$item_obj->toggleVisible($old_data['visible']);
            $item_obj->toggleChangeable((int) $data['change']);

            if ($this->course_obj->getTimingMode() == ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE) {
                $sug_start_dt = ilCalendarUtil::parseIncomingDate($data['sug_start']);
                $sug_end_dt = ilCalendarUtil::parseIncomingDate($data['sug_end']);


                if (($sug_start_dt instanceof ilDate) and ($sug_end_dt instanceof ilDate)) {
                    if (ilDateTime::_after($sug_start_dt, $sug_end_dt)) {
                        $failed[$ref_id] = 'crs_timing_err_start_end';
                        continue;
                    }
                    $item_obj->setSuggestionStart($sug_start_dt->get(IL_CAL_UNIX));
                    $item_obj->setSuggestionEnd($sug_end_dt->get(IL_CAL_UNIX));
                } else {
                    $failed['ref_id'] = 'crs_timing_err_valid_dates';
                    continue;
                }
            } else {
                if (
                    ((int) $data['sug_start_rel'] < 0) or
                    ((int) $data['duration_a'] < 0)
                ) {
                    $failed[$ref_id] = 'crs_timing_err_start_dur_rel';
                    continue;
                }
                $item_obj->setSuggestionStartRelative($data['sug_start_rel']);
                $item_obj->setSuggestionEndRelative($data['sug_start_rel'] + $data['duration_a']);

                // add default values for start/end (relative to now)
                $start = new ilDate(time(), IL_CAL_UNIX);
                $start->increment(IL_CAL_DAY, $data['sug_start_rel']);
                $item_obj->setSuggestionStart($start->get(IL_CAL_UNIX));

                $start->increment(IL_CAL_DAY, $data['duration_a']);
                $item_obj->setSuggestionEnd($start->get(IL_CAL_UNIX));
            }

            $item_obj->update($ref_id);
        }
        if (!$failed) {
            // update course => create calendar entries
            $this->course_obj->update();

            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
            $this->manageTimings();

            return true;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->manageTimings($failed);
            return true;
        }
    }

    public function updateTimings()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];

        if (!$ilAccess->checkAccess('write', '', $this->container_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_write'), $ilErr->WARNING);
        }
        $this->failed = array();
        // Validate

        $_POST['item'] = is_array($_POST['item']) ? $_POST['item'] : array();
        $all_items = array();

        foreach ($_POST['item'] as $ref_id => $data) {
            $item_obj = new ilObjectActivation();
            $old_data = ilObjectActivation::getItem($ref_id);

            $item_obj->setTimingType($_POST['item_active'][$ref_id]['active'] ?
                ilObjectActivation::TIMINGS_PRESETTING :
                ilObjectActivation::TIMINGS_DEACTIVATED);
            $item_obj->setTimingStart($old_data['timing_start']);
            $item_obj->setTimingEnd($old_data['timing_end']);
            $item_obj->setSuggestionStart($this->__toUnix($data["sug_start"]));

            // add duration
            $data['sug_start']['d'] += abs($data['duration_a']);
            $item_obj->setSuggestionEnd($this->__toUnix($data['sug_start'], array('h' => 23,'m' => 55)));
            $item_obj->toggleVisible($old_data['visible']);
            $item_obj->toggleChangeable($_POST['item_change'][$ref_id]['change']);

            if (!$item_obj->validateActivation()) {
                $this->failed[$ref_id] = $old_data['title'];
            }
            $all_items[$ref_id] = &$item_obj;
            unset($item_obj);
        }

        if (count($this->failed)) {
            $message = $this->lng->txt('crs_timings_update_error');
            $message .= ("<br />" . $this->lng->txt('crs_materials') . ': ');
            $message .= (implode(',', $this->failed));
            $this->main_tpl->setOnScreenMessage('failure', $message);
            $this->editTimings();
            return false;
        }

        // No do update
        foreach ($all_items as $ref_id => $item_obj_new) {
            $item_obj_new->update($ref_id);
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->editTimings();
        return false;
    }

    public function __setSubTabs()
    {
        if ($this->container_obj->getType() == 'crs') {
            $this->container_gui->setContentSubTabs();
        }
    }

    public function __initCourseObject()
    {
        global $DIC;

        $tree = $DIC['tree'];

        if ($this->container_obj->getType() == 'crs') {
            // Container is course
            $this->course_obj = &$this->container_obj;
        } else {
            $course_ref_id = $tree->checkForParentType($this->container_obj->getRefId(), 'crs');
            $this->course_obj = ilObjectFactory::getInstanceByRefId($course_ref_id);
        }
        return true;
    }

    public function __toUnix($date, $time = array())
    {
        return gmmktime($time['h'], $time['m'], 0, $date['m'], $date['d'], $date['y']);
    }

    public function __prepareDateSelect($a_unix_time)
    {
        return array('y' => date('Y', $a_unix_time),
                     'm' => date('m', $a_unix_time),
                     'd' => date('d', $a_unix_time));
    }

    public function __prepareTimeSelect($a_unix_time)
    {
        return array('h' => date('G', $a_unix_time),
                     'm' => date('i', $a_unix_time),
                     's' => date('s', $a_unix_time));
    }


    public function __buildPath($a_ref_id)
    {
        global $DIC;

        $tree = $DIC['tree'];

        $path_arr = $tree->getPathFull($a_ref_id, $this->course_obj->getRefId());
        $counter = 0;
        foreach ($path_arr as $data) {
            if ($counter++) {
                $path .= " -> ";
            }
            $path .= $data['title'];
        }

        return $path;
    }
} // END class.ilCourseContentGUI
