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
    protected array $map = array();
    
    protected function add(ilAssSpecificFeedbackIdentifier $identifier) : void
    {
        $this->map[] = $identifier;
    }
    
    public function load(int $questionId) : void
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->queryF(
            "SELECT feedback_id, question, answer FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($questionId)
        );

        while ($row = $DIC->database()->fetchAssoc($res)) {
            $identifier = new ilAssSpecificFeedbackIdentifier();
            
            $identifier->setQuestionId($questionId);
            
            $identifier->setQuestionIndex($row['question']);
            $identifier->setAnswerIndex($row['answer']);
            
            $identifier->setFeedbackId($row['feedback_id']);
            
            $this->add($identifier);
        }
    }

    public function current() : ilAssSpecificFeedbackIdentifier
    {
        return current($this->map);
    }

    public function next() : ilAssSpecificFeedbackIdentifier
    {
        return next($this->map);
    }
    
    public function key() : ?int
    {
        return key($this->map);
    }
    
    public function valid() : bool
    {
        return key($this->map) !== null;
    }
    
    public function rewind() : ilAssSpecificFeedbackIdentifier
    {
        return reset($this->map);
    }
    
    protected function getSpecificFeedbackTableName() : string
    {
        require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssClozeTestFeedback.php';
        return ilAssClozeTestFeedback::TABLE_NAME_SPECIFIC_FEEDBACK;
    }
}
