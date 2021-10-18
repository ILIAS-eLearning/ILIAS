<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssQuestionFeedback.php';

/**
 * abstract parent feedback class for question types
 * with multiple answer options (mc, sc, ...)
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @abstract
 */
abstract class ilAssMultiOptionQuestionFeedback extends ilAssQuestionFeedback
{
    /**
     * table name for specific feedback
     */
    const TABLE_NAME_SPECIFIC_FEEDBACK = 'qpl_fb_specific';
    
    /**
     * returns the html of SPECIFIC feedback for the given question id
     * and answer index for test presentation
     */
    public function getSpecificAnswerFeedbackTestPresentation(int $questionId, int $questionIndex, int $answerIndex) : string
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $specificAnswerFeedbackTestPresentationHTML = $this->getPageObjectContent(
                $this->getSpecificAnswerFeedbackPageObjectType(),
                $this->getSpecificAnswerFeedbackPageObjectId($questionId, $questionIndex, $answerIndex)
            );
        } else {
            $specificAnswerFeedbackTestPresentationHTML = $this->getSpecificAnswerFeedbackContent(
                $questionId,
                $questionIndex,
                $answerIndex
            );
        }
                
        return $specificAnswerFeedbackTestPresentationHTML;
    }
    
    /**
     * completes a given form object with the specific form properties
     * required by this question type
     */
    public function completeSpecificFormProperties(ilPropertyFormGUI $form) : void
    {
        if (!$this->questionOBJ->getSelfAssessmentEditingMode()) {
            $header = new ilFormSectionHeaderGUI();
            $header->setTitle($this->lng->txt('feedback_answers'));
            $form->addItem($header);
        
            foreach ($this->getAnswerOptionsByAnswerIndex() as $index => $answer) {
                $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
                    $this->buildAnswerOptionLabel($index, $answer),
                    true
                );
                
                $propertyPostVar = "feedback_answer_$index";
                
                $form->addItem($this->buildFeedbackContentFormProperty(
                    $propertyLabel,
                    $propertyPostVar,
                    $this->questionOBJ->isAdditionalContentEditingModePageObject()
                ));
            }
        }
    }
    
    /**
     * initialises a given form object's specific form properties
     * relating to this question type
     */
    public function initSpecificFormProperties(ilPropertyFormGUI $form) : void
    {
        if (!$this->questionOBJ->getSelfAssessmentEditingMode()) {
            foreach ($this->getAnswerOptionsByAnswerIndex() as $index => $answer) {
                if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
                    $value = $this->getPageObjectNonEditableValueHTML(
                        $this->getSpecificAnswerFeedbackPageObjectType(),
                        $this->getSpecificAnswerFeedbackPageObjectId($this->questionOBJ->getId(), 0, $index)
                    );
                } else {
                    $value = $this->questionOBJ->prepareTextareaOutput(
                        $this->getSpecificAnswerFeedbackContent($this->questionOBJ->getId(), 0, $index)
                    );
                }
                
                $form->getItemByPostVar("feedback_answer_$index")->setValue($value);
            }
        }
    }
    
    public function saveSpecificFormProperties(ilPropertyFormGUI $form) : void
    {
        if (!$this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            foreach ($this->getAnswerOptionsByAnswerIndex() as $index => $answer) {
                $this->saveSpecificAnswerFeedbackContent(
                    $this->questionOBJ->getId(),
                    0,
                    $index,
                    $form->getInput("feedback_answer_$index")
                );
            }
        }
    }

    public function getSpecificAnswerFeedbackContent(int $questionId, int $questionIndex, int $answerIndex) : string
    {
        require_once 'Services/RTE/classes/class.ilRTE.php';
        
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getSpecificFeedbackTableName()}
					WHERE question_fi = %s AND question = %s AND answer = %s",
            array('integer','integer','integer'),
            array($questionId, $questionIndex, $answerIndex)
        );

        $feedbackContent = '';

        if ($this->db->numRows($res) > 0) {
            $row = $this->db->fetchAssoc($res);
            $feedbackContent = ilRTE::_replaceMediaObjectImageSrc($row['feedback'], 1);
        }
        
        return $feedbackContent;
    }
    
    public function getAllSpecificAnswerFeedbackContents(int $questionId) : string
    {
        require_once 'Services/RTE/classes/class.ilRTE.php';
        
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($questionId)
        );
        
        $allFeedbackContents = '';
        
        while ($row = $this->db->fetchAssoc($res)) {
            $allFeedbackContents .= ilRTE::_replaceMediaObjectImageSrc($row['feedback'], 1);
        }
        
        return $allFeedbackContents;
    }
    
    public function saveSpecificAnswerFeedbackContent(int $questionId, int $questionIndex, int $answerIndex, string $feedbackContent) : int
    {
        require_once 'Services/RTE/classes/class.ilRTE.php';
        
        if (strlen($feedbackContent)) {
            $feedbackContent = ilRTE::_replaceMediaObjectImageSrc($feedbackContent, 0);
        }
        
        $feedbackId = $this->getSpecificAnswerFeedbackId($questionId, $questionIndex, $answerIndex);
        
        if ($feedbackId !== -1) {
            $this->db->update(
                $this->getSpecificFeedbackTableName(),
                array(
                    'feedback' => array('text', $feedbackContent),
                    'tstamp' => array('integer', time())
                ),
                array(
                    'feedback_id' => array('integer', $feedbackId),
                )
            );
        } else {
            $feedbackId = $this->db->nextId($this->getSpecificFeedbackTableName());
            
            $this->db->insert($this->getSpecificFeedbackTableName(), array(
                'feedback_id' => array('integer', $feedbackId),
                'question_fi' => array('integer', $questionId),
                'question' => array('integer', $questionIndex),
                'answer' => array('integer', $answerIndex),
                'feedback' => array('text', $feedbackContent),
                'tstamp' => array('integer', time())
            ));
        }
        
        return $feedbackId;
    }
        
    public function deleteSpecificAnswerFeedbacks(int $questionId, bool $isAdditionalContentEditingModePageObject) : void
    {
        if ($isAdditionalContentEditingModePageObject) {
            require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssSpecificFeedbackIdentifierList.php';
            $feedbackIdentifiers = new ilAssSpecificFeedbackIdentifierList();
            $feedbackIdentifiers->load($questionId);
            
            foreach ($feedbackIdentifiers as $identifier) {
                $this->ensurePageObjectDeleted(
                    $this->getSpecificAnswerFeedbackPageObjectType(),
                    $identifier->getFeedbackId()
                );
            }
        }
        
        $this->db->manipulateF(
            "DELETE FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($questionId)
        );
    }
    
    protected function duplicateSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId) : void
    {
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($originalQuestionId)
        );
        
        while ($row = $this->db->fetchAssoc($res)) {
            $nextId = $this->db->nextId($this->getSpecificFeedbackTableName());
            
            $this->db->insert($this->getSpecificFeedbackTableName(), array(
                'feedback_id' => array('integer', $nextId),
                'question_fi' => array('integer', $duplicateQuestionId),
                'question' => array('integer', $row['question']),
                'answer' => array('integer', $row['answer']),
                'feedback' => array('text', $row['feedback']),
                'tstamp' => array('integer', time())
            ));
            
            if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
                $pageObjectType = $this->getSpecificAnswerFeedbackPageObjectType();
                $this->duplicatePageObject($pageObjectType, $row['feedback_id'], $nextId, $duplicateQuestionId);
            }
        }
    }
    
    protected function syncSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId) : void
    {
        // delete specific feedback of the original
        $this->db->manipulateF(
            "DELETE FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($originalQuestionId)
        );
            
        // get specific feedback of the actual question
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($duplicateQuestionId)
        );

        // save specific feedback to the original
        while ($row = $this->db->fetchAssoc($res)) {
            $nextId = $this->db->nextId($this->getSpecificFeedbackTableName());
            
            $this->db->insert($this->getSpecificFeedbackTableName(), array(
                'feedback_id' => array('integer', $nextId),
                'question_fi' => array('integer', $originalQuestionId),
                'question' => array('integer',$row['question']),
                'answer' => array('integer',$row['answer']),
                'feedback' => array('text',$row['feedback']),
                'tstamp' => array('integer',time())
            ));
        }
    }
    
    final protected function getSpecificAnswerFeedbackId(int $questionId, int $questionIndex, int $answerIndex) : int
    {
        $res = $this->db->queryF(
            "SELECT feedback_id FROM {$this->getSpecificFeedbackTableName()}
					WHERE question_fi = %s AND question = %s AND answer = %s",
            array('integer','integer','integer'),
            array($questionId, $questionIndex, $answerIndex)
        );

        $feedbackId = -1;

        if ($this->db->numRows($res) > 0) {
            $row = $this->db->fetchAssoc($res);
            $feedbackId = (int) $row['feedback_id'];
        }
        
        return $feedbackId;
    }
    
    protected function isSpecificAnswerFeedbackId(int $feedbackId) : bool
    {
        $row = $this->db->fetchAssoc($this->db->queryF(
            "SELECT COUNT(feedback_id) cnt FROM {$this->getSpecificFeedbackTableName()}
					WHERE question_fi = %s AND feedback_id = %s",
            array('integer' ,'integer'),
            array($this->questionOBJ->getId(), $feedbackId)
        ));
        
        return (bool) $row['cnt'];
    }

    final protected function getSpecificFeedbackTableName() : string
    {
        return self::TABLE_NAME_SPECIFIC_FEEDBACK;
    }
    
    public function getAnswerOptionsByAnswerIndex() : array
    {
        return $this->questionOBJ->getAnswers();
    }
    
    protected function buildAnswerOptionLabel(int $index, $answer) : string
    {
        return $answer->getAnswertext();
    }
    
    /**
     * returns a useable page object id for specific answer feedback page objects
     * for the given question id and answer index
     * (using the id sequence of non page object specific answer feedback)
     */
    final protected function getSpecificAnswerFeedbackPageObjectId(int $questionId, int $questionIndex, int $answerIndex) : int
    {
        $pageObjectId = $this->getSpecificAnswerFeedbackId($questionId, $questionIndex, $answerIndex);
        
        if ($pageObjectId === -1) {
            $pageObjectId = $this->saveSpecificAnswerFeedbackContent($questionId, $questionIndex, $answerIndex, null);
        }
        
        return $pageObjectId;
    }
    
    public function getSpecificAnswerFeedbackExportPresentation(int $questionId, int $questionIndex, int $answerIndex) : string
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $specificAnswerFeedbackExportPresentation = $this->getPageObjectXML(
                $this->getSpecificAnswerFeedbackPageObjectType(),
                $this->getSpecificAnswerFeedbackPageObjectId($questionId, $questionIndex, $answerIndex)
            );
        } else {
            $specificAnswerFeedbackExportPresentation = $this->getSpecificAnswerFeedbackContent(
                $questionId,
                $questionIndex,
                $answerIndex
            );
        }
                
        return $specificAnswerFeedbackExportPresentation;
    }
    
    public function importSpecificAnswerFeedback(int $questionId, int $questionIndex, int $answerIndex, string $feedbackContent) : void
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $pageObjectId = $this->getSpecificAnswerFeedbackPageObjectId($questionId, $questionIndex, $answerIndex);
            $pageObjectType = $this->getSpecificAnswerFeedbackPageObjectType();
            
            $this->createPageObject($pageObjectType, $pageObjectId, $feedbackContent);
        } else {
            $this->saveSpecificAnswerFeedbackContent($questionId, $questionIndex, $answerIndex, $feedbackContent);
        }
    }

    public function specificAnswerFeedbackExists() : bool
    {
        return (bool) strlen(
            $this->getAllSpecificAnswerFeedbackContents($this->questionOBJ->getId())
        );
    }
}
