<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSolutionComparisonExpressionImport
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
     * @var integer
     */
    private $orderIndex;
    
    /**
     * @var string
     */
    private $expression;
    
    /**
     * @var integer
     */
    private $points;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->importQuestionId = null;
        $this->importSkillBaseId = null;
        $this->importSkillTrefId = null;
        $this->orderIndex = null;
        $this->expression = null;
        $this->points = null;
    }

    /**
     * @return int
     */
    public function getImportQuestionId()
    {
        return $this->importQuestionId;
    }
    
    /**
     * @param int $importQuestionId
     */
    public function setImportQuestionId($importQuestionId)
    {
        $this->importQuestionId = $importQuestionId;
    }
    
    /**
     * @return int
     */
    public function getImportSkillBaseId()
    {
        return $this->importSkillBaseId;
    }
    
    /**
     * @param int $importSkillBaseId
     */
    public function setImportSkillBaseId($importSkillBaseId)
    {
        $this->importSkillBaseId = $importSkillBaseId;
    }
    
    /**
     * @return int
     */
    public function getImportSkillTrefId()
    {
        return $this->importSkillTrefId;
    }
    
    /**
     * @param int $importSkillTrefId
     */
    public function setImportSkillTrefId($importSkillTrefId)
    {
        $this->importSkillTrefId = $importSkillTrefId;
    }
    
    /**
     * @return int
     */
    public function getOrderIndex()
    {
        return $this->orderIndex;
    }
    
    /**
     * @param int $orderIndex
     */
    public function setOrderIndex($orderIndex)
    {
        $this->orderIndex = $orderIndex;
    }
    
    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }
    
    /**
     * @param string $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }
    
    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }
    
    /**
     * @param int $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
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
