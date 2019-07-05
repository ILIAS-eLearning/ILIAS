<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\AbstractQuestionConfigFormGUI;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\CreateQuestionFormGUI;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\SingleChoiceConfigFormGUI;
use QuestionDto;

const MSG_SUCCESS = "success";

/**
 * Class AsqGUIElementFactory
 *
 * @package ILIAS\AssessmentQuestion\Authoring\_PublicApi
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AsqGUIElementFactory {
	public static function CreateQuestionCreationForm(): CreateQuestionFormGUI {
		//CreateQuestion.png
		return new CreateQuestionFormGUI();
	}

	public static function CreateQuestionListControl(array $questions) {
		//returns question list object
		//GetQuestionlist.png
	}

	public static function CreatePrintViewControl(string $question_id) {
		//returns print view
		//GetPrintView.png
	}

	public static function CreatePresentationForm(string $question_id) {
		//returns presentation form
		//EditQuestionPresentation.png
	}

	public static function CreateQuestionForm(QuestionDto $question):AbstractQuestionConfigFormGUI {
		//CreateQuestion.png
		return new SingleChoiceConfigFormGUI($question);
	}
}