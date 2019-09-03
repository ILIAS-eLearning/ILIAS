<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultiOptionQuestionFeedback.php';

/**
 * abstract parent feedback class for question types
 * with multiple answer options (mc, sc, ...)
 * and configurable display behaviour
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @abstract
 */
abstract class ilAssConfigurableMultiOptionQuestionFeedback extends ilAssMultiOptionQuestionFeedback
{
	const FEEDBACK_SETTING_ALL = 1;
	const FEEDBACK_SETTING_CHECKED = 2;
	const FEEDBACK_SETTING_CORRECT = 3;

	/**
	 * returns the name of question specific table
	 *
	 * @return string
	 */
	abstract protected function getSpecificQuestionTableName();

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
		if( !$this->questionOBJ->getSelfAssessmentEditingMode() )
		{
			$header = new ilFormSectionHeaderGUI();
			$header->setTitle($this->lng->txt('feedback_answers'));
			$form->addItem($header);

			require_once './Services/Form/classes/class.ilRadioGroupInputGUI.php';
			require_once './Services/Form/classes/class.ilRadioOption.php';

			$feedback = new ilRadioGroupInputGUI($this->lng->txt('feedback_setting'), 'feedback_setting');
			$feedback->addOption(
				new ilRadioOption($this->lng->txt('feedback_all'), self::FEEDBACK_SETTING_ALL), true
			);
			$feedback->addOption(
				new ilRadioOption($this->lng->txt('feedback_checked'), self::FEEDBACK_SETTING_CHECKED)
			);
			$feedback->addOption(
				new ilRadioOption($this->lng->txt($this->questionOBJ->getSpecificFeedbackAllCorrectOptionLabel()), self::FEEDBACK_SETTING_CORRECT)
			);
			
			$feedback->setRequired(true);
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
						$this->getSpecificAnswerFeedbackPageObjectType(),
						$this->getSpecificAnswerFeedbackPageObjectId($this->questionOBJ->getId(), 0, $index)
					);
				}
				else
				{
					$value = $this->questionOBJ->prepareTextareaOutput(
						$this->getSpecificAnswerFeedbackContent($this->questionOBJ->getId(), 0, $index)
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
		$this->saveSpecificFeedbackSetting($this->questionOBJ->getId(), $form->getInput('feedback_setting'));
		
		if( !$this->questionOBJ->isAdditionalContentEditingModePageObject() )
		{
			foreach( $this->getAnswerOptionsByAnswerIndex() as $index => $answer )
			{
				$this->saveSpecificAnswerFeedbackContent(
					$this->questionOBJ->getId(), 0, $index, $form->getInput("feedback_answer_$index")
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
		
		$this->syncSpecificFeedbackSetting($originalQuestionId, $duplicateQuestionId);

		parent::duplicateSpecificFeedback($originalQuestionId, $duplicateQuestionId);
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
		$this->syncSpecificFeedbackSetting($duplicateQuestionId, $originalQuestionId);

		parent::syncSpecificFeedback($originalQuestionId, $duplicateQuestionId);
	}
	
	private function syncSpecificFeedbackSetting($sourceQuestionId, $targetQuestionId)
	{
		$res = $this->db->queryF(
			"SELECT feedback_setting FROM {$this->getSpecificQuestionTableName()} WHERE question_fi = %s",
			array('integer'), array($sourceQuestionId)
		);

		$row = $this->db->fetchAssoc($res);

		$this->db->update( $this->getSpecificQuestionTableName(),
			array( 'feedback_setting' => array('integer', $row['feedback_setting']) ),
			array( 'question_fi' => array('integer', $targetQuestionId) )
		);
	}
}
