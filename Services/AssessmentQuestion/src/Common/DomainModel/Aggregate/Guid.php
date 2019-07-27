<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate;

use Ramsey\Uuid\Uuid;

/**
 * Class Uuid
 *
 * @package ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Guid {

	/**
	 * @return string
	 */
	public static function create(): string {
		return Uuid::uuid4()->toString();
	}
}