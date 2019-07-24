<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiContainerId;

/**
 * Interface AsqApiAuthoringQuestionService
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AsqApiAuthoringQueryService {

	/**
	 * AsqApiAuthoringQueryService constructor.
	 *
	 * @param AsqApiContainerId $container_id
	 */
	public function __construct(AsqApiContainerId $container_id);


	/**
	 * @return array
	 *
	 * Gets all questions of a Container from db as an Array containing
	 * the generic question data fields
	 */
	public function GetQuestionsOfContainerAsAssocArray():array;
}