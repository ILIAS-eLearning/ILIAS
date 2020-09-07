<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSolutionComparisonExpressionImportList.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentImport
{
    /**
     * @var integer
     */
    private $importQuestionId;
    
    /**
     * @var integer
     */
    private $importSkillBaseId;
    
    /**
     * @var integer
     */
    private $importSkillTrefId;
    
    /**
     * @var string
     */
    private $importSkillTitle;
    
    /**
     * @var string
     */
    private $importSkillPath;
    
    /**
     * @var integer
     */
    private $skillPoints;
    
    /**
     * @var string
     */
    private $evalMode;
    
    /**
     * @var ilAssQuestionSolutionComparisonExpressionImportList
     */
    private $importSolutionComparisonExpressionList;
    
    /**
     * ilAssQuestionSkillAssignmentImport constructor.
     */
    public function __construct()
    {
        $this->importSolutionComparisonExpressionList = new ilAssQuestionSolutionComparisonExpressionImportList();
    }
        
    /**
     * @param int $questionId
     */
    public function setImportQuestionId($importQuestionId)
    {
        $this->importQuestionId = $importQuestionId;
    }
    
    /**
     * @return int
     */
    public function getImportQuestionId()
    {
        return $this->importQuestionId;
    }
    
    /**
     * @param int $skillBaseId
     */
    public function setImportSkillBaseId($importSkillBaseId)
    {
        $this->importSkillBaseId = $importSkillBaseId;
    }
    
    /**
     * @return int
     */
    public function getImportSkillBaseId()
    {
        return $this->importSkillBaseId;
    }
    
    /**
     * @param int $skillTrefId
     */
    public function setImportSkillTrefId($importSkillTrefId)
    {
        $this->importSkillTrefId = $importSkillTrefId;
    }
    
    /**
     * @return int
     */
    public function getImportSkillTrefId()
    {
        return $this->importSkillTrefId;
    }
    
    /**
     * @return string
     */
    public function getImportSkillTitle()
    {
        return $this->importSkillTitle;
    }
    
    /**
     * @param string $importSkillTitle
     */
    public function setImportSkillTitle($importSkillTitle)
    {
        $this->importSkillTitle = $importSkillTitle;
    }
    
    /**
     * @return string
     */
    public function getImportSkillPath()
    {
        return $this->importSkillPath;
    }
    
    /**
     * @param string $importSkillPath
     */
    public function setImportSkillPath($importSkillPath)
    {
        $this->importSkillPath = $importSkillPath;
    }
    
    /**
     * @return string
     */
    public function getEvalMode()
    {
        return $this->evalMode;
    }
    
    /**
     * @param $evalMode
     */
    public function setEvalMode($evalMode)
    {
        $this->evalMode = $evalMode;
    }
    
    /**
     * @return bool
     */
    public function hasImportEvalModeBySolution()
    {
        return $this->getEvalMode() == ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION;
    }
    
    /**
     * @param int $skillPoints
     */
    public function setSkillPoints($skillPoints)
    {
        $this->skillPoints = $skillPoints;
    }
    
    /**
     * @return int
     */
    public function getSkillPoints()
    {
        return $this->skillPoints;
    }
    
    public function initImportSolutionComparisonExpressionList()
    {
        $this->importSolutionComparisonExpressionList->setImportQuestionId($this->getImportQuestionId());
        $this->importSolutionComparisonExpressionList->setImportSkillBaseId($this->getImportSkillBaseId());
        $this->importSolutionComparisonExpressionList->setImportSkillTrefId($this->getImportSkillTrefId());
    }
    
    /**
     * @return ilAssQuestionSolutionComparisonExpressionImportList
     */
    public function getImportSolutionComparisonExpressionList()
    {
        return $this->importSolutionComparisonExpressionList;
    }
    
    public function sleep()
    {
        // TODO: Implement __sleep() method.
    }
    
    public function wakeup()
    {
        // TODO: Implement __wakeup() method.
    }
}
