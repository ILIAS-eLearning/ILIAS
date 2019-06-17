<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\CreateQuestionFormGUI;
const MSG_SUCCESS = "success";

/**
 * Class AsqGUIElementFactory
 *
 * @package ILIAS\AssessmentQuestion\Authoring\_PublicApi
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AsqGUIElementFactory {
	public function CreateQuestionCreationForm(): CreateQuestionFormGUI {
		//CreateQuestion.png
		return new CreateQuestionFormGUI();
	}

	public function CreateQuestionListControl(array $questions) {
		//returns question list object
		//GetQuestionlist.png
	}

	public function CreatePrintViewControl(string $question_id) {
		//returns print view
		//GetPrintView.png
	}

	public function CreatePresentationForm(string $question_id) {
		//returns presentation form
		//EditQuestionPresentation.png
	}

	public function CreateQuestionForm(string $question_id) {
		//returns test form for question
		//GetQuestionForm.png
	}
}