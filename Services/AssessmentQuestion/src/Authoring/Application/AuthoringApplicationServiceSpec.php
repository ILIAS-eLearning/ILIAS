<?php

namespace ILIAS\AssessmentQuestion\Authoring\Application;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type\AnswerType;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionContainer;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\UI\Component\Link\Link;
use ilLanguage;

/**
 * Class AsqAuthoringSpec
 *
 * @package ILIAS\AssessmentQuestion\Authoring\_PublicApi
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AuthoringApplicationServiceSpec {

	/**
	 * @var  DomainObjectId
	 */
	protected $question_uuid;
	/**
	 * @var int
	 */
	protected $initiating_user_id;
	/**
	 * @var int;
	 */
	protected $container_obj_id;
	/**
	 * @var AnswerType
	 */
	protected $answer_type;


	/**
	 * AuthoringApplicationServiceSpec constructor.
	 *
	 * @param DomainObjectId $question_uuid
	 * @param int            $initiating_user_id
	 */
	public function __construct(
		DomainObjectId $question_uuid,
		int $initiating_user_id) {
		$this->question_uuid = $question_uuid;
		$this->initiating_user_id = $initiating_user_id;
	}


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int {
		return $this->initiating_user_id;
	}


	/**
	 * @return DomainObjectId
	 */
	public function getQuestionUuid(): DomainObjectId {
		return $this->question_uuid;
	}
}