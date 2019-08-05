<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QueryServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionDtoContract;

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
class QuestionListing implements QueryServiceContract {

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
	 * @return QuestionDtoContract[]
	 */
	public function GetQuestionsOfContainerAsDtoList(int $container_id): array {
		// TODO: Implement GetQuestionsOfContainerAsAssocArray() method.
	}


	/**
	 * @param string $questionUuid
	 *
	 * @return string
	 */
	public function getQuestionQtiXml(QuestionIdContract $questionUuid, RevisionIdContract $revisionUuid = null): string {
		// TODO: implement
	}
}