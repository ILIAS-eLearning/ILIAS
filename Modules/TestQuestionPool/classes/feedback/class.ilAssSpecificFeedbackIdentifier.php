<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssClozeTestSpecificFeedbackIdentifier
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilAssSpecificFeedbackIdentifier
{
    /**
     * @var integer
     */
    protected $feedbackId;
    
    /**
     * @var integer
     */
    protected $questionId;
    
    /**
     * @var integer
     */
    protected $questionIndex;
    
    /**
     * @var integer
     */
    protected $answerIndex;
    
    /**
     * @return int
     */
    public function getFeedbackId()
    {
        return $this->feedbackId;
    }
    
    /**
     * @param int $feedbackId
     */
    public function setFeedbackId($feedbackId)
    {
        $this->feedbackId = $feedbackId;
    }
    
    /**
     * @return int
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }
    
    /**
     * @param int $questionId
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;
    }
    
    /**
     * @return int
     */
    public function getQuestionIndex()
    {
        return $this->questionIndex;
    }
    
    /**
     * @param int $questionIndex
     */
    public function setQuestionIndex($questionIndex)
    {
        $this->questionIndex = $questionIndex;
    }
    
    /**
     * @return int
     */
    public function getAnswerIndex()
    {
        return $this->answerIndex;
    }
    
    /**
     * @param int $answerIndex
     */
    public function setAnswerIndex($answerIndex)
    {
        $this->answerIndex = $answerIndex;
    }
}
