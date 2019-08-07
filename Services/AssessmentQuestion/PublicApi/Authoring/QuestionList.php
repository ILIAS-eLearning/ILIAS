<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Authoring;

use QuestionDto;

/**
 * Class AuthoringQueryService
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 */
class QuestionList {

	/**
	 * @var int
	 */
	protected $container_obj_id;
	/**
	 * @var int
	 */
	protected $actor_user_id;


	public function __construct(int $container_obj_id, int $actor_user_id) {
		$this->container_obj_id = $container_obj_id;
		$this->actor_user_id;
	}


	/**
	 * @param int $container_id
	 *
	 * @return array
	 */
	public function GetQuestionsOfContainerAsAssocArray(): array {
		// TODO: Implement GetQuestionsOfContainerAsAssocArray() method.
	}


	/**
	 * @param int $container_id
	 *
	 * @return QuestionDto[]
	 */
	public function GetQuestionsOfContainerAsDtoList(): array {
		// TODO: Implement GetQuestionsOfContainerAsAssocArray() method.
	}
}