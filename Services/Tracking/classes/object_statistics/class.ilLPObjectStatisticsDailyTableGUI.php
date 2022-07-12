<?php declare(strict_types=0);

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
 * @ilCtrl_Calls ilLPObjectStatisticsDailyTableGUI: ilFormPropertyDispatchGUI
 * @ingroup      ServicesTracking
 */
class ilLPObjectStatisticsDailyTableGUI extends ilLPTableBaseGUI
{
    protected ?array $preselected;

    /**
     * Constructor
     */
    public function __construct(
        ?object $a_parent_obj,
        string $a_parent_cmd,
        array $a_preselect = null,
        bool $a_load_items = true
    ) {
        $this->preselected = $a_preselect;

        $this->setId("lpobjstatdlytbl");
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function init() : void
    {
        $this->setShowRowsSelector(true);
        $this->initFilter();

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("trac_title"), "title");

        $all_columns = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col_name => $col_info) {
            $column_definition = $all_columns[$col_name];
            $this->addColumn(
                $column_definition['txt'],
                $column_definition['sortable'] ? $column_definition['field'] : '',
                $column_definition['width']
            );
        }
        for ($loop = 0; $loop < 24; $loop += 2) {
            $this->addColumn(
                str_pad($loop, 2, "0", STR_PAD_LEFT) . ":00-<br />" .
                str_pad((string) $loop, 2, "0", STR_PAD_LEFT) . ":00 ",
                "hour" . $loop
            );
        }
        $this->addColumn($this->lng->txt("total"), "sum");
        $this->setTitle($this->lng->txt("trac_object_stat_daily"));

        // $this->setSelectAllCheckbox("item_id");
        $this->addMultiCommand(
            "showDailyGraph",
            $this->lng->txt("trac_show_graph")
        );
        $this->setResetCommand("resetDailyFilter");
        $this->setFilterCommand("applyDailyFilter");

        $this->setFormAction(
            $this->ctrl->getFormAction(
                $this->getParentObject(),
                $this->getParentCmd()
            )
        );
        $this->setRowTemplate(
            "tpl.lp_object_statistics_daily_row.html",
            "Services/Tracking"
        );
        $this->setEnableHeader(true);
        $this->setEnableNumInfo(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_CSV));
    }

    public function getSelectableColumns() : array
    {
        $columns = [];
        $columns['obj_id'] = [
            'field' => 'obj_id',
            'txt' => $this->lng->txt('object_id'),
            'default' => false,
            'optional' => true,
            'sortable' => true,
            'width' => '5%'
        ];
        $columns['reference_ids'] = [
            'field' => 'reference_ids',
            'txt' => $this->lng->txt('trac_reference_ids_column'),
            'default' => false,
            'optional' => true,
            'sortable' => true,
            'width' => '5%'
        ];
        $columns['paths'] = [
            'field' => 'paths',
            'txt' => $this->lng->txt('trac_paths'),
            'default' => false,
            'optional' => true,
            'sortable' => false,
            'width' => '25%'
        ];
        return $columns;
    }


    public function numericOrdering(string $a_field) : bool
    {
        $alphabetic_ordering = [
            'title'
        ];
        if (in_array($a_field, $alphabetic_ordering)) {
            return true;
        }
        return false;
    }

    /**
     * Init filter
     */
    public function initFilter() : void
    {
        $this->setDisableFilterHiding(true);

        // object type selection
        $si = new ilSelectInputGUI($this->lng->txt("obj_type"), "type");
        $si->setOptions($this->getPossibleTypes(true, false, true));
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue("crs");
        }
        $this->filter["type"] = $si->getValue();

        // title/description
        $ti = new ilTextInputGUI(
            $this->lng->txt("trac_title_description"),
            "query"
        );
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["query"] = $ti->getValue();

        // read_count/spent_seconds
        $si = new ilSelectInputGUI($this->lng->txt("trac_figure"), "figure");
        $si->setOptions(
            array("read_count" => $this->lng->txt("trac_read_count"),
                  "spent_seconds" => $this->lng->txt("trac_spent_seconds")
            )
        );
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue("read_count");
        }
        $this->filter["measure"] = $si->getValue();

        // year/month
        $si = new ilSelectInputGUI(
            $this->lng->txt("year") . " / " . $this->lng->txt("month"),
            "yearmonth"
        );
        $si->setOptions($this->getMonthsFilter());
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue(date("Y-m"));
        }
        $this->filter["yearmonth"] = $si->getValue();
        $this->filter = $this->initRepositoryFilter($this->filter);
    }

    public function getItems() : void
    {
        $data = array();

        $objects = [];
        if ($this->filter["type"] != "prtf") {
            // JF, 2016-06-06
            $objects = $this->searchObjects(
                $this->getCurrentFilter(true),
                "",
                null,
                false
            );

            if ($this->filter["type"] == "blog") {
                foreach (ilTrQuery::getWorkspaceBlogs(
                    $this->filter["query"]
                ) as $obj_id) {
                    $objects[$obj_id] = array($obj_id);
                }
            }
        } else {
            // portfolios are not part of repository
            foreach (ilTrQuery::getPortfolios(
                $this->filter["query"]
            ) as $obj_id) {
                $objects[$obj_id] = array($obj_id);
            }
        }

        if ($objects) {
            $yearmonth = explode("-", $this->filter["yearmonth"]);
            if (sizeof($yearmonth) == 1) {
                $stat_objects = ilTrQuery::getObjectDailyStatistics(
                    $objects,
                    $yearmonth[0]
                );
            } else {
                $stat_objects = ilTrQuery::getObjectDailyStatistics(
                    $objects,
                    (string) $yearmonth[0],
                    (string) $yearmonth[1]
                );
            }

            foreach ($stat_objects as $obj_id => $hours) {
                $data[$obj_id]["obj_id"] = $obj_id;
                $data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
                $data[$obj_id]['reference_ids'] = $this->findReferencesForObjId($obj_id);

                foreach ($hours as $hour => $values) {
                    // table data
                    $hour_transf = floor($hour / 2) * 2;
                    $data[$obj_id]["hour" . $hour_transf] =
                        ($data[$obj_id]["hour" . $hour_transf] ?? 0) + (int) $values[$this->filter["measure"]];
                    $data[$obj_id]["sum"] =
                        ($data[$obj_id]["sum"] ?? 0) + (int) $values[$this->filter["measure"]];

                    // graph data
                    $data[$obj_id]["graph"]["hour" . $hour] = $values[$this->filter["measure"]];
                }
            }

            // add objects with no usage data
            foreach ($objects as $obj_id => $ref_ids) {
                if (!isset($data[$obj_id])) {
                    $data[$obj_id]["obj_id"] = $obj_id;
                    $data[$obj_id]['reference_ids'] = $this->findReferencesForObjId($obj_id);
                    $data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
                }
            }
        }
        $this->setData($data);
    }

    /**
     * Fill table row
     */
    protected function fillRow(array $a_set) : void
    {
        $type = ilObject::_lookupType($a_set["obj_id"]);
        $this->tpl->setVariable("OBJ_ID", $a_set["obj_id"]);
        $this->tpl->setVariable(
            "ICON_SRC",
            ilObject::_getIcon(0, "tiny", $type)
        );
        $this->tpl->setVariable("ICON_ALT", $this->lng->txt($type));
        $this->tpl->setVariable("TITLE_TEXT", $a_set["title"]);

        if ($this->preselected && in_array(
            $a_set["obj_id"],
            $this->preselected
        )) {
            $this->tpl->setVariable("CHECKBOX_STATE", " checked=\"checked\"");
        }

        $this->tpl->setCurrentBlock("hour");
        for ($loop = 0; $loop < 24; $loop += 2) {
            $value = (int) ($a_set["hour" . $loop] ?? 0);
            if ($this->filter["measure"] != "spent_seconds") {
                $value = $this->anonymizeValue($value);
            } else {
                $value = $this->formatSeconds($value, true);
            }
            $this->tpl->setVariable("HOUR_VALUE", $value);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->filter["measure"] == "spent_seconds") {
            $sum = $this->formatSeconds((int) ($a_set["sum"] ?? 0), true);
        } else {
            $sum = $this->anonymizeValue((int) ($a_set["sum"] ?? 0));
        }
        $this->tpl->setVariable("TOTAL", $sum);

        // optional columns
        if ($this->isColumnSelected('obj_id')) {
            $this->tpl->setVariable('OBJ_ID_COL_VALUE', (string) $a_set['obj_id']);
        }
        if ($this->isColumnSelected('reference_ids')) {
            $this->tpl->setVariable('REF_IDS', implode(', ', $a_set['reference_ids']));
        }
        if ($this->isColumnSelected('paths')) {
            $paths = [];
            foreach ($a_set['reference_ids'] as $reference_id) {
                $path_gui = new ilPathGUI();
                $path_gui->enableTextOnly(false);
                $path_gui->enableHideLeaf(false);
                $path_gui->setUseImages(true);
                $paths[] = $path_gui->getPath(ROOT_FOLDER_ID, $reference_id);
            }
            $this->tpl->setVariable('PATHS', implode('<br />', $paths));
        }
    }

    public function getGraph(array $a_graph_items) : string
    {
        global $DIC;

        $lng = $DIC['lng'];

        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, "objstdly");
        $chart->setSize("700", "500");

        $legend = new ilChartLegend();
        $chart->setLegend($legend);

        $max_value = 0;
        foreach ($this->getData() as $object) {
            if (in_array($object["obj_id"], $a_graph_items)) {
                $series = $chart->getDataInstance(ilChartGrid::DATA_LINES);
                $series->setLabel(ilObject::_lookupTitle($object["obj_id"]));

                for ($loop = 0; $loop < 24; $loop++) {
                    $value = (int) ($object["graph"]["hour" . $loop] ?? 0);
                    $max_value = max($max_value, $value);
                    if ($this->filter["measure"] != "spent_seconds") {
                        $value = $this->anonymizeValue($value, true);
                    }
                    $series->addPoint($loop, $value);
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
        for ($loop = 0; $loop < 24; $loop++) {
            $labels[$loop] = str_pad((string) $loop, 2, "0", STR_PAD_LEFT);
        }
        $chart->setTicks($labels, $value_ticks, true);

        return $chart->getHTML();
    }

    protected function fillMetaExcel(ilExcel $a_excel, int &$a_row) : void
    {
    }

    protected function fillRowExcel(
        ilExcel $a_excel,
        int &$a_row,
        array $a_set
    ) : void {
        $a_excel->setCell($a_row, 0, ilObject::_lookupTitle($a_set["obj_id"]));
        $a_excel->setCell($a_row, 1, $a_set["obj_id"]);

        $col = 1;
        for ($loop = 0; $loop < 24; $loop += 2) {
            $value = (int) ($a_set["hour" . $loop] ?? 0);
            if ($this->filter["measure"] != "spent_seconds") {
                $value = $this->anonymizeValue($value);
            }

            $a_excel->setCell($a_row, ++$col, $value);
        }

        if ($this->filter["measure"] == "spent_seconds") {
            // keep seconds
            // $sum = $this->formatSeconds((int)$a_set["sum"]);
            $sum = (int) $a_set["sum"];
        } else {
            $sum = $this->anonymizeValue((int) $a_set["sum"]);
        }
        $a_excel->setCell($a_row, ++$col, $sum);
    }

    protected function fillMetaCSV(ilCSVWriter $a_csv) : void
    {
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set) : void
    {
        $a_csv->addColumn(ilObject::_lookupTitle($a_set["obj_id"]));
        $a_csv->addColumn($a_set["obj_id"]);

        for ($loop = 0; $loop < 24; $loop += 2) {
            $value = (int) ($a_set["hour" . $loop] ?? 0);
            if ($this->filter["measure"] != "spent_seconds") {
                $value = $this->anonymizeValue($value);
            }

            $a_csv->addColumn($value);
        }

        if ($this->filter["measure"] == "spent_seconds") {
            // keep seconds
            // $sum = $this->formatSeconds((int)$a_set["sum"]);
            $sum = (int) $a_set["sum"];
        } else {
            $sum = $this->anonymizeValue((int) $a_set["sum"]);
        }
        $a_csv->addColumn($sum);

        $a_csv->addRow();
    }
}
