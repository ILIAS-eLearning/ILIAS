<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");
include_once("./Services/Tracking/classes/class.ilLPStatus.php");

/**
* TableGUI class for learning progress
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilLPObjectStatisticsLPTableGUI: ilFormPropertyDispatchGUI
* @ingroup ServicesTracking
*/
class ilLPObjectStatisticsLPTableGUI extends ilLPTableBaseGUI
{
    protected $types = array("min", "avg", "max");
    protected $status = array(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM,
        ilLPStatus::LP_STATUS_IN_PROGRESS_NUM,
        ilLPStatus::LP_STATUS_COMPLETED_NUM,
        ilLPStatus::LP_STATUS_FAILED_NUM);
    protected $is_chart = false;
    protected $is_details = false;
    protected $chart_data = array();
    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, array $a_preselect = null, $a_load_items = true, $a_is_chart = false, $a_is_details = false)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->preselected = $a_preselect;
        $this->is_chart = (bool) $a_is_chart;
        $this->is_details = (bool) $a_is_details;

        $this->setId("lpobjstatlptbl");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        if (!$this->is_details) {
            $this->setShowRowsSelector(true);
            // $this->setLimit(ilSearchSettings::getInstance()->getMaxHits());
            
            $this->addColumn("", "", "1%", true);
            $this->addColumn($lng->txt("trac_title"), "title");
            $this->addColumn($lng->txt("object_id"), "obj_id");
        } else {
            $this->setLimit(20);
            
            $this->addColumn($lng->txt("trac_figure"));
        }
                        
        $this->initFilter();
        
        if (strpos($this->filter["yearmonth"], "-") === false) {
            foreach ($this->getMonthsYear($this->filter["yearmonth"]) as $num => $caption) {
                $this->addColumn($caption, "month_" . $num);
            }
        } else {
            foreach ($this->types as $type) {
                if ($type != "avg") {
                    $caption = " " . $this->lng->txt("trac_object_stat_lp_" . $type);
                } else {
                    $caption = " &#216;";
                }
                $this->addColumn($lng->txt("trac_members_short") . $caption, "mem_cnt_" . $type);
            }
            
            include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
            foreach ($this->status as $status) {
                $path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
                $text = ilLearningProgressBaseGUI::_getStatusText($status);
                $icon = ilUtil::img($path, $text);
                
                foreach ($this->types as $type) {
                    if ($type != "avg") {
                        $caption = $icon . $this->lng->txt("trac_object_stat_lp_" . $type);
                    } else {
                        $caption = $icon . " &#216;";
                    }
                    $this->addColumn($caption, $status . "_" . $type);
                }
            }
        }
        
        if (!$this->is_details) {
            $this->setTitle($this->lng->txt("trac_object_stat_lp"));

            // $this->setSelectAllCheckbox("item_id");
            $this->addMultiCommand("showLearningProgressGraph", $lng->txt("trac_show_graph"));
            $this->setResetCommand("resetLearningProgressFilter");
            $this->setFilterCommand("applyLearningProgressFilter");
        }
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.lp_object_statistics_lp_row.html", "Services/Tracking");
        $this->setEnableHeader(true);
        $this->setEnableNumInfo(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        
        $this->status_map = array(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => "not_attempted",
            ilLPStatus::LP_STATUS_IN_PROGRESS_NUM => "in_progress",
            ilLPStatus::LP_STATUS_COMPLETED_NUM => "completed",
            ilLPStatus::LP_STATUS_FAILED_NUM => "failed");

        if ($a_load_items) {
            if ($this->is_details) {
                $this->getDetailItems($this->preselected[0]);
            } else {
                $this->initLearningProgressDetailsLayer();
                $this->getItems();
            }
        }
    }
    
    public function numericOrdering($a_field)
    {
        if ($a_field != "title") {
            return true;
        }
        return false;
    }
    
    /**
    * Init filter
    */
    public function initFilter()
    {
        global $DIC;

        $lng = $DIC['lng'];

        $this->setDisableFilterHiding(true);

        // object type selection
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        /*
        $si = new ilSelectInputGUI($lng->txt("obj_type"), "type");
        $options = $this->getPossibleTypes(true);
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        if(!$si->getValue())
        {
            $si->setValue("crs");
        }
        $this->filter["type"] = $si->getValue();

        $this->filter_captions[] = $options[$this->filter["type"]];
        */
        $this->filter["type"] = "crs";
        
        
        // title/description
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new ilTextInputGUI($lng->txt("trac_title_description"), "query");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["query"] = $ti->getValue();
        
        // year/month
        $si = new ilSelectInputGUI($lng->txt("year") . " / " . $lng->txt("month"), "yearmonth");
        $si->setOptions($this->getMonthsFilter());
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue(date("Y-m"));
        }
        $this->filter["yearmonth"] = $si->getValue();
        
        if (!strpos($this->filter["yearmonth"], "-")) {
            $si = new ilSelectInputGUI($lng->txt("trac_figure"), "figure");
            $options = array(
                "mem_cnt_max" => $lng->txt("members") . " " . $lng->txt("trac_object_stat_lp_max"),
                "mem_cnt_avg" => $lng->txt("members") . " &#216;",
                // we are using the db column names here (not the lp constants)!
                "in_progress_max" => ilLearningProgressBaseGUI::_getStatusText(ilLPStatus::LP_STATUS_IN_PROGRESS_NUM) . " " . $lng->txt("trac_object_stat_lp_max"),
                "in_progress_avg" => ilLearningProgressBaseGUI::_getStatusText(ilLPStatus::LP_STATUS_IN_PROGRESS_NUM) . " &#216;");
            $si->setOptions($options);
            $this->addFilterItem($si);
            $si->readFromSession();
            if (!$si->getValue()) {
                $si->setValue("mem_cnt_max");
            }
            $this->filter["measure"] = $si->getValue();
        }
    
        if ($this->is_details) {
            $this->filters = array();
        }
    }
    
    public function getItems()
    {
        $data = array();
        $all_status = array_merge(array("mem_cnt"), $this->status);
        
        $objects = $this->searchObjects(
            $this->getCurrentFilter(true),
            "read",
            null,
            false
        );
        if ($objects) {
            $objects = array_keys($objects);
            
            include_once "Services/Tracking/classes/class.ilTrQuery.php";
                                                        
            $yearmonth = explode("-", $this->filter["yearmonth"]);
            if (sizeof($yearmonth) == 1) {
                foreach (ilTrQuery::getObjectLPStatistics($objects, $yearmonth[0]) as $item) {
                    $obj_id = $item["obj_id"];
                    if (!isset($data[$obj_id])) {
                        $data[$obj_id]["obj_id"] = $obj_id;
                        $data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
                    }
                            
                    $measure_type = substr($this->filter["measure"], -3);
                    $measure_field = substr($this->filter["measure"], 0, -4);
                    $value = $item[$measure_field . "_" . $measure_type];
                    $idx = $item["yyyy"] . "-" . str_pad($item["mm"], 2, "0", STR_PAD_LEFT);
                    $data[$obj_id]["month_" . $idx] = $value;
                }
                
                if ($this->is_chart) {
                    // get data for single days (used in chart display)
                    foreach (array_keys($this->getMonthsYear($yearmonth[0])) as $num) {
                        $num = (int) array_pop(explode("-", $num));
                        foreach (ilTrQuery::getObjectLPStatistics($objects, $yearmonth[0], $num, true) as $item) {
                            $idx = $yearmonth[0] .
                                "-" . str_pad($num, 2, "0", STR_PAD_LEFT) .
                                "-" . str_pad($item["dd"], 2, "0", STR_PAD_LEFT);
                            $this->chart_data[$item["obj_id"]][$idx] = $item;
                        }
                    }
                }
            } else {
                // get data aggregated for month
                foreach (ilTrQuery::getObjectLPStatistics($objects, $yearmonth[0], (int) $yearmonth[1]) as $item) {
                    $obj_id = $item["obj_id"];
                    if (!isset($data[$obj_id])) {
                        $data[$obj_id]["obj_id"] = $obj_id;
                        $data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
                        $this->initRow($data[$obj_id]);
                    }
                    
                    foreach ($all_status as $status) {
                        // status-id to field name
                        if (is_numeric($status)) {
                            $field = $this->status_map[$status];
                        } else {
                            $field = $status;
                        }
                        
                        // aggregated fields
                        foreach ($this->types as $type) {
                            $value = $item[$field . "_" . $type];
                            $data[$obj_id][$status . "_" . $type] = $value;
                        }
                    }
                }
                
                if ($this->is_chart) {
                    // get data for single days (used in chart display)
                    foreach (ilTrQuery::getObjectLPStatistics($objects, $yearmonth[0], (int) $yearmonth[1], true) as $item) {
                        $this->chart_data[$item["obj_id"]][$item["dd"]] = $item;
                    }
                }
            }
            
            // add objects with no usage data
            foreach ($objects as $obj_id) {
                if (!isset($data[$obj_id])) {
                    $data[$obj_id]["obj_id"] = $obj_id;
                    $data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
                }
            }
        }
        
        $this->setData($data);
        
        include_once "./Services/Link/classes/class.ilLink.php";
    }
    
    protected function getDetailItems($a_obj_id)
    {
        $data = array();
        $all_status = array_merge(array("mem_cnt"), $this->status);
    
        include_once "Services/Tracking/classes/class.ilTrQuery.php";
        foreach (ilTrQuery::getObjectLPStatistics(array($a_obj_id), $this->filter["yearmonth"]) as $item) {
            $month = "month_" . $item["yyyy"] . "-" . str_pad($item["mm"], 2, "0", STR_PAD_LEFT);
            
            foreach ($all_status as $status) {
                // status-id to field name
                if ($status != "mem_cnt") {
                    $field = $this->status_map[$status];
                } else {
                    $field = $status;
                }
                // aggregated fields
                foreach ($this->types as $type) {
                    $value = $item[$field . "_" . $type];
                    $idx = $item["yyyy"] . "-" . str_pad($item["mm"], 2, "0", STR_PAD_LEFT);
                    $data[$status . "_" . $type]["month_" . $idx] = $value;
                }
            }
        }
        
        // add captions
        foreach (array_keys($data) as $figure) {
            $status = substr($figure, 0, -4);
            $type = substr($figure, -3);
            
            if ($status != "mem_cnt") {
                $path = ilLearningProgressBaseGUI::_getImagePathForStatus((int) $status);
                $text = ilLearningProgressBaseGUI::_getStatusText((int) $status);
                $icon = ilUtil::img($path, $text);
                $text = $icon . " " . $text;
            } else {
                $text = $this->lng->txt("members");
            }
            if ($type != "avg") {
                $caption = $text . " " . $this->lng->txt("trac_object_stat_lp_" . $type);
            } else {
                $caption = $text . " &#216;";
            }
            $data[$figure]["figure"] = $caption;
        }
        
        $this->setData($data);
    }
    
    protected function initRow(&$a_row)
    {
        foreach ($this->types as $type) {
            $a_row["mem_cnt_" . $type] = null;
        }
        foreach ($this->status as $status) {
            foreach ($this->types as $type) {
                $a_row[$status . "_" . $type] = null;
            }
        }
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        if (!$this->is_details) {
            $type = ilObject::_lookupType($a_set["obj_id"]);
        
            // ajax details layer link
            if (strpos($this->filter["yearmonth"], "-") === false) {
                $ilCtrl->setParameter($this->parent_obj, "item_id", $a_set["obj_id"]);
                $url = $ilCtrl->getLinkTarget($this->parent_obj, "showLearningProgressDetails");
                $a_set["title"] .= " (<a href=\"#\" onclick=\"ilObjStat.showLPDetails(event, '" . $url . "');\">Details</a>)";
                $ilCtrl->setParameter($this->parent_obj, "item_id", "");
            }
            
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable("OBJ_ID", $a_set["obj_id"]);
            $this->tpl->setVariable("ICON_SRC", ilObject::_getIcon("", "tiny", $type));
            $this->tpl->setVariable("ICON_ALT", $this->lng->txt($type));
            $this->tpl->setVariable("TITLE_TEXT", $a_set["title"]);
            if ($this->preselected && in_array($a_set["obj_id"], $this->preselected)) {
                $this->tpl->setVariable("CHECKBOX_STATE", " checked=\"checked\"");
            }
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("details");
            $this->tpl->setVariable("TXT_FIGURE", $a_set["figure"]);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setCurrentBlock("item");
        
        if (strpos($this->filter["yearmonth"], "-") === false) {
            foreach (array_keys($this->getMonthsYear($this->filter["yearmonth"])) as $num) {
                $value = $this->anonymizeValue((int) $a_set["month_" . $num]);
                $this->tpl->setVariable("ITEM_VALUE", $value);
                $this->tpl->parseCurrentBlock();
            }
        } else {
            foreach ($this->types as $type) {
                $this->tpl->setVariable("ITEM_VALUE", $this->anonymizeValue((int) $a_set["mem_cnt_" . $type]));
                $this->tpl->parseCurrentBlock();
            }
            foreach ($this->status as $status) {
                foreach ($this->types as $type) {
                    $this->tpl->setVariable("ITEM_VALUE", $this->anonymizeValue((int) $a_set[$status . "_" . $type]));
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
    }

    public function getGraph(array $a_graph_items)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $a_graph_items = array(array_pop($a_graph_items));
        
        include_once "Services/Chart/classes/class.ilChart.php";
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, "objstlp");
        $chart->setsize(700, 500);
        
        $legend = new ilChartLegend();
        $chart->setLegend($legend);
        
        // needed for correct stacking
        $custom_order = array(
            ilLPStatus::LP_STATUS_IN_PROGRESS_NUM => array("#f7d408", "#fffa00"),
            ilLPStatus::LP_STATUS_FAILED_NUM => array("#cf0202", "#f15b5b"),
            ilLPStatus::LP_STATUS_COMPLETED_NUM => array("#17aa0e", "#6ce148"),
            ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => array("#a4a4a4", "#c4c4c4")
            );
        
        $chart->setColors(array());
        
        $max_value = 0;
        foreach ($this->chart_data as $object_id => $days) {
            if (in_array($object_id, $a_graph_items)) {
                $series = array();
                foreach ($custom_order as $status => $colors) {
                    /*
                    if(strpos($this->filter["yearmonth"], "-") === false)
                    {
                        $series[$status] = new ilChartData("lines");
                        $series[$status]->setLineSteps(true);
                    }
                    else
                    {
                        $series[$status] = new ilChartData("bars");
                        $series[$status]->setBarOptions(0.75);
                        $series[$status]->setFill(true, $colors[1]);
                    }
                    $series[$status]->setStackingId($object_id);
                    */
                    $series[$status] = $chart->getDataInstance(ilChartGrid::DATA_LINES);
                                        
                    $series[$status]->setLabel(ilLearningProgressBaseGUI::_getStatusText($status));
                    $chart_colors[] = $colors[0];
                }
                $chart->setColors($chart_colors);

                if (strpos($this->filter["yearmonth"], "-") === false) {
                    $x_axis = $this->lng->txt("month");
                    
                    $counter = 0;
                    foreach (array_keys($this->getMonthsYear($this->filter["yearmonth"])) as $month) {
                        for ($loop = 1; $loop < 32; $loop++) {
                            $item_day = $month . "-" . str_pad($loop, 2, "0", STR_PAD_LEFT);
                            foreach (array_keys($custom_order) as $status) {
                                if (isset($days[$item_day])) {
                                    // as there is only 1 entry per day, avg == sum
                                    $value = (int) $days[$item_day][$this->status_map[$status] . "_avg"];
                                } else {
                                    $value = 0;
                                }
                                $max_value = max($max_value, $value);
                                $value = $this->anonymizeValue($value, true);
                                $series[$status]->addPoint($counter, $value);
                            }
                            $counter++;
                        }
                    }
                } else {
                    $x_axis = $this->lng->txt("day");
                    for ($loop = 1; $loop < 32; $loop++) {
                        foreach (array_keys($custom_order) as $status) {
                            if (isset($days[$loop])) {
                                // as there is only 1 entry per day, avg == sum
                                $value = (int) $days[$loop][$this->status_map[$status] . "_avg"];
                            } else {
                                $value = 0;
                            }
                            $max_value = max($max_value, $value);
                            $value = $this->anonymizeValue($value, true);
                            $series[$status]->addPoint($loop, $value);
                        }
                    }
                }
                
                foreach (array_keys($custom_order) as $status) {
                    $chart->addData($series[$status]);
                }
            }
        }
        
        $value_ticks = $this->buildValueScale($max_value, true);
        
        $labels = array();
        if (strpos($this->filter["yearmonth"], "-") === false) {
            $counter = 0;
            foreach ($this->getMonthsYear($this->filter["yearmonth"], true) as $caption) {
                $labels[$counter] = $caption;
                $counter += 31;
            }
        } else {
            for ($loop = 1; $loop < 32; $loop++) {
                $labels[$loop] = $loop . ".";
            }
        }
        $chart->setTicks($labels, $value_ticks, true);
        
        return $chart->getHTML();
    }
            
    protected function initLearningProgressDetailsLayer()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        ilYuiUtil::initPanel();
        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
        iljQueryUtil::initjQuery();
        
        $tpl->addJavascript("./Services/Tracking/js/ilObjStat.js");
    }
}
