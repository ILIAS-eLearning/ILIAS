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
 * Benchmark table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilBenchmarkTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_records, $a_mode = "chronological")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setLimit(9999);
        $this->mode = $a_mode;

        switch ($this->mode) {
            case "slowest_first":
                $this->setData(ilArrayUtil::sortArray($a_records, "time", "desc", true));
                $this->setTitle($lng->txt("adm_db_bench_slowest_first"));
                $this->addColumn($this->lng->txt("adm_time"));
                $this->addColumn($this->lng->txt("adm_sql"));
                break;

            case "sorted_by_sql":
                $this->setData(ilArrayUtil::sortArray($a_records, "sql", "asc"));
                $this->setTitle($lng->txt("adm_db_bench_sorted_by_sql"));
                $this->addColumn($this->lng->txt("adm_time"));
                $this->addColumn($this->lng->txt("adm_sql"));
                break;

            case "by_first_table":
                $this->setData($this->getDataByFirstTable($a_records));
                $this->setTitle($lng->txt("adm_db_bench_by_first_table"));
                $this->addColumn($this->lng->txt("adm_time"));
                $this->addColumn($this->lng->txt("adm_nr_statements"));
                $this->addColumn($this->lng->txt("adm_table"));
                break;

            default:
                $this->setData($a_records);
                $this->setTitle($lng->txt("adm_db_bench_chronological"));
                $this->addColumn($this->lng->txt("adm_time"));
                $this->addColumn($this->lng->txt("adm_sql"));
                break;

        }

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.db_bench.html", "Modules/SystemFolder");
        $this->disable("footer");
        $this->setEnableTitle(true);
    }

    /**
     * Get first occurence of string
     */
    public function getFirst(string $a_str, array $a_needles): int
    {
        $pos = 0;
        foreach ($a_needles as $needle) {
            $pos2 = strpos($a_str, (string) $needle);

            if ($pos2 > 0 && ($pos2 < $pos || $pos === 0)) {
                $pos = $pos2;
            }
        }

        return $pos;
    }

    /**
     * Extract first table from sql
     */
    public function extractFirstTableFromSQL(string $a_sql): string
    {
        $pos1 = $this->getFirst(strtolower($a_sql), ["from ", "from\n", "from\t", "from\r"]);

        $table = "";
        if ($pos1 > 0) {
            $tablef = substr(strtolower($a_sql), $pos1 + 5);
            $pos2 = $this->getFirst($tablef, [" ", "\n", "\t", "\r"]);
            $table = $pos2 > 0 ? substr($tablef, 0, $pos2) : $tablef;
        }
        if (trim($table) !== "") {
            return $table;
        }

        return "";
    }

    /**
     * Get data by first table
     */
    public function getDataByFirstTable(array $a_records): array
    {
        $data = [];
        foreach ($a_records as $r) {
            $table = $this->extractFirstTableFromSQL($r["sql"]);
            if (trim($table) === '') {
                continue;
            }
            $data[$table]["table"] = $table;
            if (!isset($data[$table]["cnt"])) {
                $data[$table]["cnt"] = 1;
            } else {
                $data[$table]["cnt"]++;
            }
            if (!isset($data[$table]["time"])) {
                $data[$table]["time"] = $r["time"];
            } else {
                $data[$table]["time"] += $r["time"];
            }
        }
        if ($data !== []) {
            return ilArrayUtil::sortArray($data, "time", "desc", true);
        }

        return $data;
    }

    /**
     * Fill table row
     */
    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;

        switch ($this->mode) {
            case "by_first_table":
                $this->tpl->setCurrentBlock("td");
                $this->tpl->setVariable("VAL", $a_set["table"]);
                $this->tpl->parseCurrentBlock();
                $this->tpl->setVariable("VAL1", $a_set["time"]);
                $this->tpl->setVariable("VAL2", $a_set["cnt"]);
                break;

            case "slowest_first":
            case "sorted_by_sql":
            default:
                $this->tpl->setVariable("VAL1", $a_set["time"]);
                $this->tpl->setVariable("VAL2", $a_set["sql"]);
                break;
        }
    }
}
