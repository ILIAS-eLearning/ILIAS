<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Command;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type\AnswerType;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionContainer;
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
	 * @var ?int;
	 */
	protected $container_id;
	/**
	 * @var int
	 */
	protected $answer_type_id;

	/**
	 * CreateQuestionCommand constructor.
	 *
	 * @param DomainObjectId     $question_uuid
	 * @param int                $initiating_user_id
	 * @param QuestionContainer  $question_container
	 * @param AnswerType $answer_type
	 */
	public function __construct(
		DomainObjectId $question_uuid,
		int $initiating_user_id,
		?int $container_id = null,
		?int $answer_type_id = null
	) {
		parent::__construct($initiating_user_id);
		$this->question_uuid = $question_uuid;
		$this->container_id = $container_id;
		$this->answer_type_id = $answer_type_id;
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
	public function getQuestionContainer(): ?int {
		return $this->container_id;
	}


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int {
		return $this->issuing_user_id;
	}


	/**
	 * @return int
	 */
	public function getAnswerType(): ?int {
		return $this->answer_type_id;
	}
}
