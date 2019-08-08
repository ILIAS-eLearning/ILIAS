<?php

namespace ILIAS\AssessmentQuestion\Application;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandBusBuilder;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\Command\AnswerQuestionCommand;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\QuestionRepository;
const MSG_SUCCESS = "success";

/**
 * Class PlayApplicationService
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class PlayApplicationService {

    /**
     * @param Answer $answer
     */
	public function AnswerQuestion(Answer $answer) {
		CommandBusBuilder::getCommandBus()->handle(new AnswerQuestionCommand($answer));
	}

    /**
     * @param string $question_id
     * @param int    $user_id
     * @param string $test_id
     */
	public function ClearAnswer(string $question_id, int $user_id, string $test_id) {
		//TODO CommandBusBuilder::getCommandBus()->handle(new QuestionAnswerClearedCommand($question_id, $user_id, $test_id));
	}


    /**
     * @param string $question_id
     * @param int    $user_id
     * @param string $test_id
     *
     * @return Answer|null
     */
	public function GetUserAnswer(string $question_id, int $user_id, string $test_id) : ?Answer {
		//TODO get from read side after test ist finished (projected)
		/** @var Question $question */
		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_id));
		return $question->getAnswer($user_id, $test_id);
	}

	public function GetPointsByUser(string $question_id, int $user_id, string $test_id): float {
		// gets the result of the user
	    $question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_id));
	    $scoring_class = QuestionPlayConfiguration::getScoringClass($question->getPlayConfiguration());
	    $scoring = new $scoring_class($question);
	    return $scoring->score($question->getAnswer($user_id, $test_id));
	}
}