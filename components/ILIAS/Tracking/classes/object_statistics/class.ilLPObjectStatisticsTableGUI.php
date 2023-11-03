<?php

declare(strict_types=0);

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
 * @ilCtrl_Calls ilLPObjectStatisticsTableGUI: ilFormPropertyDispatchGUI
 * @ingroup      ServicesTracking
 */
class ilLPObjectStatisticsTableGUI extends ilLPTableBaseGUI
{
    protected ?array $preselected;

    /**
     * Constructor
     */
    public function __construct(
        ?object $a_parent_obj,
        string $a_parent_cmd,
        ?array $a_preselect = null
    ) {
        $this->preselected = $a_preselect;

        $this->setId("lpobjstattbl");
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function init(): void
    {
        $this->setShowRowsSelector(true);
        $this->initFilter();

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("trac_title"), "title", '30%');

        $all_columns = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col_name => $col_info) {
            $column_definition = $all_columns[$col_name];
            $this->addColumn(
                $column_definition['txt'],
                $column_definition['sortable'] ? $column_definition['field'] : '',
                $column_definition['width']
            );
        }
        if (strpos($this->filter["yearmonth"], "-") === false) {
            foreach ($this->getMonthsYear(
                $this->filter["yearmonth"]
            ) as $num => $caption) {
                $this->addColumn($caption, "month_" . $num);
            }
        }
        $this->addColumn($this->lng->txt("total"), "total");

        $this->setTitle($this->lng->txt("trac_object_stat_access"));

        // $this->setSelectAllCheckbox("item_id");
        $this->addMultiCommand(
            "showAccessGraph",
            $this->lng->txt("trac_show_graph")
        );
        $this->setResetCommand("resetAccessFilter");
        $this->setFilterCommand("applyAccessFilter");

        $this->setFormAction(
            $this->ctrl->getFormAction(
                $this->getParentObject(),
                $this->getParentCmd()
            )
        );
        $this->setRowTemplate(
            "tpl.lp_object_statistics_row.html",
            "Services/Tracking"
        );
        $this->setEnableHeader(true);
        $this->setEnableNumInfo(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_CSV));
    }

    public function getSelectableColumns(): array
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

    public function numericOrdering(string $a_field): bool
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
    public function initFilter(): void
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
                  "spent_seconds" => $this->lng->txt("trac_spent_seconds"),
                  "users" => $this->lng->txt("users")
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

    protected function isForwardingToFormDispatcher(): bool
    {
        return true;
    }

    public function getItems(): void
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
                foreach (ilTrQuery::getObjectAccessStatistics(
                    $objects,
                    $yearmonth[0]
                ) as $obj_id => $months) {
                    $data[$obj_id]["obj_id"] = $obj_id;
                    $data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
                    $data[$obj_id]['reference_ids'] = $this->findReferencesForObjId($obj_id);

                    foreach ($months as $month => $values) {
                        $idx = $yearmonth[0] . "-" . str_pad(
                            $month,
                            2,
                            "0",
                            STR_PAD_LEFT
                        );
                        $data[$obj_id]["month_" . $idx] = (int) ($values[$this->filter["measure"]] ?? 0);
                        $data[$obj_id]["total"] = ($data[$obj_id]["total"] ?? 0) + (int) ($values[$this->filter["measure"]] ?? 0);
                    }
                }
            } else {
                foreach (ilTrQuery::getObjectAccessStatistics(
                    $objects,
                    (string) $yearmonth[0],
                    (string) $yearmonth[1]
                ) as $obj_id => $days) {
                    $data[$obj_id]["obj_id"] = $obj_id;
                    $data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
                    $data[$obj_id]['reference_ids'] = $this->findReferencesForObjId($obj_id);

                    foreach ($days as $day => $values) {
                        $data[$obj_id]["day_" . $day] = (int) ($values[$this->filter["measure"]] ?? 0);
                        $data[$obj_id]["total"] = ($data[$obj_id]["total"] ?? 0) + (int) ($values[$this->filter["measure"]] ?? 0);
                    }
                }
            }

            // add objects with no usage data
            foreach (array_keys($objects) as $obj_id) {
                if (!isset($data[$obj_id])) {
                    $data[$obj_id]["obj_id"] = $obj_id;
                    $data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
                    $data[$obj_id]['reference_ids'] = $this->findReferencesForObjId($obj_id);
                }
            }
        }
        $this->setData($data);
    }

    /**
     * @return int[]
     */
    protected function findReferencesForObjId(int $a_obj_id): array
    {
        $ref_ids = array_keys(ilObject::_getAllReferences($a_obj_id));
        sort($ref_ids, SORT_NUMERIC);
        return $ref_ids;
    }

    /**
     * Fill table row
     */
    protected function fillRow(array $a_set): void
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

        $sum = 0;
        if (strpos($this->filter["yearmonth"], "-") === false) {
            $this->tpl->setCurrentBlock("month");
            foreach (array_keys(
                $this->getMonthsYear($this->filter["yearmonth"])
            ) as $num) {
                $value = (int) ($a_set["month_" . $num] ?? 0);
                if (($this->filter["measure"] ?? "") != "spent_seconds") {
                    $value = $this->anonymizeValue($value);
                } else {
                    $value = $this->formatSeconds($value, true);
                }
                $this->tpl->setVariable("MONTH_VALUE", $value);
                $this->tpl->parseCurrentBlock();
            }
        }

        if (($this->filter["measure"] ?? "") == "spent_seconds") {
            $sum = $this->formatSeconds((int) ($a_set["total"] ?? 0), true);
        } else {
            $sum = $this->anonymizeValue((int) ($a_set["total"] ?? 0));
        }
        $this->tpl->setVariable("TOTAL", $sum);

        // optional columns
        if ($this->isColumnSelected('obj_id')) {
            $this->tpl->setVariable('OBJ_ID_COL_VALUE', (string) $a_set['obj_id']);
        }
        if ($this->isColumnSelected('reference_ids')) {
            $this->tpl->setVariable('REF_IDS', (string) implode(', ', $a_set['reference_ids']));
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
            $this->tpl->setVariable('PATHS', implode('<br /> ', $paths));
        }
    }

    public function getGraph(array $a_graph_items): string
    {
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, "objstacc");
        $chart->setSize("700", "500");

        $legend = new ilChartLegend();
        $chart->setLegend($legend);

        $max_value = 0;
        foreach ($this->getData() as $object) {
            if (in_array($object["obj_id"], $a_graph_items)) {
                $series = $chart->getDataInstance(ilChartGrid::DATA_LINES);
                $series->setLabel(ilObject::_lookupTitle($object["obj_id"]));

                if (strpos($this->filter["yearmonth"], "-") === false) {
                    foreach (array_keys(
                        $this->getMonthsYear(
                            $this->filter["yearmonth"]
                        )
                    ) as $idx => $num) {
                        $value = (int) ($object["month_" . $num] ?? 0);
                        $max_value = max($max_value, $value);
                        if ($this->filter["measure"] != "spent_seconds") {
                            $value = $this->anonymizeValue($value, true);
                        }
                        $series->addPoint($idx, $value);
                    }
                } else {
                    for ($loop = 1; $loop < 32; $loop++) {
                        $value = (int) ($object["day_" . $loop] ?? 0);
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
            foreach (array_values(
                $this->getMonthsYear($this->filter["yearmonth"], true)
            ) as $idx => $caption) {
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

    protected function fillMetaExcel(ilExcel $a_excel, int &$a_row): void
    {
    }

    protected function fillRowExcel(
        ilExcel $a_excel,
        int &$a_row,
        array $a_set
    ): void {
        $a_excel->setCell($a_row, 0, ilObject::_lookupTitle($a_set["obj_id"]));

        $col = 0;

        // optional columns
        if ($this->isColumnSelected('obj_id')) {
            $a_excel->setCell($a_row, ++$col, (string) $a_set['obj_id']);
        }
        if ($this->isColumnSelected('reference_ids')) {
            $a_excel->setCell($a_row, ++$col, implode(', ', $a_set['reference_ids']));
        }
        if ($this->isColumnSelected('paths')) {
            $paths = [];
            foreach ($a_set['reference_ids'] as $reference_id) {
                $path_gui = new ilPathGUI();
                $path_gui->enableTextOnly(true);
                $path_gui->enableHideLeaf(false);
                $path_gui->setUseImages(false);
                $paths[] = $path_gui->getPath(ROOT_FOLDER_ID, $reference_id);
            }
            /*
             * The strings returned by the PathGUI have a linebreak at the end,
             * which has to be removed or it messes up how the paths are displayed in excel.
             */
            $a_excel->setCell($a_row, ++$col, substr(implode(', ', $paths), 0, -1));
        }

        if (strpos($this->filter["yearmonth"], "-") === false) {
            foreach (array_keys(
                $this->getMonthsYear($this->filter["yearmonth"])
            ) as $num) {
                $value = (int) ($a_set["month_" . $num] ?? 0);
                if ($this->filter["measure"] != "spent_seconds") {
                    $value = $this->anonymizeValue($value);
                }

                $a_excel->setCell($a_row, ++$col, $value);
            }
        }

        if ($this->filter["measure"] == "spent_seconds") {
            // keep seconds
            // $sum = $this->formatSeconds((int)$a_set["total"]);
            $sum = (int) ($a_set["total"] ?? 0);
        } else {
            $sum = $this->anonymizeValue((int) ($a_set["total"] ?? 0));
        }
        $a_excel->setCell($a_row, ++$col, $sum);
    }

    protected function fillMetaCSV(ilCSVWriter $a_csv): void
    {
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set): void
    {
        $a_csv->addColumn(ilObject::_lookupTitle($a_set["obj_id"]));

        // optional columns
        if ($this->isColumnSelected('obj_id')) {
            $a_csv->addColumn($a_set["obj_id"]);
        }
        if ($this->isColumnSelected('reference_ids')) {
            $a_csv->addColumn(implode(', ', $a_set['reference_ids']));
        }
        if ($this->isColumnSelected('paths')) {
            $paths = [];
            foreach ($a_set['reference_ids'] as $reference_id) {
                $path_gui = new ilPathGUI();
                $path_gui->enableTextOnly(true);
                $path_gui->enableHideLeaf(false);
                $path_gui->setUseImages(false);
                $paths[] = $path_gui->getPath(ROOT_FOLDER_ID, $reference_id);
            }
            /*
            * The strings returned by the PathGUI have a linebreak at the end,
            * which has to be removed or it messes up how the paths are displayed in excel.
            */
            $a_csv->addColumn(substr(implode(', ', $paths), 0, -1));
        }

        if (strpos($this->filter["yearmonth"], "-") === false) {
            foreach (array_keys(
                $this->getMonthsYear($this->filter["yearmonth"])
            ) as $num) {
                $value = (int) ($a_set["month_" . $num] ?? 0);
                if ($this->filter["measure"] != "spent_seconds") {
                    $value = $this->anonymizeValue($value);
                }

                $a_csv->addColumn($value);
            }
        }

        if ($this->filter["measure"] == "spent_seconds") {
            // keep seconds
            // $sum = $this->formatSeconds((int)$a_set["total"]);
            $sum = (int) ($a_set["total"] ?? 0);
        } else {
            $sum = $this->anonymizeValue((int) ($a_set["total"] ?? 0));
        }
        $a_csv->addColumn($sum);

        $a_csv->addRow();
    }
}
