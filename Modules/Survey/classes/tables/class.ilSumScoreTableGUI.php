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
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSumScoreTableGUI extends ilTable2GUI
{
    protected int $counter;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        bool $is_anonymized
    ) {
        global $DIC;

        $this->setId("svy_sum_score");
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->counter = 1;

        $this->addColumn($this->lng->txt("username"), 'username', '');
        $this->addColumn($this->lng->txt("svy_sum_score"), 'score', '');
        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));

        $this->setRowTemplate("tpl.sum_score_row.html", "Modules/Survey");

        $this->setDefaultOrderField('username');

        $this->setShowRowsSelector(true);
    }

    public function setSumScores(array $scores) : void
    {
        $this->setData($scores);
    }

    protected function fillRow(array $a_set) : void
    {
        if ($a_set['score'] === null) {
            $a_set['score'] = "n.a.";
        }
        $this->tpl->setVariable("SUM_SCORE", $a_set['score']);
        $this->tpl->setVariable("PARTICIPANT", $a_set['username']);
    }

    protected function fillHeaderExcel(ilExcel $a_excel, int &$a_row) : void
    {
        $a_excel->setCell($a_row, 0, $this->lng->txt("username"));
        $a_excel->setCell($a_row, 1, $this->lng->txt("sum_score"));
        $a_excel->setBold("A" . $a_row . ":" . $a_excel->getColumnCoord(2 - 1) . $a_row);
    }

    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set) : void
    {
        if ($a_set['score'] === null) {
            $a_set['score'] = "n.a.";
        }
        $a_excel->setCell($a_row, 0, $a_set["username"]);
        $a_excel->setCell($a_row, 1, $a_set["score"]);
    }

    protected function fillHeaderCSV(ilCSVWriter $a_csv) : void
    {
        $a_csv->addColumn($this->lng->txt("username"));
        $a_csv->addColumn($this->lng->txt("score"));
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set) : void
    {
        if ($a_set['score'] === null) {
            $a_set['score'] = "n.a.";
        }
        $a_csv->addColumn($a_set["title"]);
        $a_csv->addColumn($a_set["score"]);
    }
}
