<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
* TableGUI class for learning progress
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilLPObjectStatisticsTableGUI: ilFormPropertyDispatchGUI
* @ingroup ServicesTracking
*/
class ilLPObjectStatisticsTableGUI extends ilLPTableBaseGUI
{
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, array $a_preselect = null, $a_load_items = true)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->preselected = $a_preselect;

        $this->setId("lpobjstattbl");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setShowRowsSelector(true);
        // $this->setLimit(ilSearchSettings::getInstance()->getMaxHits());
        $this->initFilter();
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($lng->txt("trac_title"), "title");
        $this->addColumn($lng->txt("object_id"), "obj_id");
        if (strpos($this->filter["yearmonth"], "-") === false) {
            foreach ($this->getMonthsYear($this->filter["yearmonth"]) as $num => $caption) {
                $this->addColumn($caption, "month_" . $num);
            }
        }
        $this->addColumn($lng->txt("total"), "total");

        $this->setTitle($this->lng->txt("trac_object_stat_access"));

        // $this->setSelectAllCheckbox("item_id");
        $this->addMultiCommand("showAccessGraph", $lng->txt("trac_show_graph"));
        $this->setResetCommand("resetAccessFilter");
        $this->setFilterCommand("applyAccessFilter");
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.lp_object_statistics_row.html", "Services/Tracking");
        $this->setEnableHeader(true);
        $this->setEnableNumInfo(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        
        $this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_CSV));
        
        if ($a_load_items) {
            $this->getItems();
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
        $si = new ilSelectInputGUI($lng->txt("obj_type"), "type");
        $si->setOptions($this->getPossibleTypes(true, false, true));
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue("crs");
        }
        $this->filter["type"] = $si->getValue();

        // title/description
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new ilTextInputGUI($lng->txt("trac_title_description"), "query");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["query"] = $ti->getValue();

        // read_count/spent_seconds
        $si = new ilSelectInputGUI($lng->txt("trac_figure"), "figure");
        $si->setOptions(array("read_count" => $lng->txt("trac_read_count"),
            "spent_seconds" => $lng->txt("trac_spent_seconds"),
            "users" => $lng->txt("users")));
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue("read_count");
        }
        $this->filter["measure"] = $si->getValue();

        // year/month
        $si = new ilSelectInputGUI($lng->txt("year") . " / " . $lng->txt("month"), "yearmonth");
        $si->setOptions($this->getMonthsFilter());
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue(date("Y-m"));
        }
        $this->filter["yearmonth"] = $si->getValue();
    }

    public function getItems()
    {
        $data = array();
        
        if ($this->filter["type"] != "prtf") {
            // JF, 2016-06-06
            $objects = $this->searchObjects($this->getCurrentFilter(true), "", null, false);
            
            if ($this->filter["type"] == "blog") {
                include_once './Services/Tracking/classes/class.ilTrQuery.php';
                foreach (ilTrQuery::getWorkspaceBlogs($this->filter["query"]) as $obj_id) {
                    $objects[$obj_id] = array($obj_id);
                }
            }
        } else {
            // portfolios are not part of repository
            include_once './Services/Tracking/classes/class.ilTrQuery.php';
            foreach (ilTrQuery::getPortfolios($this->filter["query"]) as $obj_id) {
                $objects[$obj_id] = array($obj_id);
            }
        }
        
        if ($objects) {
            $yearmonth = explode("-", $this->filter["yearmonth"]);
            if (sizeof($yearmonth) == 1) {
                include_once './Services/Tracking/classes/class.ilTrQuery.php';
                foreach (ilTrQuery::getObjectAccessStatistics($objects, $yearmonth[0]) as $obj_id => $months) {
                    $data[$obj_id]["obj_id"] = $obj_id;
                    $data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
                    
                    foreach ($months as $month => $values) {
                        $idx = $yearmonth[0] . "-" . str_pad($month, 2, "0", STR_PAD_LEFT);
                        $data[$obj_id]["month_" . $idx] = (int) $values[$this->filter["measure"]];
                        $data[$obj_id]["total"] += (int) $values[$this->filter["measure"]];
                    }
                }
            } else {
                include_once './Services/Tracking/classes/class.ilTrQuery.php';
                foreach (ilTrQuery::getObjectAccessStatistics($objects, $yearmonth[0], (int) $yearmonth[1]) as $obj_id => $days) {
                    $data[$obj_id]["obj_id"] = $obj_id;
                    $data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
                    
                    foreach ($days as $day => $values) {
                        $data[$obj_id]["day_" . $day] = (int) $values[$this->filter["measure"]];
                        $data[$obj_id]["total"] += (int) $values[$this->filter["measure"]];
                    }
                }
            }
            
            // add objects with no usage data
            foreach (array_keys($objects) as $obj_id) {
                if (!isset($data[$obj_id])) {
                    $data[$obj_id]["obj_id"] = $obj_id;
                    $data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
                }
            }
        }
        
        $this->setData($data);
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $type = ilObject::_lookupType($a_set["obj_id"]);

        $this->tpl->setVariable("OBJ_ID", $a_set["obj_id"]);
        $this->tpl->setVariable("ICON_SRC", ilObject::_getIcon("", "tiny", $type));
        $this->tpl->setVariable("ICON_ALT", $this->lng->txt($type));
        $this->tpl->setVariable("TITLE_TEXT", $a_set["title"]);
        
        if ($this->preselected && in_array($a_set["obj_id"], $this->preselected)) {
            $this->tpl->setVariable("CHECKBOX_STATE", " checked=\"checked\"");
        }

        $sum = 0;
        if (strpos($this->filter["yearmonth"], "-") === false) {
            $this->tpl->setCurrentBlock("month");
            foreach (array_keys($this->getMonthsYear($this->filter["yearmonth"])) as $num) {
                $value = (int) $a_set["month_" . $num];
                if ($this->filter["measure"] != "spent_seconds") {
                    $value = $this->anonymizeValue($value);
                } else {
                    $value = $this->formatSeconds($value, true);
                }
                $this->tpl->setVariable("MONTH_VALUE", $value);
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($this->filter["measure"] == "spent_seconds") {
            $sum = $this->formatSeconds((int) $a_set["total"], true);
        } else {
            $sum = $this->anonymizeValue((int) $a_set["total"]);
        }
        $this->tpl->setVariable("TOTAL", $sum);
    }

    public function getGraph(array $a_graph_items)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        include_once "Services/Chart/classes/class.ilChart.php";
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, "objstacc");
        $chart->setSize(700, 500);

        $legend = new ilChartLegend();
        $chart->setLegend($legend);

        $max_value = 0;
        foreach ($this->getData() as $object) {
            if (in_array($object["obj_id"], $a_graph_items)) {
                $series = $chart->getDataInstance(ilChartGrid::DATA_LINES);
                $series->setLabel(ilObject::_lookupTitle($object["obj_id"]));

                if (strpos($this->filter["yearmonth"], "-") === false) {
                    foreach (array_keys($this->getMonthsYear($this->filter["yearmonth"])) as $idx => $num) {
                        $value = (int) $object["month_" . $num];
                        $max_value = max($max_value, $value);
                        if ($this->filter["measure"] != "spent_seconds") {
                            $value = $this->anonymizeValue($value, true);
                        }
                        $series->addPoint($idx, $value);
                    }
                } else {
                    for ($loop = 1; $loop < 32; $loop++) {
                        $value = (int) $object["day_" . $loop];
                        $max_value = max($max_value, $value);
                        if ($this->filter["measure"] != "spent_seconds") {
                            $value = $this->anonymizeValue($value, true);
                        }
                        $series->addPoint($loop, $value);
                    }
                }

                $chart->addData($series);
            }
        }
        
        $value_ticks = $this->buildValueScale(
            $max_value,
            ($this->filter["measure"] != "spent_seconds"),
            ($this->filter["measure"] == "spent_seconds")
        );
        
        $labels = array();
        if (strpos($this->filter["yearmonth"], "-") === false) {
            foreach (array_values($this->getMonthsYear($this->filter["yearmonth"], true)) as $idx => $caption) {
                $labels[$idx] = $caption;
            }
        } else {
            for ($loop = 1; $loop < 32; $loop++) {
                $labels[$loop] = $loop . ".";
            }
        }
        $chart->setTicks($labels, $value_ticks, true);

        return $chart->getHTML();
    }
    
    protected function fillMetaExcel(ilExcel $a_excel, &$a_row)
    {
    }
    
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $a_set)
    {
        $a_excel->setCell($a_row, 0, ilObject::_lookupTitle($a_set["obj_id"]));
        $a_excel->setCell($a_row, 1, $a_set["obj_id"]);
            
        $col = 1;
        if (strpos($this->filter["yearmonth"], "-") === false) {
            foreach (array_keys($this->getMonthsYear($this->filter["yearmonth"])) as $num) {
                $value = (int) $a_set["month_" . $num];
                if ($this->filter["measure"] != "spent_seconds") {
                    $value = $this->anonymizeValue($value);
                }
                
                $a_excel->setCell($a_row, ++$col, $value);
            }
        }
        
        if ($this->filter["measure"] == "spent_seconds") {
            // keep seconds
            // $sum = $this->formatSeconds((int)$a_set["total"]);
            $sum = (int) $a_set["total"];
        } else {
            $sum = $this->anonymizeValue((int) $a_set["total"]);
        }
        $a_excel->setCell($a_row, ++$col, $sum);
    }
    
    protected function fillMetaCSV($a_csv)
    {
    }
    
    protected function fillRowCSV($a_csv, $a_set)
    {
        $a_csv->addColumn(ilObject::_lookupTitle($a_set["obj_id"]));
        $a_csv->addColumn($a_set["obj_id"]);
            
        if (strpos($this->filter["yearmonth"], "-") === false) {
            foreach (array_keys($this->getMonthsYear($this->filter["yearmonth"])) as $num) {
                $value = (int) $a_set["month_" . $num];
                if ($this->filter["measure"] != "spent_seconds") {
                    $value = $this->anonymizeValue($value);
                }
                
                $a_csv->addColumn($value);
            }
        }
        
        if ($this->filter["measure"] == "spent_seconds") {
            // keep seconds
            // $sum = $this->formatSeconds((int)$a_set["total"]);
            $sum = (int) $a_set["total"];
        } else {
            $sum = $this->anonymizeValue((int) $a_set["total"]);
        }
        $a_csv->addColumn($sum);
        
        $a_csv->addRow();
    }
}
