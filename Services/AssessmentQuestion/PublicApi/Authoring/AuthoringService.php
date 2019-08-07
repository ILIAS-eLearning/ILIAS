<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Authoring;

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
class AuthoringService {

	/**
	 * @var int
	 */
	protected $container_obj_id;
	/**
	 * @var int
	 */
	protected $actor_user_id;


	/**
	 * @param int $container_obj_id
	 * @param int $actor_user_id
	 */
	public function __construct(int $container_obj_id, int $actor_user_id) {
		$this->container_obj_id = $container_obj_id;
		$this->actor_user_id = $actor_user_id;
	}


	/**
	 * @param int        $container_obj_id
	 * @param QuestionId $question_uuid
	 * @param int        $actor_user_id
	 * @param Link       $container_backlink
	 *
	 * @return Question
	 */
	public function question(QuestionId $question_uuid, Link $container_backlink): Question {
		return new Question($this->container_obj_id, $question_uuid, $this->actor_user_id, $container_backlink);
	}


	/**
	 * @return QuestionList
	 */
	public function questionList(): QuestionList {
		return new QuestionList($this->container_obj_id, $this->actor_user_id);
	}


	/**
	 * @return QuestionImport
	 */
	public function questionImport(): QuestionImport {
		return new QuestionImport();
	}


	/**
	 * Returns the current question_uuid or a new one if no current exists
	 *
	 * @return QuestionId
	 */
	public function currentOrNewQuestionId(): QuestionId {
		global $DIC;

		$uuid = new Factory;
		return $DIC->http()->request()->getAttribute('question_uuid',new QuestionId($uuid->toString()));
	}
}
