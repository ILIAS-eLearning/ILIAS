<?php

namespace ILIAS\AssessmentQuestion\Authoring\_PublicApi;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\CreateQuestionFormGUI;
use ILIAS\Messaging\CommandBusBuilder;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command\CreateQuestionCommand;

const MSG_SUCCESS = "success";

/**
 * Class AsqPlayService
 *
 * @package ILIAS\AssessmentQuestion\Authoring\_PublicApi
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AsqPlayService {
	public function AnswerQuestion(string $question_id, int $user_id, $answer) {
		// answers the question
	}

	public function ClearAnswer(string $question_id, int $user_id) {
		// clears the answer of the question from the user
	}

	public function GetPointsByUser (string $question_id, int $user_id) : float {
		// gets the result of the user
	}
}