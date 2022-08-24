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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentXmlParser extends ilSaxParser
{
    /**
     * @var bool
     */
    protected $parsingActive;

    /**
     * @var string
     */
    protected $characterDataBuffer;

    /**
     * @var integer
     */
    protected $curQuestionId;

    /**
     * @var ilAssQuestionSkillAssignmentImport
     */
    protected $curAssignment;

    /**
     * @var ilAssQuestionSolutionComparisonExpressionImport
     */
    protected $curExpression;

    /**
     * @var ilAssQuestionSkillAssignmentImportList
     */
    protected $assignmentList;

    /**
     * @param $xmlFile
     */
    public function __construct(?string $xmlFile)
    {
        $this->parsingActive = false;
        $this->characterDataBuffer = null;
        $this->curQuestionId = null;
        $this->curAssignment = null;
        $this->curExpression = null;
        $this->assignmentList = new ilAssQuestionSkillAssignmentImportList();
        return parent::__construct($xmlFile);
    }

    public function isParsingActive(): bool
    {
        return $this->parsingActive;
    }

    public function setParsingActive(bool $parsingActive): void
    {
        $this->parsingActive = $parsingActive;
    }

    protected function getCharacterDataBuffer(): string
    {
        return $this->characterDataBuffer;
    }

    /**
     * @param string $characterDataBuffer
     */
    protected function resetCharacterDataBuffer(): void
    {
        $this->characterDataBuffer = '';
    }

    protected function appendToCharacterDataBuffer(string $characterData): void
    {
        $this->characterDataBuffer .= $characterData;
    }

    public function getCurQuestionId(): int
    {
        return $this->curQuestionId;
    }

    public function setCurQuestionId(?int $curQuestionId): void
    {
        $this->curQuestionId = (int) $curQuestionId;
    }

    public function getCurAssignment(): \ilAssQuestionSkillAssignmentImport
    {
        return $this->curAssignment;
    }

    public function setCurAssignment(\ilAssQuestionSkillAssignmentImport $curAssignment): void
    {
        $this->curAssignment = $curAssignment;
    }

    public function getAssignmentList(): \ilAssQuestionSkillAssignmentImportList
    {
        return $this->assignmentList;
    }

    public function getCurExpression(): \ilAssQuestionSolutionComparisonExpressionImport
    {
        return $this->curExpression;
    }

    public function setCurExpression(\ilAssQuestionSolutionComparisonExpressionImport $curExpression): void
    {
        $this->curExpression = $curExpression;
    }

    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    public function handlerBeginTag($xmlParser, $tagName, $tagAttributes): void
    {
        if ($tagName != 'QuestionSkillAssignments' && !$this->isParsingActive()) {
            return;
        }

        switch ($tagName) {
            case 'QuestionSkillAssignments':
                $this->setParsingActive(true);
                break;

            case 'TriggerQuestion':
                $this->setCurQuestionId((int) $tagAttributes['Id']);
                break;

            case 'TriggeredSkill':
                $assignment = new ilAssQuestionSkillAssignmentImport();
                $assignment->setImportQuestionId($this->getCurQuestionId());
                $assignment->setImportSkillBaseId((int) $tagAttributes['BaseId']);
                $assignment->setImportSkillTrefId((int) $tagAttributes['TrefId']);
                $assignment->initImportSolutionComparisonExpressionList();
                $this->setCurAssignment($assignment);
                break;

            case 'OriginalSkillPath':
            case 'OriginalSkillTitle':
                $this->resetCharacterDataBuffer();
                break;

            case 'EvalByQuestionResult':
                $this->getCurAssignment()->setEvalMode(ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT);
                $this->getCurAssignment()->setSkillPoints((int) $tagAttributes['Points']);
                break;

            case 'EvalByQuestionSolution':
                $this->getCurAssignment()->setEvalMode(ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION);
                break;

            case 'SolutionComparisonExpression':
                $expression = new ilAssQuestionSolutionComparisonExpressionImport();
                $expression->setPoints((int) $tagAttributes['Points']);
                $expression->setOrderIndex((int) $tagAttributes['Index']);
                $this->setCurExpression($expression);
                $this->resetCharacterDataBuffer();
                break;
        }
    }

    public function handlerEndTag($xmlParser, $tagName): void
    {
        if (!$this->isParsingActive()) {
            return;
        }

        switch ($tagName) {
            case 'QuestionSkillAssignments':
                $this->setParsingActive(false);
                break;

            case 'TriggerQuestion':
                $this->setCurQuestionId(null);
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

    public function handlerCharacterData($xmlParser, $charData): void
    {
        if (!$this->isParsingActive()) {
            return;
        }

        if ($charData != "\n") {
            // Replace multiple tabs with one space
            $charData = preg_replace("/\t+/", " ", $charData);

            $this->appendToCharacterDataBuffer($charData);
        }
    }
}
