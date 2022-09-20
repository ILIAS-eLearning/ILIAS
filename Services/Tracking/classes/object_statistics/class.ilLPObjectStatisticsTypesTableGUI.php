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
 * TableGUI class for learning progress
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version      $Id$
 * @ilCtrl_Calls ilLPObjectStatisticsTypesTableGUI: ilFormPropertyDispatchGUI
 * @ingroup      ServicesTracking
 */
class ilLPObjectStatisticsTypesTableGUI extends ilLPTableBaseGUI
{
    protected ?array $preselected;

    /**
     * Constructor
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        array $a_preselect = null,
        $a_load_items = true
    ) {
        $this->preselected = $a_preselect;

        $this->setId("lpobjstattypetbl");
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->initFilter();
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("type"), "title");
        // #12788
        foreach ($this->getMonthsYear(
            $this->filter["year"]
        ) as $num => $caption) {
            $this->addColumn($caption, "month_" . $num);
        }
        if ($this->filter["year"] == date("Y")) {
            $this->addColumn($this->lng->txt("trac_current"), "month_live");
        }

        $this->setTitle($this->lng->txt("trac_object_stat_types"));

        // $this->setSelectAllCheckbox("item_id");
        $this->addMultiCommand(
            "showTypesGraph",
            $this->lng->txt("trac_show_graph")
        );
        $this->setResetCommand("resetTypesFilter");
        $this->setFilterCommand("applyTypesFilter");

        $this->setFormAction(
            $this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd)
        );
        $this->setRowTemplate(
            "tpl.lp_object_statistics_types_row.html",
            "Services/Tracking"
        );
        $this->setEnableHeader(true);
        $this->setEnableNumInfo(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->setLimit(9999);

        $this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_CSV));

        if ($a_load_items) {
            $this->getItems();
        }
    }

    public function numericOrdering(string $a_field): bool
    {
        if ($a_field != "title") {
            return true;
        }
        return false;
    }

    /**
     * Init filter
     */
    public function initFilter(): void
    {
        $this->setDisableFilterHiding(true);

        // figure
        $si = new ilSelectInputGUI($this->lng->txt("trac_figure"), "figure");
        $options = array("objects" => $this->lng->txt("objects"),
                         "references" => $this->lng->txt("trac_reference"),
                         "deleted" => $this->lng->txt("trac_trash")
        );
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue("objects");
        }
        $this->filter["measure"] = $si->getValue();

        // aggregation
        $si = new ilSelectInputGUI(
            $this->lng->txt("trac_aggregation"),
            "aggregation"
        );
        $options = array();
        $options["max"] = $this->lng->txt(
            "trac_object_stat_lp_max"
        ) . " (" . $this->lng->txt("month") . ")";
        $options["avg"] = "&#216; (" . $this->lng->txt("month") . ")";
        $options["min"] = $this->lng->txt(
            "trac_object_stat_lp_min"
        ) . " (" . $this->lng->txt("month") . ")";
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue("max");
        }
        $this->filter["aggregation"] = $si->getValue();

        // year/month
        $si = new ilSelectInputGUI($this->lng->txt("year"), "year");
        $options = array();
        for ($loop = 0; $loop < 4; $loop++) {
            $year = date("Y") - $loop;
            $options[$year] = $year;
        }
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue(date("Y"));
        }
        $this->filter["year"] = $si->getValue();
    }

    public function getItems(): void
    {
        $res = ilTrQuery::getObjectTypeStatisticsPerMonth(
            $this->filter["aggregation"],
            $this->filter["year"]
        );

        $data = array();
        foreach ($res as $type => $months) {
            // inactive plugins, etc.
            if (!$this->objDefinition->getLocation($type)) {
                continue;
            }

            $data[$type]["type"] = $type;

            // to enable sorting by title
            if ($this->objDefinition->isPluginTypeName($type)) {
                $data[$type]["title"] = ilObjectPlugin::lookupTxtById(
                    $type,
                    "obj_" . $type
                );
                $data[$type]["icon"] = ilObject::_getIcon(0, "tiny", $type);
            } else {
                $data[$type]["title"] = $this->lng->txt("objs_" . $type);
                $data[$type]["icon"] = ilObject::_getIcon(0, "tiny", $type);
            }

            foreach ($months as $month => $row) {
                $value = '';
                if (isset($this->filter['measure']) && isset($row[$this->filter['measure']])) {
                    $value = $row[$this->filter["measure"]];
                }
                $data[$type]["month_" . $month] = $value;
            }
        }

        // add live data
        if ($this->filter["year"] == date("Y")) {
            $live = ilTrQuery::getObjectTypeStatistics();
            foreach ($live as $type => $item) {
                // inactive plugins, etc.
                if (!$this->objDefinition->getLocation($type)) {
                    continue;
                }

                $data[$type]["type"] = $type;

                // to enable sorting by title
                if ($this->objDefinition->isPluginTypeName($type)) {
                    $data[$type]["title"] = ilObjectPlugin::lookupTxtById(
                        $type,
                        "obj_" . $type
                    );
                    $data[$type]["icon"] = ilObject::_getIcon(0, "tiny", $type);
                } else {
                    $data[$type]["title"] = $this->lng->txt("objs_" . $type);
                    $data[$type]["icon"] = ilObject::_getIcon(
                        0,
                        "tiny",
                        $type
                    );
                }
                $value = '';
                if (isset($this->filter['measure']) && isset($item[$this->filter['measure']])) {
                    $value = $item[$this->filter["measure"]];
                }
                $data[$type]["month_live"] = $value;
            }
        }

        $this->setData($data);
    }

    /**
     * Fill table row
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("ICON_SRC", $a_set["icon"]);
        $this->tpl->setVariable(
            "ICON_ALT",
            $this->lng->txt("objs_" . $a_set["type"])
        );
        $this->tpl->setVariable("TITLE_TEXT", $a_set["title"]);
        $this->tpl->setVariable("OBJ_TYPE", $a_set["type"]);

        if ($this->preselected && in_array(
            $a_set["type"],
            $this->preselected
        )) {
            $this->tpl->setVariable("CHECKBOX_STATE", " checked=\"checked\"");
        }

        $this->tpl->setCurrentBlock("item");
        foreach (array_keys(
            $this->getMonthsYear($this->filter["year"])
        ) as $month) {
            $this->tpl->setVariable(
                "VALUE_ITEM",
                $this->anonymizeValue(
                    (int) ($a_set["month_" . $month] ?? 0)
                )
            );
            $this->tpl->parseCurrentBlock();
        }

        if ($this->filter["year"] == date("Y")) {
            $this->tpl->setVariable(
                "VALUE_ITEM",
                $this->anonymizeValue(
                    (int) ($a_set["month_live"] ?? 0)
                )
            );
            $this->tpl->parseCurrentBlock();
        }
    }

    public function getGraph(array $a_graph_items): string
    {
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, "objsttp");
        $chart->setSize("700", "500");

        $legend = new ilChartLegend();
        $chart->setLegend($legend);

        $types = array();
        foreach ($this->getData() as $id => $item) {
            $types[$id] = $item["title"];
        }

        $labels = [];
        foreach (array_values(
            $this->getMonthsYear($this->filter["year"], true)
        ) as $idx => $caption) {
            $labels[$idx + 1] = $caption;
        }
        $chart->setTicks($labels, false, true);

        foreach ($this->getData() as $type => $object) {
            if (in_array($type, $a_graph_items)) {
                $series = $chart->getDataInstance(ilChartGrid::DATA_LINES);
                $series->setLabel($types[$type]);

                foreach (array_keys(
                    $this->getMonthsYear($this->filter["year"])
                ) as $idx => $month) {
                    $series->addPoint(
                        $idx + 1,
                        (int) ($object["month_" . $month] ?? 0)
                    );
                }

                $chart->addData($series);
            }
        }

        return $chart->getHTML();
    }

    protected function fillMetaExcel(ilExcel $a_excel, int &$a_row): void
    {
    }

    protected function fillRowExcel(
        ilExcel $a_excel,
        int &$a_row,
        array $a_set
    ): void {
        $a_excel->setCell($a_row, 0, $a_set["title"]);

        $cnt = 1;
        foreach (array_keys(
            $this->getMonthsYear($this->filter["year"])
        ) as $month) {
            $value = $this->anonymizeValue((int) ($a_set["month_" . $month] ?? 0));
            $a_excel->setCell($a_row, $cnt++, $value);
        }

        $value = $this->anonymizeValue((int) ($a_set["month_live"] ?? 0));
        $a_excel->setCell($a_row, $cnt, $value);
    }

    protected function fillMetaCSV(ilCSVWriter $a_csv): void
    {
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set): void
    {
        $a_csv->addColumn($a_set["title"]);

        foreach (array_keys(
            $this->getMonthsYear($this->filter["year"])
        ) as $month) {
            $value = $this->anonymizeValue((int) ($a_set["month_" . $month] ?? 0));
            $a_csv->addColumn($value);
        }

        $value = $this->anonymizeValue((int) ($a_set["month_live"] ?? 0));
        $a_csv->addColumn($value);

        $a_csv->addRow();
    }
}
