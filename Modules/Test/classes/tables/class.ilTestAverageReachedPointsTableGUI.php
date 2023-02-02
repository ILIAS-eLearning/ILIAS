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
 * @author  Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ModulesTest
 */
class ilTestAverageReachedPointsTableGUI extends ilTable2GUI
{
    public function __construct(?object $a_parent_obj, string $a_parent_cmd)
    {
        $this->setId('tstAvgReachedPointsTbl');
        $this->setPrefix('tstAvgReachedPointsTbl');

        parent::__construct($a_parent_obj, $a_parent_cmd);

        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $this->setFormName('average_reached_points');
        $this->setTitle($this->lng->txt('average_reached_points'));
        $this->setStyle('table', 'fullwidth');
        $this->addColumn($this->lng->txt("question_id"), 'qid', '');
        $this->addColumn($this->lng->txt("question_title"), 'title', '');
        $this->addColumn($this->lng->txt("points"), 'points', '');
        $this->addColumn($this->lng->txt("percentage"), 'percentage', '');
        $this->addColumn($this->lng->txt("number_of_answers"), 'answers', '');

        $this->setRowTemplate("tpl.il_as_tst_average_reached_points_row.html", "Modules/Test");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->enable('sort');
        $this->enable('header');
        $this->disable('select_all');
    }

    /**
     * Should this field be sorted numeric?
     * @return bool  numeric ordering; default is false
     */
    public function numericOrdering(string $a_field): bool
    {
        switch ($a_field) {
            case 'percentage':
            case 'qid':
            case 'points':
                return true;

            default:
                return false;
        }
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("ID", $a_set["qid"]);
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("POINTS", $this->formatPoints($a_set));
        $this->tpl->setVariable("PERCENTAGE", sprintf("%.2f", $a_set["percentage"]) . "%");
        $this->tpl->setVariable("ANSWERS", $a_set["answers"]);
    }

    protected function formatPoints(array $data): string
    {
        return sprintf("%.2f", $data['points_reached']) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%.2f", $data['points_max']);
    }
}
