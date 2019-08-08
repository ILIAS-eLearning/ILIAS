<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web;

use Exception;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\CreateQuestionFormGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFormGUI;
use ilPropertyFormGUI;

const MSG_SUCCESS = "success";

/**
 * Class AsqGUIElementFactory
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AsqGUIElementFactory {

    /**
     * @return CreateQuestionFormGUI
     */
	public static function CreateQuestionCreationForm(): CreateQuestionFormGUI {
		//CreateQuestion.png
		return new CreateQuestionFormGUI();
	}

    /**
     * @param array $questions
     */
	public static function CreateQuestionListControl(array $questions) {
		//returns question list object
		//GetQuestionlist.png
	}

    /**
     * @param string $question_id
     */
	public static function CreatePrintViewControl(string $question_id) {
		//returns print view
		//GetPrintView.png
	}

    /**
     * @param string $question_id
     */
	public static function CreatePresentationForm(string $question_id) {
		//returns presentation form
		//EditQuestionPresentation.png
	}

    /**
     * @param QuestionDto $question
     *
     * @return ilPropertyFormGUI
     * @throws Exception
     */
	public static function CreateQuestionForm(QuestionDto $question):ilPropertyFormGUI {
		if (is_null($question->getLegacyData())) {
			return new QuestionFormGUI($question);
		} else {
			return $question->getLegacyData()->createLegacyForm($question);
		}

	}
}