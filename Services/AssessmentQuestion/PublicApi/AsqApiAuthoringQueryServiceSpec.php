<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiContainerId;
use ILIAS\UI\Component\Link\Link;

/**
 * Interface AsqApiAuthoringQueryServiceSpec
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AsqApiAuthoringQueryServiceSpec {

	/**
	 * AsqApiAuthoringQuestionServiceSpec constructor.
	 *
	 * @param AsqApiContainerId $container_id
	 */
	public function __construct(AsqApiContainerId $container_id);
}