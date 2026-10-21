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

declare(strict_types=1);

use ILIAS\Data\Order;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Implementation\Component\Table\Data as TableData;

/**
 * Class ilSCORMTrackingItemsTableGUI
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @ingroup components\ILIASScormAicc
 */
class ilSCORMTrackingItemsTableGUI
{
    private int $obj_id;
    private bool $bySCO = false;
    private array $scosSelected;
    private array $userSelected;
    private bool $allowExportPrivacy = false;
    private string $lmTitle = "";
    private string $report;
    private ilLanguage $lng;
    private ?TableAdapterGUI $table = null;
    private ?array $items = null;

    public function __construct(
        int $a_obj_id,
        ?object $a_parent_obj,
        string $a_parent_cmd,
        array $a_userSelected,
        array $a_scosSelected,
        string $a_report
    ) {
        global $DIC;
        if ($a_parent_obj === null) {
            throw new ilException('Missing parent object');
        }

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("scormtrac");
        $this->lng->loadLanguageModule("trac");

        $this->obj_id = $a_obj_id;
        $this->report = $a_report;
        $this->scosSelected = $a_scosSelected;
        $this->userSelected = $a_userSelected;
        $this->bySCO = ($a_parent_cmd === "showTrackingItemsBySco");
        $this->lmTitle = $a_parent_obj->getObject()->getTitle();

        $privacy = ilPrivacySettings::getInstance();
        $this->allowExportPrivacy = $privacy->enabledExportSCORM();

        $this->initTable($a_parent_obj, $a_parent_cmd);
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getHTML(): string
    {
        return $this->table?->render() ?? '';
    }

    public function sendExportCSV(): void
    {
        $columns = $this->getColumnsForExport();
        $rows = $this->getRowsForExport();

        $csv = new ilCSVWriter();
        $csv->setSeparator(';');

        foreach ($columns as $column) {
            $csv->addColumn($this->getExportColumnLabel($column));
        }
        $csv->addRow();

        foreach ($rows as $row) {
            foreach ($columns as $column) {
                $value = $this->exportValue($column, $row[$column] ?? null);
                $csv->addColumn((string) ($value ?? ''));
            }
            $csv->addRow();
        }

        $tmp_file = ilFileUtils::ilTempnam();
        if (file_put_contents($tmp_file, $csv->getCSVString()) === false) {
            throw new ilException('Unable to create CSV export file.');
        }

        ilFileDelivery::deliverFileAttached($tmp_file, $this->buildExportFilename('csv'), null, true);
        exit;
    }

    public function sendExportExcel(): void
    {
        $columns = $this->getColumnsForExport();
        $rows = $this->getRowsForExport();

        $excel = new ilExcel();
        $excel->addSheet($this->lng->txt("cont_tracking_items"));

        $row_idx = 1;
        $col_idx = 0;
        foreach ($columns as $column) {
            $excel->setCell($row_idx, $col_idx, $this->getExportColumnLabel($column));
            $col_idx++;
        }

        $row_idx++;
        foreach ($rows as $row) {
            $col_idx = 0;
            foreach ($columns as $column) {
                $excel->setCell($row_idx, $col_idx, $this->exportValue($column, $row[$column] ?? null));
                $col_idx++;
            }
            $row_idx++;
        }

        $excel->sendToClient($this->buildExportFilename('xlsx'));
        exit;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getSelectableColumns(): array
    {
        return match ($this->report) {
            "exportSelectedCore" => ilSCORMTrackingItems::exportSelectedCoreColumns(
                $this->bySCO,
                $this->allowExportPrivacy
            ),
            "exportSelectedRaw" => ilSCORMTrackingItems::exportSelectedRawColumns(),
            "exportSelectedInteractions" => ilSCORMTrackingItems::exportSelectedInteractionsColumns(),
            "exportSelectedObjectives" => ilSCORMTrackingItems::exportSelectedObjectivesColumns(),
            "exportSelectedSuccess" => ilSCORMTrackingItems::exportSelectedSuccessColumns(),
            default => []
        };
    }

    private function initTable(object $a_parent_obj, string $a_parent_cmd): void
    {
        $rows = [];
        $available_columns = $this->getAvailableColumns();
        $default_columns = $this->getDefaultColumns();
        $numeric_columns = $this->getNumericColumns();
        $numeric_table_keys = ['id'];

        foreach ($this->getItems() as $index => $row) {
            $mapped = ['id' => $index + 1];
            foreach ($available_columns as $column) {
                $table_key = $this->tableColumnKey($column);
                $mapped[$table_key] = $this->parseValue($column, $row[$column] ?? null);

                if (
                    in_array($column, $numeric_columns, true)
                    && !in_array($table_key, $numeric_table_keys, true)
                ) {
                    $numeric_table_keys[] = $table_key;
                }
            }
            $rows[] = $mapped;
        }

        $this->table = new TableAdapterGUI(
            'AICC' . $this->report,
            '',
            new ilSCORMTrackingTableRetrieval($rows, $numeric_table_keys),
            $a_parent_obj,
            $a_parent_cmd
        );

        foreach ($available_columns as $column) {
            $this->table->column(
                $this->tableColumnKey($column),
                $this->buildColumn(
                    $column,
                    in_array($column, $default_columns, true)
                )
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function getAvailableColumns(): array
    {
        return array_keys($this->getSelectableColumns());
    }

    /**
     * @return array<int, string>
     */
    private function getDefaultColumns(): array
    {
        $selectable = $this->getSelectableColumns();
        $defaults = [];

        foreach ($selectable as $key => $definition) {
            if (($definition['default'] ?? false) === true) {
                $defaults[] = $key;
            }
        }

        if ($defaults === []) {
            return array_keys($selectable);
        }

        return $defaults;
    }

    private function buildColumn(string $column, bool $is_initially_visible): Column
    {
        global $DIC;

        return $DIC->ui()->factory()->table()->column()->text($this->getColumnTitle($column))
            ->withIsSortable(true)
            ->withIsOptional(true, $is_initially_visible);
    }

    private function tableColumnKey(string $column): string
    {
        return 'col_' . $column;
    }

    private function getColumnTitle(string $column): string
    {
        $lookup = $column;
        if (in_array($lookup, ["status", "time", "score"], true)) {
            $lookup = "cont_" . $lookup;
        }

        if (str_starts_with($lookup, "interaction_id")) {
            $base = substr($lookup, 0, 14);
            return $this->lng->txt($base) . ' ' . substr($lookup, 14);
        }

        if (str_starts_with($lookup, "interaction_value")) {
            $base = substr($lookup, 0, 17);
            $space = strpos($lookup, ' ');
            if ($space === false) {
                return $this->lng->txt($base);
            }
            return sprintf($this->lng->txt($base), substr($lookup, 17, $space - 17)) . substr($lookup, $space);
        }

        if (str_starts_with($lookup, "interaction_description")) {
            $base = substr($lookup, 0, 23);
            return $this->lng->txt($base) . ' ' . substr($lookup, 23);
        }

        return $this->lng->txt($lookup);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getItems(): array
    {
        if ($this->items !== null) {
            return $this->items;
        }

        $tracking_items = new ilSCORMTrackingItems();

        $this->items = match ($this->report) {
            "exportSelectedCore" => $tracking_items->exportSelectedCore(
                $this->userSelected,
                $this->scosSelected,
                $this->bySCO,
                $this->allowExportPrivacy,
                $this->getObjId(),
                $this->lmTitle
            ),
            "exportSelectedRaw" => $tracking_items->exportSelectedRaw(
                $this->userSelected,
                $this->scosSelected,
                $this->bySCO,
                $this->allowExportPrivacy,
                $this->getObjId(),
                $this->lmTitle
            ),
            "exportSelectedInteractions" => $tracking_items->exportSelectedInteractions(
                $this->userSelected,
                $this->scosSelected,
                $this->bySCO,
                $this->allowExportPrivacy,
                $this->getObjId(),
                $this->lmTitle
            ),
            "exportSelectedObjectives" => $tracking_items->exportSelectedObjectives(
                $this->userSelected,
                $this->scosSelected,
                $this->bySCO,
                $this->allowExportPrivacy,
                $this->getObjId(),
                $this->lmTitle
            ),
            "exportSelectedSuccess" => $tracking_items->exportSelectedSuccess(
                $this->userSelected,
                $this->allowExportPrivacy,
                $this->getObjId(),
                $this->lmTitle
            ),
            default => []
        };

        return $this->items;
    }

    protected function parseValue(string $id, string|float|int|null $value): string|float|int|null
    {
        if ($id === "status") {
            return ilLearningProgressBaseGUI::_getStatusText((int) $value);
        }

        if ($id === "launch_data" || $id === "suspend_data") {
            return $value;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        return $value;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRowsForExport(): array
    {
        $rows = $this->getItems();
        [$order_column, $order_direction] = $this->readOrderFromTableStorage();

        if ($order_column !== null && $order_direction !== null) {
            $rows = ilArrayUtil::stableSortArray($rows, $order_column, $order_direction);
        }

        return $rows;
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function readOrderFromTableStorage(): array
    {
        foreach ($this->getTableStorageData() as $value) {
            if (!is_array($value) || count($value) !== 2) {
                continue;
            }

            $order_value = array_values($value);
            if (
                !is_string($order_value[0])
                || !str_starts_with($order_value[0], 'col_')
                || !is_string($order_value[1])
            ) {
                continue;
            }

            $column = substr($order_value[0], 4);
            $direction = strtoupper($order_value[1]);

            if (
                !in_array($direction, [Order::ASC, Order::DESC], true)
                || !array_key_exists($column, $this->getSelectableColumns())
            ) {
                continue;
            }

            return [$column, strtolower($direction)];
        }

        return [null, null];
    }

    /**
     * @return array<int, string>
     */
    private function getColumnsForExport(): array
    {
        $selected_columns = $this->readSelectedColumnsFromTableStorage();
        if ($selected_columns !== []) {
            return $selected_columns;
        }

        return $this->getDefaultColumns();
    }

    /**
     * @return array<int, string>
     */
    private function readSelectedColumnsFromTableStorage(): array
    {
        $available = array_flip($this->getAvailableColumns());

        foreach ($this->getTableStorageData() as $value) {
            if (!is_array($value) || $value === []) {
                continue;
            }

            $candidate = [];
            foreach ($value as $entry) {
                if (!is_string($entry)) {
                    continue;
                }

                if ($entry === '') {
                    continue;
                }

                if (!str_starts_with($entry, 'col_')) {
                    $candidate = [];
                    break;
                }

                $column = substr($entry, 4);
                if (isset($available[$column])) {
                    $candidate[] = $column;
                }
            }

            if ($candidate !== []) {
                return array_values(array_unique($candidate));
            }
        }

        return [];
    }

    private function getTableStorageData(): array
    {
        $storage_id = TableData::STORAGE_ID_PREFIX . 'AICC' . $this->report;
        $stored_values = ilSession::get($storage_id);

        return is_array($stored_values) ? $stored_values : [];
    }

    /**
     * @return array<int, string>
     */
    private function getNumericColumns(): array
    {
        return [
            "lm_id",
            "sco_id",
            "counter",
            "c_max",
            "c_min",
            "c_raw",
            "LearningModuleId",
            "LearningModuleVersion",
            "Percentage",
            "Attempts",
            "existingSCOs",
            "startedSCOs",
            "completedSCOs",
            "passedSCOs",
            "roundedTotal_timeSeconds"
        ];
    }

    private function getExportColumnLabel(string $column): string
    {
        $selectable = $this->getSelectableColumns();
        if (isset($selectable[$column]['txt']) && is_string($selectable[$column]['txt'])) {
            return $selectable[$column]['txt'];
        }

        return $this->getColumnTitle($column);
    }

    private function exportValue(string $column, string|float|int|null $value): string|float|int|null
    {
        if ($column === 'status') {
            return ilLearningProgressBaseGUI::_getStatusText((int) $value);
        }

        return $this->parseValue($column, $value);
    }

    private function buildExportFilename(string $extension): string
    {
        return sprintf(
            'scorm_tracking_items_%d_%s_%s.%s',
            $this->obj_id,
            strtolower($this->report),
            date('Ymd_His'),
            $extension
        );
    }
}
