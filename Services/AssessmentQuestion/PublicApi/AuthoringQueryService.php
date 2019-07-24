<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringQueryServiceContract;

/**
 * Class AbstractAsqApiAuthoringQueryService
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AuthoringQueryService implements AuthoringQueryServiceContract {

	/**
	 * AsqApiServiceAuthoringQueryContract constructor.
	 *
	 * @param int $container_id
	 */
	public function __construct(int $container_id) {

	}


	public function GetQuestionsOfContainerAsAssocArray(): array {
		// TODO: Implement GetQuestionsOfContainerAsAssocArray() method.
	}
}