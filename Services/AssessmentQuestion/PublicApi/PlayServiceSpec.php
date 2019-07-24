<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\UI\Component\Link\Link;

/**
 * Class PlayServiceSpec
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 */
class PlayServiceSpec implements PlayServiceSpecContract {

	/**
	 * PlayServiceSpec constructor.
	 * @param int $container_obj_id
	 * @param int $actor_user_id
	 * @param Link $container_backlink
	 * @param QuestionIdContract $question_uuid
	 * @param QuestionIdContract $question_revision_uuid
	 */
	public function __construct(int $container_obj_id, int $actor_user_id, Link $container_backlink, QuestionIdContract $question_uuid, QuestionIdContract $question_revision_uuid) {

	}


	/**
	 * @return void
	 */
	public function withAdditionalButton() {
		// TODO: Implement withAdditionalButton() method.
	}


	/**
	 * @return void
	 */
	public function withQuestionActionLink() {
		// TODO: Implement withQuestionActionLink() method.
	}
}