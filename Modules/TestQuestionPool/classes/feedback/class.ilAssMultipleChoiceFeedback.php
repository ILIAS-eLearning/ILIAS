<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultiOptionQuestionFeedback.php';

/**
 * feedback class for assMultipleChoice questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssMultipleChoiceFeedback extends ilAssMultiOptionQuestionFeedback
{
	/**
	 * table name for specific feedback
	 */
	const SPECIFIC_QUESTION_TABLE_NAME = 'qpl_qst_mc';
	
	/**
	 * completes a given form object with the specific form properties
	 * required by this question type
	 * 
	 * (overwrites the method from ilAssMultiOptionQuestionFeedback, because of individual setting)
	 * 
	 * @access public
	 * @param ilPropertyFormGUI $form
	 */
	public function completeSpecificFormProperties(ilPropertyFormGUI $form)
	{
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt('feedback_answers'));
		$form->addItem($header);
		
		if( !$this->questionOBJ->getSelfAssessmentEditingMode() )
		{
			require_once './Services/Form/classes/class.ilRadioGroupInputGUI.php';
			require_once './Services/Form/classes/class.ilRadioOption.php';

			$feedback = new ilRadioGroupInputGUI($this->lng->txt('feedback_setting'), 'feedback_setting');
			$feedback->addOption(new ilRadioOption($this->lng->txt('feedback_all'), 1), true);
			$feedback->addOption(new ilRadioOption($this->lng->txt('feedback_checked'), 2));
			$feedback->addOption(new ilRadioOption($this->lng->txt('feedback_correct'), 3));
			$form->addItem($feedback);

			foreach( $this->getAnswerOptionsByAnswerIndex() as $index => $answer )
			{
				$propertyLabel = $this->questionOBJ->prepareTextareaOutput(
						$this->buildAnswerOptionLabel($index, $answer), true
				);
				
				$propertyPostVar = "feedback_answer_$index";
				
				$form->addItem($this->buildFeedbackContentFormProperty(
					$propertyLabel , $propertyPostVar, $this->questionOBJ->isAdditionalContentEditingModePageObject()
				));
			}
		}
	}
	
	/**
	 * initialises a given form object's specific form properties
	 * relating to this question type
	 * 
	 * (overwrites the method from ilAssMultiOptionQuestionFeedback, because of individual setting)
	 * 
	 * @access public
	 * @param ilPropertyFormGUI $form
	 */
	public function initSpecificFormProperties(ilPropertyFormGUI $form)
	{
		if (!$this->questionOBJ->getSelfAssessmentEditingMode())
		{
			$form->getItemByPostVar('feedback_setting')->setValue(
					$this->questionOBJ->getSpecificFeedbackSetting()
			);

			foreach( $this->getAnswerOptionsByAnswerIndex() as $index => $answer )
			{
				if( $this->questionOBJ->isAdditionalContentEditingModePageObject() )
				{
					$value = $this->getPageObjectNonEditableValueHTML(
							$this->getSpecificFeedbackPageObjectType(), $this->getPageObjectIdByAnswerIndex($index)
					);
				}
				else
				{
					$value = $this->questionOBJ->prepareTextareaOutput(
							$this->getSpecificAnswerFeedbackContent($this->questionOBJ->getId(), $index)
					);
				}
				
				$form->getItemByPostVar("feedback_answer_$index")->setValue($value);
			}
		}
	}
	
	/**
	 * saves a given form object's specific form properties
	 * relating to this question type
	 * 
	 * (overwrites the method from ilAssMultiOptionQuestionFeedback, because of individual setting)
	 * 
	 * @access public
	 * @param ilPropertyFormGUI $form
	 */
	public function saveSpecificFormProperties(ilPropertyFormGUI $form)
	{
		if( !$this->questionOBJ->isAdditionalContentEditingModePageObject() )
		{
			$this->saveSpecificFeedbackSetting($this->questionOBJ->getId(), $form->getInput('feedback_setting'));

			foreach( $this->getAnswerOptionsByAnswerIndex() as $index => $answer )
			{
				$this->saveSpecificAnswerFeedbackContent(
						$this->questionOBJ->getId(), $index, $form->getInput("feedback_answer_$index")
				);
			}
		}
	}
	
	/**
	 * returns the fact that the feedback editing form is saveable in page object editing mode,
	 * because this question type has additional feedback settings
	 * 
	 * @access public
	 * @return boolean
	 */
	public function isSaveableInPageObjectEditingMode()
	{
		return true;
	}
	
	/**
	 * returns the table name for specific question itself
	 * 
	 * @return string $specificFeedbackTableName
	 */
	protected function getSpecificQuestionTableName()
	{
		return self::SPECIFIC_QUESTION_TABLE_NAME;
	}
	
	/**
	 * saves the given specific feedback setting for the given question id to the db.
	 * (It#s stored to dataset of question itself)
	 * @access public
	 * @param integer $questionId
	 * @param integer $specificFeedbackSetting
	 */
	public function saveSpecificFeedbackSetting($questionId, $specificFeedbackSetting)
	{
		$this->db->update($this->getSpecificQuestionTableName(),
				array('feedback_setting' => array('integer', $specificFeedbackSetting)),
				array('question_fi' => array('integer', $questionId))
		);
	}
	
	/**
	 * duplicates the SPECIFIC feedback relating to the given original question id
	 * and saves it for the given duplicate question id
	 * 
	 * (overwrites the method from parent class, because of individual setting)
	 * 
	 * @access protected
	 * @param integer $originalQuestionId
	 * @param integer $duplicateQuestionId
	 */
	protected function duplicateSpecificFeedback($originalQuestionId, $duplicateQuestionId)
	{
		// sync specific feedback setting to duplicated question
		$res = $this->db->queryF(
			"SELECT feedback_setting FROM {$this->getSpecificQuestionTableName()} WHERE question_fi = %s",
			array('integer'), array($originalQuestionId)
		);
				
		$row = $this->db->fetchAssoc($res);
		
		$this->db->manipulateF(
			"UPDATE {$this->getSpecificQuestionTableName()} SET feedback_setting = %s WHERE question_fi = %s",
			array('integer', 'integer'), array($row['feedback_setting'], $duplicateQuestionId)
		);
		
		// sync specific answer feedback to duplicated question
				
		$res = $this->db->queryF(
			"SELECT * FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s", array('integer'), array($originalQuestionId)
		);
		
		while( $row = $this->db->fetchAssoc($res) )
		{
			$nextId = $this->db->nextId($this->getSpecificFeedbackTableName());
			
			$this->db->insert($this->getSpecificFeedbackTableName(), array(
				'feedback_id' => array('integer', $nextId),
				'question_fi' => array('integer', $duplicateQuestionId),
				'answer' => array('integer', $row['answer']),
				'feedback' => array('text', $row['feedback']),
				'tstamp' => array('integer', time())
			));
			
			if( $this->questionOBJ->isAdditionalContentEditingModePageObject() )
			{
				$pageObjectType = $this->getSpecificAnswerFeedbackPageObjectType();
				$this->duplicatePageObject($pageObjectType, $row['feedback_id'], $nextId, $duplicateQuestionId);
			}
		}
	}
	
	/**
	 * syncs the SPECIFIC feedback from a duplicated question back to the original question
	 * 
	 * (overwrites the method from parent class, because of individual setting)
	 * 
	 * @access protected
	 * @param integer $originalQuestionId
	 * @param integer $duplicateQuestionId
	 */
	protected function syncSpecificFeedback($originalQuestionId, $duplicateQuestionId)
	{
		// sync specific feedback setting to the original
		$this->db->manipulate("
				UPDATE {$this->getSpecificQuestionTableName()} SET feedback_setting = (
					SELECT feedback_setting FROM {$this->getSpecificQuestionTableName()} WHERE question_fi = %s
				) WHERE question_fi = %s
			",
			array('integer', 'integer'), array($duplicateQuestionId, $originalQuestionId)
		);

		// delete specific feedback of the original
		$this->db->manipulateF(
			"DELETE FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
			array('integer'), array($originalQuestionId)
		);
			
		// get specific feedback of the actual question
		$res = $this->db->queryF(
			"SELECT * FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
			array('integer'), array($duplicateQuestionId)
		);

		// save specific feedback to the original
		while( $row = $this->db->fetchAssoc($res) )
		{
			$nextId = $this->db->nextId($this->getSpecificFeedbackTableName());
			
			$this->db->insert($this->getSpecificFeedbackTableName(), array(
				'feedback_id' => array('integer', $nextId),
				'question_fi' => array('integer', $originalQuestionId),
				'answer' => array('integer',$row['answer']),
				'feedback' => array('text',$row['feedback']),
				'tstamp' => array('integer',time())
			));
		}
	}
}
