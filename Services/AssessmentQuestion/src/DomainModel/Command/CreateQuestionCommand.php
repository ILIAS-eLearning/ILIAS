<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Command;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Command\AbstractCommand;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandContract;

/**
 * Class CreateQuestionCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
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
	 * @var ?int
	 */
	protected $answer_type_id;
	/**
	 * @var ?int
	 */
	protected $question_int_id;

    /**
     * @param DomainObjectId $question_uuid
     * @param int $initiating_user_id
     * @param int $container_id
     * @param int $answer_type_id
     */
	public function __construct(
		DomainObjectId $question_uuid,
		int $initiating_user_id,
		?int $container_id = null,
		?int $answer_type_id = null,
	    ?int $question_int_id = null
	) {
		parent::__construct($initiating_user_id);
		$this->question_uuid = $question_uuid;
		$this->container_id = $container_id;
		$this->answer_type_id = $answer_type_id;
		$this->object_id = $question_int_id;
	}

	/**
	 * @return DomainObjectId
	 */
	public function getQuestionUuid(): DomainObjectId {
		return $this->question_uuid;
	}

    /**
     * @return int
     */
	public function getQuestionContainerId(): int {
		return $this->container_id;
	}

	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int {
		return $this->issuing_user_id;
	}
	
	/**
	 * @return int|NULL
	 */
	public function getQuestionIntId(): ?int {
	    return $this->question_int_id;
	}

	/**
	 * @return int
	 */
	public function getAnswerType(): ?int {
		return $this->answer_type_id;
	}
}
