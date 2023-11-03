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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentExporter
{
    /**
     * @var ilXmlWriter
     */
    protected $xmlWriter;

    /**
     * @var array
     */
    protected $questionIds;

    /**
     * @var ilAssQuestionSkillAssignmentList
     */
    protected $assignmentList;

    /**
     * ilAssQuestionSkillAssignmentExporter constructor.
     */
    public function __construct()
    {
        $this->xmlWriter = null;
        $this->questionIds = array();
        $this->assignmentList = null;
    }

    /**
     * @return ilXmlWriter
     */
    public function getXmlWriter(): ?ilXmlWriter
    {
        return $this->xmlWriter;
    }

    /**
     * @param ilXmlWriter $xmlWriter
     */
    public function setXmlWriter(ilXmlWriter $xmlWriter): void
    {
        $this->xmlWriter = $xmlWriter;
    }

    /**
     * @return array
     */
    public function getQuestionIds(): array
    {
        return $this->questionIds;
    }

    /**
     * @param array $questionIds
     */
    public function setQuestionIds($questionIds): void
    {
        $this->questionIds = $questionIds;
    }

    /**
     * @return ilAssQuestionSkillAssignmentList
     */
    public function getAssignmentList(): ?ilAssQuestionSkillAssignmentList
    {
        return $this->assignmentList;
    }

    /**
     * @param ilAssQuestionSkillAssignmentList $assignmentList
     */
    public function setAssignmentList($assignmentList): void
    {
        $this->assignmentList = $assignmentList;
    }

    public function export(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $this->getXmlWriter()->xmlStartTag('QuestionSkillAssignments');

        foreach ($this->getQuestionIds() as $questionId) {
            $this->getXmlWriter()->xmlStartTag('TriggerQuestion', array('Id' => $questionId));

            foreach ($this->getAssignmentList()->getAssignmentsByQuestionId($questionId) as $questionSkillAssignment) {
                /* @var ilAssQuestionSkillAssignment $questionSkillAssignment */

                $this->getXmlWriter()->xmlStartTag('TriggeredSkill', array(
                    'BaseId' => $questionSkillAssignment->getSkillBaseId(),
                    'TrefId' => $questionSkillAssignment->getSkillTrefId()
                ));

                $this->getXmlWriter()->xmlElement(
                    'OriginalSkillTitle',
                    null,
                    $questionSkillAssignment->getSkillTitle()
                );

                $this->getXmlWriter()->xmlElement(
                    'OriginalSkillPath',
                    null,
                    $questionSkillAssignment->getSkillPath()
                );

                switch ($questionSkillAssignment->getEvalMode()) {
                    case ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT:

                        $this->getXmlWriter()->xmlElement('EvalByQuestionResult', array(
                            'Points' => $questionSkillAssignment->getSkillPoints()
                        ));
                        break;

                    case ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION:

                        $this->getXmlWriter()->xmlStartTag('EvalByQuestionSolution');

                        $questionSkillAssignment->initSolutionComparisonExpressionList();
                        $expressionList = $questionSkillAssignment->getSolutionComparisonExpressionList();

                        foreach ($expressionList->get() as $expression) {
                            /* @var ilAssQuestionSolutionComparisonExpression $expression */

                            $this->getXmlWriter()->xmlStartTag('SolutionComparisonExpression', array(
                                'Points' => $expression->getPoints(),
                                'Index' => $expression->getOrderIndex()
                            ));

                            $this->getXmlWriter()->xmlData($expression->getExpression(), false, true);

                            $this->getXmlWriter()->xmlEndTag('SolutionComparisonExpression');
                        }

                        $this->getXmlWriter()->xmlEndTag('EvalByQuestionSolution');
                        break;
                }

                $this->getXmlWriter()->xmlEndTag('TriggeredSkill');
            }

            $this->getXmlWriter()->xmlEndTag('TriggerQuestion');
        }

        $this->getXmlWriter()->xmlEndTag('QuestionSkillAssignments');
    }
}
