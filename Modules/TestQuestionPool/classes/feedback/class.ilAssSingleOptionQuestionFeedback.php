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
     */
    public function getSpecificAnswerFeedbackTestPresentation(int $questionId, int $questionIndex, int $answerIndex): string
    {
        return '';
    }

    /**
     * completes a given form object with the specific form properties
     * required by this question type
     *
     * @access public
     * @param ilPropertyFormGUI $form
     */
    public function completeSpecificFormProperties(ilPropertyFormGUI $form): void
    {
    }

    /**
     * initialises a given form object's specific form properties
     * relating to this question type
     *
     * @access public
     * @param ilPropertyFormGUI $form
     */
    public function initSpecificFormProperties(ilPropertyFormGUI $form): void
    {
    }

    public function saveSpecificFormProperties(ilPropertyFormGUI $form): void
    {
    }

    public function getSpecificAnswerFeedbackContent(int $questionId, int $questionIndex, int $answerIndex): string
    {
        return '';
    }

    public function getAllSpecificAnswerFeedbackContents(int $questionId): string
    {
        return '';
    }

    public function saveSpecificAnswerFeedbackContent(int $questionId, int $questionIndex, int $answerIndex, string $feedbackContent): int
    {
        return -1;
    }

    public function deleteSpecificAnswerFeedbacks(int $questionId, bool $isAdditionalContentEditingModePageObject): void
    {
    }

    protected function duplicateSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId): void
    {
    }

    protected function syncSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId): void
    {
    }

    public function getSpecificAnswerFeedbackExportPresentation(int $questionId, int $questionIndex, int $answerIndex): string
    {
        return '';
    }

    public function importSpecificAnswerFeedback(int $questionId, int $questionIndex, int $answerIndex, string $feedbackContent): void
    {
    }

    protected function isSpecificAnswerFeedbackId(int $feedbackId): bool
    {
        return false;
    }
}
