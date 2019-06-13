<?php

namespace ILIAS\AssessmentQuestion\APIGateway;

use ILIAS\AssessmentQuestion\Domain\Question\Command\CreateQuestionCommand;
use ILIAS\AssessmentQuestion\Infrastructure\QuestionDIC;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\CreateQuestionFormGUI;

class AsqAdiGateway {
	//TODO just create form directly in testgui?
	public static function GetCreationForm() : CreateQuestionFormGUI {
		return new CreateQuestionFormGUI();
	}

	public static function CreateQuestion(string $title, string $description, int $creator_id) : CreateQuestionResult {
		/** @var QuestionDIC $dic */
		$dic = QuestionDIC::getInstance();

		$dic->getCommandBus()->handle(new CreateQuestionCommand($title, $description, $creator_id));

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