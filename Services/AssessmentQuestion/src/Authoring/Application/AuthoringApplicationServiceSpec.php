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
	 * @var int
	 */
	protected $initiating_user_id;


	/**
	 * AuthoringApplicationServiceSpec constructor.
	 *
	 * @param DomainObjectId $question_uuid
	 * @param int            $initiating_user_id
	 */
	public function __construct(
		int $initiating_user_id) {
		$this->initiating_user_id = $initiating_user_id;
	}


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int {
		return $this->initiating_user_id;
	}
}