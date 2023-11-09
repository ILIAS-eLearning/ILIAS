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

/**
*
* @author	Björn Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup	ModulesTest
*/
class ilTestPassManualScoringOverviewTableGUI extends ilTable2GUI
{
    public function __construct(ilTestScoringGUI $parent_obj, string $parent_cmd)
    {
        $this->setPrefix('manScorePassesTable');

        parent::__construct($parent_obj, $parent_cmd);

        $this->setFormName('manScorePassesTable');
        $this->setStyle('table', 'fullwidth');

        $this->enable('header');

        $this->setFormAction($this->ctrl->getFormAction($parent_obj, $parent_cmd));

        $this->setRowTemplate("tpl.il_as_tst_pass_overview_tblrow.html", "components/ILIAS/Test");

        $this->initColumns();
        $this->initOrdering();
    }

    private function initColumns(): void
    {
        $this->addColumn($this->lng->txt("scored_pass"), '', '150');
        $this->addColumn($this->lng->txt("pass"), 'pass', '');
        $this->addColumn($this->lng->txt("date"), 'finishdate', '');
        $this->addColumn($this->lng->txt("tst_answered_questions"), 'answered_questions', '');
        $this->addColumn($this->lng->txt("tst_reached_points"), 'reached_points', '');
        $this->addColumn($this->lng->txt("tst_percent_solved"), 'percentage', '');
        $this->addColumn('', '', '1%');
    }

    private function initOrdering(): void
    {
        $this->disable('sort');

        $this->setDefaultOrderField("pass");
        $this->setDefaultOrderDirection("asc");
    }

    public function fillRow(array $a_set): void
    {
        $this->ctrl->setParameter($this->parent_obj, 'active_id', $a_set['active_id']);
        $this->ctrl->setParameter($this->parent_obj, 'pass', $a_set['pass']);

        if ($a_set['is_scored_pass']) {
            $this->tpl->setCurrentBlock('selected_pass');
            $this->tpl->touchBlock('selected_pass');
            $this->tpl->parseCurrentBlock();
            $this->tpl->setVariable('CSS_ROW', 'tblrowmarked');
        }

        $this->tpl->setVariable("PASS_NR", $a_set['pass'] + 1);
        $this->tpl->setVariable("PASS_DATE", ilDatePresentation::formatDate(new ilDate($a_set['finishdate'], IL_CAL_UNIX)));
        $this->tpl->setVariable("PASS_ANSWERED_QUESTIONS", $a_set['answered_questions'] . " " . strtolower($this->lng->txt("of")) . " " . $a_set['total_questions']);
        $this->tpl->setVariable("PASS_REACHED_POINTS", $a_set['reached_points'] . " " . strtolower($this->lng->txt("of")) . " " . $a_set['max_points']);
        $this->tpl->setVariable("PASS_REACHED_PERCENTAGE", sprintf("%.2f%%", $a_set['percentage']));

        $this->tpl->setVariable("TXT_SHOW_PASS", $this->lng->txt('tst_edit_scoring'));
        $this->tpl->setVariable("HREF_SHOW_PASS", $this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
    }
}
