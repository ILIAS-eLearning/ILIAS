<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\CQRS\Aggregate;

/**
 * An object that identifies an Aggregate. Typically a UUID, but any kind of id will do, as long as it is unique within the system.
 *
 * An Aggregate can have several Revisions. With the additional attribute revision the
 * aggregate is unique.
 */
interface AggregateWithRevisionId {

	/**
	 * string
	 */
	public function get_aggregate_id();


	/**
	 * string
	 * RevisionID_6Digit_per_Installation
	 */
	public function get_aggregate_revision();
}