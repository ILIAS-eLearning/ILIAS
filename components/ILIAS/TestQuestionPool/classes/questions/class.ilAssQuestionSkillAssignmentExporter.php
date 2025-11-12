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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentExporter
{
    protected ?ilXmlWriter $xml_writer = null;

    protected array $question_ids = [];

    protected ?ilAssQuestionSkillAssignmentList $assignment_list = null;

    public function getXmlWriter(): ?ilXmlWriter
    {
        return $this->xml_writer;
    }

    public function setXmlWriter(ilXmlWriter $xml_writer): void
    {
        $this->xml_writer = $xml_writer;
    }

    public function getQuestionIds(): array
    {
        return $this->question_ids;
    }

    public function setQuestionIds(array $question_ids): void
    {
        $this->question_ids = $question_ids;
    }

    public function getAssignmentList(): ?ilAssQuestionSkillAssignmentList
    {
        return $this->assignment_list;
    }

    public function setAssignmentList(ilAssQuestionSkillAssignmentList $assignment_list): void
    {
        $this->assignment_list = $assignment_list;
    }

    public function export(): void
    {
        $this->getXmlWriter()->xmlStartTag('SkillAssignments');

        foreach ($this->getQuestionIds() as $question_id) {
            $this->getXmlWriter()->xmlStartTag('TriggerQuestion', ['Id' => $question_id]);

            foreach ($this->getAssignmentList()->getAssignmentsByQuestionId($question_id) as $question_skill_assignment) {
                /* @var ilAssQuestionSkillAssignment $question_skill_assignment */

                $this->getXmlWriter()->xmlStartTag(
                    'TriggeredSkill',
                    [
                        'BaseId' => $question_skill_assignment->getSkillBaseId(),
                        'TrefId' => $question_skill_assignment->getSkillTrefId()
                    ]
                );

                $this->getXmlWriter()->xmlElement(
                    'OriginalSkillTitle',
                    null,
                    $question_skill_assignment->getSkillTitle()
                );

                $this->getXmlWriter()->xmlElement(
                    'OriginalSkillPath',
                    null,
                    $question_skill_assignment->getSkillPath()
                );

                switch ($question_skill_assignment->getEvalMode()) {
                    case ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT:

                        $this->getXmlWriter()->xmlElement(
                            'EvalByQuestionResult',
                            ['Points' => $question_skill_assignment->getSkillPoints()]
                        );
                        break;

                    case ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION:

                        $this->getXmlWriter()->xmlStartTag('EvalByQuestionSolution');

                        $question_skill_assignment->initSolutionComparisonExpressionList();
                        $expression_list = $question_skill_assignment->getSolutionComparisonExpressionList();

                        foreach ($expression_list->get() as $expression) {
                            /* @var ilAssQuestionSolutionComparisonExpression $expression */

                            $this->getXmlWriter()->xmlStartTag(
                                'SolutionComparisonExpression',
                                [
                                    'Points' => $expression->getPoints(),
                                    'Index' => $expression->getOrderIndex()
                                ]
                            );

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

        $this->getXmlWriter()->xmlEndTag('SkillAssignments');
    }
}
