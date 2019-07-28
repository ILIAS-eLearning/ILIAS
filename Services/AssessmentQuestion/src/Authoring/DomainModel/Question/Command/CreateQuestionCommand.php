<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type\AnswerType;
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
	 * @var AnswerType
	 */
	protected $answer_type;
	//TODO AnswerTypeMultipleChoice should be a ValueObject!!


	/**
	 * CreateQuestionCommand constructor.
	 *
	 * @param DomainObjectId     $question_uuid
	 * @param int                $initiating_user_id
	 * @param QuestionContainer  $question_container
	 * @param AnswerType $answer_type
	 */
	public function __construct(DomainObjectId $question_uuid, int $initiating_user_id, QuestionContainer $question_container, AnswerType $answer_type) {
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
	public function getInitiatingUserId(): int {
		return $this->initiating_user_id;
	}


	/**
	 * @return AnswerType
	 */
	public function getAnswerType(): AnswerType {
		return $this->answer_type;
	}
}
