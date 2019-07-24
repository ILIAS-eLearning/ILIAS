<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Implementation;

use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiAuthoringQueryServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiAuthoringQueryServiceContract;

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
class AsqApiAuthoringQueryService implements AsqApiAuthoringQueryServiceContract {

	/**
	 * AsqApiAuthoringQueryServiceContract constructor.
	 *
	 * @param int $container_id
	 */
	public function __construct(int $container_id) {

	}


	public function GetQuestionsOfContainerAsAssocArray(): array {
		// TODO: Implement GetQuestionsOfContainerAsAssocArray() method.
	}
}