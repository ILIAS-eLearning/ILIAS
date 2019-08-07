<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ILIAS\UI\Component\Link\Link;
use QuestionId;

/**
 * Class ServiceFactory
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Authoring
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ProcessingService {

	/**
	 * @var int
	 */
	protected $actor_user_id;


	/**
	 * @param int $actor_user_id
	 */
	public function __construct(int $actor_user_id) {
		$this->actor_user_id = $actor_user_id;
	}


	/**
	 * @param string $question_revision_uuid
	 * @param int    $actor_user_id
	 * @param string $userAnswerUuid
	 *
	 * @return Question
	 */
	public function question(QuestionRevisionId $question_revision_id, UserAnswerId $user_answer_id): Question {
		return new Question($question_revision_id, $this->actor_user_id, $user_answer_id );
	}

	/**
	 * Returns a mew user answer id
	 *
	 * @return QuestionId
	 */
	public function newUserAnswerId(): UserAnswerId {
		global $DIC;

		$uuid = new Factory;
		new UserAnswerId($uuid->toString());
	}

}
