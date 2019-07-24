<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

/**
 * Interface DomainObjectId
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AsqApiIdQuestionContract {

	public function getId():string;
}