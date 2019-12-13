<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Tree/classes/class.ilPathGUI.php';
require_once 'Services/Link/classes/class.ilLink.php';

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
     * @param ilUnitConfigurationGUI $controller
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
    protected function initColumns()
    {
        $this->addColumn($this->lng->txt('result'), 'result');
        $this->addColumn($this->lng->txt('value'), 'value');
    }

    /**
     *
     */
    protected function initData()
    {
        $rows = array();

        $total_of_answers = $this->question->getTotalAnswers();

        if ($total_of_answers) {
            $rows[] = array(
                'result' => $this->lng->txt('qpl_assessment_total_of_answers'),
                'value'  => $total_of_answers,
                'is_percent' => false
            );

            $rows[] = array(
                'result' => $this->lng->txt('qpl_assessment_total_of_right_answers'),
                'value'  => assQuestion::_getTotalRightAnswers($this->question->getId()) * 100.0,
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
    public function numericOrdering($a_field)
    {
        if ('value' == $a_field) {
            return true;
        }

        return false;
    }

    /**
     * @param array $row
     */
    public function fillRow($row)
    {
        $this->tpl->setVariable('VAL_RESULT', $row['result']);
        $this->tpl->setVariable('VAL_VALUE', $row['is_percent'] ? sprintf("%2.2f", $row['value'])
                                                                  . ' %' : $row['value']);
    }
}
