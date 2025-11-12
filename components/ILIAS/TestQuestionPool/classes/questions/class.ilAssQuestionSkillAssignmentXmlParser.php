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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package components\ILIAS/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentXmlParser extends ilSaxParser
{
    protected bool $parsing_active = false;

    protected string $character_data_buffer = '';

    protected ?int $cur_question_id = null;

    protected ?ilAssQuestionSkillAssignmentImport $cur_assignment = null;

    protected ?ilAssQuestionSolutionComparisonExpressionImport $cur_expression = null;

    protected ilAssQuestionSkillAssignmentImportList $assignment_list;

    public function __construct(?string $xml_file)
    {
        $this->assignment_list = new ilAssQuestionSkillAssignmentImportList();
        parent::__construct($xml_file);
    }

    public function isParsingActive(): bool
    {
        return $this->parsing_active;
    }

    public function setParsingActive(bool $parsing_active): void
    {
        $this->parsing_active = $parsing_active;
    }

    protected function getCharacterDataBuffer(): string
    {
        return $this->character_data_buffer;
    }

    protected function resetCharacterDataBuffer(): void
    {
        $this->character_data_buffer = '';
    }

    protected function appendToCharacterDataBuffer(string $characterData): void
    {
        $this->character_data_buffer .= $characterData;
    }

    public function getCurQuestionid(): int
    {
        return $this->cur_question_id;
    }

    public function setCurQuestionid(?int $cur_question_id): void
    {
        $this->cur_question_id = (int) $cur_question_id;
    }

    public function getCurAssignment(): ilAssQuestionSkillAssignmentImport
    {
        return $this->cur_assignment;
    }

    public function setCurAssignment(?ilAssQuestionSkillAssignmentImport $cur_assignment): void
    {
        $this->cur_assignment = $cur_assignment;
    }

    public function getAssignmentList(): ilAssQuestionSkillAssignmentImportList
    {
        return $this->assignment_list;
    }

    public function getCurExpression(): ilAssQuestionSolutionComparisonExpressionImport
    {
        return $this->cur_expression;
    }

    public function setCurExpression(?ilAssQuestionSolutionComparisonExpressionImport $cur_expression): void
    {
        $this->cur_expression = $cur_expression;
    }

    public function setHandlers($a_xml_parser): void
    {
        xml_set_element_handler($a_xml_parser, $this->handlerBeginTag(...), $this->handlerEndTag(...));
        xml_set_character_data_handler($a_xml_parser, $this->handlerCharacterData(...));
    }

    public function handlerBeginTag($xml_parser, $tag_name, $tag_attributes): void
    {
        if ($tag_name !== 'SkillAssignments' && !$this->isParsingActive()) {
            return;
        }

        switch ($tag_name) {
            case 'SkillAssignments':
                $this->setParsingActive(true);
                break;

            case 'TriggerQuestion':
                $this->setCurQuestionid((int) $tag_attributes['Id']);
                break;

            case 'TriggeredSkill':
                $assignment = new ilAssQuestionSkillAssignmentImport();
                $assignment->setImportQuestionId($this->getCurQuestionid());
                $assignment->setImportSkillBaseId((int) $tag_attributes['BaseId']);
                $assignment->setImportSkillTrefId((int) $tag_attributes['TrefId']);
                $assignment->initImportSolutionComparisonExpressionList();
                $this->setCurAssignment($assignment);
                break;

            case 'OriginalSkillPath':
            case 'OriginalSkillTitle':
                $this->resetCharacterDataBuffer();
                break;

            case 'EvalByQuestionResult':
                $this->getCurAssignment()->setEvalMode(ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT);
                $this->getCurAssignment()->setSkillPoints((int) $tag_attributes['Points']);
                break;

            case 'EvalByQuestionSolution':
                $this->getCurAssignment()->setEvalMode(ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION);
                break;

            case 'SolutionComparisonExpression':
                $expression = new ilAssQuestionSolutionComparisonExpressionImport();
                $expression->setPoints((int) $tag_attributes['Points']);
                $expression->setOrderIndex((int) $tag_attributes['Index']);
                $this->setCurExpression($expression);
                $this->resetCharacterDataBuffer();
                break;
        }
    }

    public function handlerEndTag($xml_parser, $tag_name): void
    {
        if (!$this->isParsingActive()) {
            return;
        }

        switch ($tag_name) {
            case 'SkillAssignments':
                $this->setParsingActive(false);
                break;

            case 'TriggerQuestion':
                $this->setCurQuestionid(null);
                break;

            case 'TriggeredSkill':
                $this->getAssignmentList()->addAssignment($this->getCurAssignment());
                $this->setCurAssignment(null);
                break;

            case 'OriginalSkillTitle':
                $this->getCurAssignment()->setImportSkillTitle($this->getCharacterDataBuffer());
                $this->resetCharacterDataBuffer();
                break;

            case 'OriginalSkillPath':
                $this->getCurAssignment()->setImportSkillPath($this->getCharacterDataBuffer());
                $this->resetCharacterDataBuffer();
                break;

            case 'EvalByQuestionSolution':
            case 'EvalByQuestionResult':
                break;

            case 'SolutionComparisonExpression':
                $this->getCurExpression()->setExpression($this->getCharacterDataBuffer());
                $this->getCurAssignment()->getImportSolutionComparisonExpressionList()->addExpression($this->getCurExpression());
                $this->setCurExpression(null);
                $this->resetCharacterDataBuffer();
                break;
        }
    }

    public function handlerCharacterData($xml_parser, $char_data): void
    {
        if (!$this->isParsingActive()) {
            return;
        }

        if ($char_data !== "\n") {
            // Replace multiple tabs with one space
            $this->appendToCharacterDataBuffer(preg_replace("/\t+/", ' ', $char_data));
        }
    }
}
