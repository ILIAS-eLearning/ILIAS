<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\CQRS\Event;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AggregateRoot;

/**
 * Interface Projection
 *
 * @package ILIAS\AssessmentQuestion\Common\Event
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface Projection {

	/**
	 * @param AggregateRoot $projectee
	 *
	 * @return mixed
	 */
	public function project(AggregateRoot $projectee);
}