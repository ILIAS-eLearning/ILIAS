<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QueryServiceContract;

/**
 * Class AuthoringQueryService
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QueryService implements QueryServiceContract {
	
	/**
	 * @param int $container_id
	 * @return array
	 */
	public function GetQuestionsOfContainerAsAssocArray(int $container_id): array {
		// TODO: Implement GetQuestionsOfContainerAsAssocArray() method.
	}
}