<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssQuestionFeedback.php';

/**
 * abstract parent feedback class for question types
 * with single answer options (numeric, essey, ...)
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @abstract
 */
abstract class ilAssSingleOptionQuestionFeedback extends ilAssQuestionFeedback
{
    /**
     * returns the html of SPECIFIC feedback for the given question id
     * and answer index for test presentation
     *
     * @access public
     * @param integer $questionId
     * @param integer $questionIndex
     * @param integer $answerIndex
     * @return string $specificAnswerFeedbackTestPresentationHTML
     */
    public function getSpecificAnswerFeedbackTestPresentation($questionId, $questionIndex, $answerIndex)
    {
        return null;
    }
    
    /**
     * completes a given form object with the specific form properties
     * required by this question type
     *
     * @access public
     * @param ilPropertyFormGUI $form
     */
    public function completeSpecificFormProperties(ilPropertyFormGUI $form)
    {
    }
    
    /**
     * initialises a given form object's specific form properties
     * relating to this question type
     *
     * @access public
     * @param ilPropertyFormGUI $form
     */
    public function initSpecificFormProperties(ilPropertyFormGUI $form)
    {
    }
    
    /**
     * saves a given form object's specific form properties
     * relating to this question type
     *
     * @access public
     * @param ilPropertyFormGUI $form
     */
    public function saveSpecificFormProperties(ilPropertyFormGUI $form)
    {
    }

    /**
     * returns the SPECIFIC answer feedback content for a given question id and answer index.
     *
     * @access public
     * @param integer $questionId
     * @param integer $questionIndex
     * @param boolean $answerIndex
     * @return string $feedbackContent
     */
    public function getSpecificAnswerFeedbackContent($questionId, $questionIndex, $answerIndex)
    {
        return '';
    }
    
    /**
     * returns the SPECIFIC feedback content for a given question id and answer index.
     *
     * @abstract
     * @access public
     * @param integer $questionId
     * @return string $feedbackContent
     */
    public function getAllSpecificAnswerFeedbackContents($questionId)
    {
        return '';
    }
    
    /**
     * saves SPECIFIC answer feedback content for the given question id and answer index to the database.
     *
     * @access public
     * @param integer $questionId
     * @param integer $questionIndex
     * @param integer $answerIndex
     * @param string $feedbackContent
     * @return integer $feedbackId
     */
    public function saveSpecificAnswerFeedbackContent($questionId, $questionIndex, $answerIndex, $feedbackContent)
    {
        return null;
    }
        
    /**
     * deletes all SPECIFIC answer feedback contents (and page objects if required)
     * for the given question id
     *
     * @access public
     * @param integer $questionId
     * @param boolean $isAdditionalContentEditingModePageObject
     */
    public function deleteSpecificAnswerFeedbacks($questionId, $isAdditionalContentEditingModePageObject)
    {
    }
    
    /**
     * duplicates the SPECIFIC feedback relating to the given original question id
     * and saves it for the given duplicate question id
     *
     * @access protected
     * @param integer $originalQuestionId
     * @param integer $duplicateQuestionId
     */
    protected function duplicateSpecificFeedback($originalQuestionId, $duplicateQuestionId)
    {
    }
    
    /**
     * syncs the SPECIFIC feedback from a duplicated question back to the original question
     *
     * @access protected
     * @param integer $originalQuestionId
     * @param integer $duplicateQuestionId
     */
    protected function syncSpecificFeedback($originalQuestionId, $duplicateQuestionId)
    {
    }
    
    /**
     * returns the generic feedback export presentation for given question id
     * either for solution completed or incompleted
     *
     * @access public
     * @param integer $questionId
     * @param integer $questionIndex
     * @param integer $answerIndex
     * @return string $specificAnswerFeedbackExportPresentation
     */
    public function getSpecificAnswerFeedbackExportPresentation($questionId, $questionIndex, $answerIndex)
    {
        return null;
    }
    
    /**
     * imports the given feedback content as specific feedback
     * for the given question id and answer index
     *
     * @access public
     * @param integer $questionId
     * @param integer $questionIndex
     * @param integer $answerIndex
     * @param string $feedbackContent
     */
    public function importSpecificAnswerFeedback($questionId, $questionIndex, $answerIndex, $feedbackContent)
    {
    }
    
    /**
     * @param int $feedbackId
     * @return bool
     */
    protected function isSpecificAnswerFeedbackId($feedbackId)
    {
        return false;
    }
}
