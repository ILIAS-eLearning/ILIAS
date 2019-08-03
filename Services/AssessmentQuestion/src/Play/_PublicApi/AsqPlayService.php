<?php

namespace ILIAS\AssessmentQuestion\Authoring\_PublicApi;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Answer;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command\AnswerQuestionCommand;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionRepository;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\Messaging\CommandBusBuilder;

const MSG_SUCCESS = "success";

/**
 * Class AsqPlayService
 *
 * @package ILIAS\AssessmentQuestion\Authoring\_PublicApi
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AsqPlayService {

	public function AnswerQuestion(Answer $answer) {
		CommandBusBuilder::getCommandBus()->handle(new AnswerQuestionCommand($answer));
	}


	public function ClearAnswer(string $question_id, int $user_id, string $test_id) {
		CommandBusBuilder::getCommandBus()->handle(new QuestionAnswerClearedCommand($question_id, $user_id, $test_id));
	}

	public function GetUserAnswer(string $question_id, int $user_id, string $test_id) : ?Answer {
		//TODO get from read side after test ist finished (projected)
		/** @var Question $question */
		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_id));
		return $question->getAnswer($user_id, $test_id);
	}

	public function GetPointsByUser(string $question_id, int $user_id, string $test_id): float {
		// gets the result of the user
	}
}