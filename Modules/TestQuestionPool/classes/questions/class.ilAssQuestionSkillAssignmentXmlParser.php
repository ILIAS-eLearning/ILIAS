<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Xml/classes/class.ilSaxParser.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignment.php';
require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImportList.php';

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
    public function __construct($xmlFile)
    {
        $this->parsingActive = false;
        $this->characterDataBuffer = null;
        $this->curQuestionId = null;
        $this->curAssignment = null;
        $this->curExpression = null;
        $this->assignmentList = new ilAssQuestionSkillAssignmentImportList();
        return parent::__construct($xmlFile);
    }
    
    /**
     * @return boolean
     */
    public function isParsingActive()
    {
        return $this->parsingActive;
    }
    
    /**
     * @param boolean $parsingActive
     */
    public function setParsingActive($parsingActive)
    {
        $this->parsingActive = $parsingActive;
    }
    
    /**
     * @return string
     */
    protected function getCharacterDataBuffer()
    {
        return $this->characterDataBuffer;
    }
    
    /**
     * @param string $characterDataBuffer
     */
    protected function resetCharacterDataBuffer()
    {
        $this->characterDataBuffer = '';
    }
    
    /**
     * @param string $characterData
     */
    protected function appendToCharacterDataBuffer($characterData)
    {
        $this->characterDataBuffer .= $characterData;
    }
    
    /**
     * @return int
     */
    public function getCurQuestionId()
    {
        return $this->curQuestionId;
    }
    
    /**
     * @param int $curQuestionId
     */
    public function setCurQuestionId($curQuestionId)
    {
        $this->curQuestionId = $curQuestionId;
    }
    
    /**
     * @return ilAssQuestionSkillAssignmentImport
     */
    public function getCurAssignment()
    {
        return $this->curAssignment;
    }
    
    /**
     * @param ilAssQuestionSkillAssignmentImport $curAssignment
     */
    public function setCurAssignment($curAssignment)
    {
        $this->curAssignment = $curAssignment;
    }
    
    /**
     * @return ilAssQuestionSkillAssignmentImportList
     */
    public function getAssignmentList()
    {
        return $this->assignmentList;
    }
    
    /**
     * @return ilAssQuestionSolutionComparisonExpressionImport
     */
    public function getCurExpression()
    {
        return $this->curExpression;
    }
    
    /**
     * @param ilAssQuestionSolutionComparisonExpressionImport $curExpression
     */
    public function setCurExpression($curExpression)
    {
        $this->curExpression = $curExpression;
    }
    
    public function setHandlers($xmlParser)
    {
        xml_set_object($xmlParser, $this);
        xml_set_element_handler($xmlParser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($xmlParser, 'handlerCharacterData');
    }
    
    public function handlerBeginTag($xmlParser, $tagName, $tagAttributes)
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
            
            case 'OriginalSkillTitle':
                $this->resetCharacterDataBuffer();
                break;
            
            case 'OriginalSkillPath':
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
    
    public function handlerEndTag($xmlParser, $tagName)
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
            
            case 'EvalByQuestionResult':
                break;
            
            case 'EvalByQuestionSolution':
                break;
            
            case 'SolutionComparisonExpression':
                $this->getCurExpression()->setExpression($this->getCharacterDataBuffer());
                $this->getCurAssignment()->getImportSolutionComparisonExpressionList()->addExpression($this->getCurExpression());
                $this->setCurExpression(null);
                $this->resetCharacterDataBuffer();
                break;
        }
    }
    
    public function handlerCharacterData($xmlParser, $charData)
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
