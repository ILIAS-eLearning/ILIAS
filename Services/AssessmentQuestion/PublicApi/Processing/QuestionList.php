<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;


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
	 * @param int $container_id
	 *
	 * @return array
	 */
	public function GetQuestionsOfContainerAsAssocArray(int $container_id): array {
		// TODO: Implement GetQuestionsOfContainerAsAssocArray() method.
	}


	/**
	 * @param int $container_id
	 *
	 * @return QuestionDto[]
	 */
	public function GetQuestionsOfContainerAsDtoList(int $container_id): array {
		// TODO: Implement GetQuestionsOfContainerAsAssocArray() method.
	}
}