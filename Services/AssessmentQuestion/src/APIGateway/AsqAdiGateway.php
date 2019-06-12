<?php

namespace ILIAS\AssessmentQuestion\APIGateway;

use ILIAS\AssessmentQuestion\UserInterface\Web\Form\CreateQuestionFormGUI;

class AsqAdiGateway {
	//TODO just create form directly in testgui?
	public static function GetCreationForm() : CreateQuestionFormGUI {
		return new CreateQuestionFormGUI();
	}

	public static function CreateQuestion(string $title, string $description, int $creator_id) : CreateQuestionResult {
		if ($description === 'fail') {
			return new CreateQuestionResult(
				false,
				"Question creation failed!");
		}

		return new CreateQuestionResult(
			true,
			sprintf("Question %s succesfully created by %d", $title, $creator_id));
	}
}