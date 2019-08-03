<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate;

/**
 * Interface AggregateRepository
 *
 * @package ILIAS\AssessmentQuestion\Common\Model
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AggregateRepository {

	/**
	 * @param DomainObjectId $aggregate_id
	 *
	 * @return AggregateRoot
	 */
	public function getAggregateRootById(DomainObjectId $aggregate_id): AggregateRoot;
}