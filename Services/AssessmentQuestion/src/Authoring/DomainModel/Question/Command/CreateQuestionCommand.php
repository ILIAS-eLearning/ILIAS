<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type\AnswerTypeContract;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionContainer;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionData;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\AbstractCommand;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;

/**
 * Class CreateQuestionCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class CreateQuestionCommand extends AbstractCommand implements CommandContract {

	/**
	 * @var  DomainObjectId
	 */
	protected $question_uuid;
	/**
	 * @var QuestionContainer;
	 */
	protected $question_container;
	/**
	 * @var int
	 */
	protected $initiating_user_id;
	/**
	 * @var AnswerTypeContract
	 */
	protected $answer_type;
	//TODO AnswerTypeContractMultipleChoice should be a ValueObject!!


	/**
	 * CreateQuestionCommand constructor.
	 *
	 * @param DomainObjectId     $question_uuid
	 * @param int                $initiating_user_id
	 * @param QuestionContainer  $question_container
	 * @param AnswerTypeContract $answer_type
	 */
	public function __construct(DomainObjectId $question_uuid, int $initiating_user_id, QuestionContainer $question_container, AnswerTypeContract $answer_type) {
		$this->question_uuid = $question_uuid;
		$this->initiating_user_id = $initiating_user_id;
		$this->question_container = $question_container;
		$this->answer_type = $answer_type;
	}


	/**
	 * @return DomainObjectId
	 */
	public function getQuestionUuid(): DomainObjectId {
		return $this->question_uuid;
	}


	/**
	 * @return ;
	 */
	public function getQuestionContainer(): QuestionContainer {
		return $this->question_container;
	}


	/**
	 * @return int
	 */
	public function getActorUserId(): int {
		return $this->initiating_user_id;
	}


	/**
	 * @return AnswerTypeContract
	 */
	public function getAnswerType(): AnswerTypeContract {
		return $this->answer_type;
	}
}
