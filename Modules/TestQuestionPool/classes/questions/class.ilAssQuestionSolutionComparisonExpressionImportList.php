<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSolutionComparisonExpressionImport.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSolutionComparisonExpressionImportList implements Iterator
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
     * @var array
     */
    private $expressions;
    
    /**
     * ilAssQuestionSolutionComparisonExpressionImportList constructor.
     */
    public function __construct()
    {
        $this->importQuestionId = null;
        $this->importSkillBaseId = null;
        $this->importSkillTrefId = null;
        
        $this->expressions = array();
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
     * @return array
     */
    public function getExpressions()
    {
        return $this->expressions;
    }
    
    public function addExpression(ilAssQuestionSolutionComparisonExpressionImport $expression)
    {
        $expression->setImportQuestionId($this->getImportQuestionId());
        $expression->setImportSkillBaseId($this->getImportSkillBaseId());
        $expression->setImportSkillTrefId($this->getImportSkillTrefId());
        
        $this->expressions[$expression->getOrderIndex()] = $expression;
    }
    
    /**
     * @return ilAssQuestionSolutionComparisonExpressionImport
     */
    public function current()
    {
        return current($this->expressions);
    }
    
    /**
     * @return ilAssQuestionSolutionComparisonExpressionImport
     */
    public function next()
    {
        return next($this->expressions);
    }
    
    /**
     * @return integer|bool
     */
    public function key()
    {
        return key($this->expressions);
    }
    
    /**
     * @return bool
     */
    public function valid()
    {
        return key($this->expressions) !== null;
    }
    
    /**
     * @return ilAssQuestionSolutionComparisonExpressionImport|bool
     */
    public function rewind()
    {
        return reset($this->expressions);
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
