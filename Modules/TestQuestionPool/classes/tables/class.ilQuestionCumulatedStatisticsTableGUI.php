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
 * Class ilQuestionUsagesTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilQuestionCumulatedStatisticsTableGUI extends ilTable2GUI
{
    /**
     * @var assQuestion
     */
    protected $question;

    /**
     * @param string                 $cmd
     * @param string                 $template_context
     * @param assQuestion            $question
     */
    public function __construct($controller, $cmd, $template_context, assQuestion $question)
    {
        $this->question = $question;
        $this->setId('qst_usage_' . $question->getId());
        parent::__construct($controller, $cmd);

        $this->setRowTemplate('tpl.il_as_qpl_question_cumulated_stats_table_row.html', 'Modules/TestQuestionPool');
        $this->setLimit(PHP_INT_MAX);

        $this->setDefaultOrderField('result');
        $this->setDefaultOrderDirection('ASC');

        $this->setTitle($this->lng->txt('question_cumulated_statistics'));
        $this->setNoEntriesText($this->lng->txt('qpl_assessment_no_assessment_of_questions'));

        $this->disable('sort');
        $this->disable('hits');
        $this->disable('numinfo');

        $this->initColumns();
        $this->initData();
    }

    /**
     *
     */
    protected function initColumns(): void
    {
        $this->addColumn($this->lng->txt('result'), 'result');
        $this->addColumn($this->lng->txt('value'), 'value');
    }

    /**
     *
     */
    protected function initData(): void
    {
        $rows = array();

        $total_of_answers = $this->question->getTotalAnswers();

        if ($total_of_answers) {
            $rows[] = array(
                'result' => $this->lng->txt('qpl_assessment_total_of_answers'),
                'value' => $total_of_answers,
                'is_percent' => false
            );

            $rows[] = array(
                'result' => $this->lng->txt('qpl_assessment_total_of_right_answers'),
                'value' => assQuestion::_getTotalRightAnswers($this->question->getId()) * 100.0,
                'is_percent' => true
            );
        } else {
            $this->disable('header');
        }

        $this->setData($rows);
    }

    /**
     * @param string $a_field
     * @return bool
     */
    public function numericOrdering(string $a_field): bool
    {
        if ('value' == $a_field) {
            return true;
        }

        return false;
    }

    /**
     * @param array $a_set
     */
    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_RESULT', $a_set['result']);
        $this->tpl->setVariable('VAL_VALUE', $a_set['is_percent'] ? sprintf("%2.2f", $a_set['value'])
                                                                  . ' %' : $a_set['value']);
    }
}
