<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssSpecificFeedbackIdentifier.php';

/**
 * Class ilAssClozeTestFeedbackIdMap
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilAssSpecificFeedbackIdentifierList implements Iterator
{
    /**
     * @var ilAssSpecificFeedbackIdentifier[]
     */
    protected $map = array();
    
    /**
     * @param ilAssSpecificFeedbackIdentifier $identifier
     */
    protected function add(ilAssSpecificFeedbackIdentifier $identifier)
    {
        $this->map[] = $identifier;
    }
    
    /**
     * @param integer $questionId
     */
    public function load($questionId)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->queryF(
            "SELECT feedback_id, question, answer FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($questionId)
        );
        
        $feedbackIdByAnswerIndexMap = array();
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $identifier = new ilAssSpecificFeedbackIdentifier();
            
            $identifier->setQuestionId($questionId);
            
            $identifier->setQuestionIndex($row['question']);
            $identifier->setAnswerIndex($row['answer']);
            
            $identifier->setFeedbackId($row['feedback_id']);
            
            $this->add($identifier);
        }
    }
    
    /**
     * @return ilAssSpecificFeedbackIdentifier
     */
    public function current()
    {
        return current($this->map);
    }
    
    /**
     * @return ilAssSpecificFeedbackIdentifier
     */
    public function next()
    {
        return next($this->map);
    }
    
    /**
     * @return integer|null
     */
    public function key()
    {
        return key($this->map);
    }
    
    /**
     * @return bool
     */
    public function valid()
    {
        return key($this->map) !== null;
    }
    
    /**
     * @return ilAssSpecificFeedbackIdentifier
     */
    public function rewind()
    {
        return reset($this->map);
    }
    
    protected function getSpecificFeedbackTableName()
    {
        require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssClozeTestFeedback.php';
        return ilAssClozeTestFeedback::TABLE_NAME_SPECIFIC_FEEDBACK;
    }
}
