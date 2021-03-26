<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id: class.ilSurveyResultsCumulatedTableGUI.php 23310 2010-03-21 23:41:39Z hschottm $
 *
 * @ingroup ModulesSurvey
 */

class ilSumScoreTableGUI extends ilTable2GUI
{
    private $is_anonymized;

    /**
     * ilSumScoreTableGUI constructor.
     * @param $a_parent_obj
     * @param $a_parent_cmd
     * @param $is_anonymized
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $is_anonymized)
    {
        global $DIC;

        $this->setId("svy_sum_score");
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->is_anonymized = $is_anonymized;
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

    /**
     * Set sum scores
     * @param $scores
     */
    public function setSumScores($scores)
    {
        $this->setData($scores);
    }

    /**
     * fill row
     *
     * @param array $data
     */
    public function fillRow($data)
    {
        if ($data['score'] === null) {
            $data['score'] = "n.a.";
        }
        $this->tpl->setVariable("SUM_SCORE", $data['score']);
        $this->tpl->setVariable("PARTICIPANT", $data['username']);
    }

    protected function fillHeaderExcel(ilExcel $a_excel, &$a_row)
    {
        $a_excel->setCell($a_row, 0, $this->lng->txt("username"));
        $a_excel->setCell($a_row, 1, $this->lng->txt("sum_score"));
        $a_excel->setBold("A" . $a_row . ":" . $a_excel->getColumnCoord(2 - 1) . $a_row);
    }

    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $a_set)
    {
        if ($a_set['score'] === null) {
            $a_set['score'] = "n.a.";
        }
        $a_excel->setCell($a_row, 0, $a_set["username"]);
        $a_excel->setCell($a_row, 1, $a_set["score"]);
    }

    protected function fillHeaderCSV($a_csv)
    {
        $a_csv->addColumn($this->lng->txt("username"));
        $a_csv->addColumn($this->lng->txt("score"));
    }

    protected function fillRowCSV($a_csv, $a_set)
    {
        if ($a_set['score'] === null) {
            $a_set['score'] = "n.a.";
        }
        $a_csv->addColumn($a_set["title"]);
        $a_csv->addColumn($a_set["score"]);
    }
}
