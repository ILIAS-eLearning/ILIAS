<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Component;

/**
 * This is an information about a course, noteworthy for a user in some context.
 */
interface CourseInfo extends Component {
	const CONTEXT_SEARCH_SHORT_INFO = 1;
	const CONTEXT_SEARCH_DETAIL_INFO = 2;
	const CONTEXT_BOOKING_DEFAULT_INFO = 3;
	const CONTEXT_SEARCH_FURTHER_INFO = 4;
	const CONTEXT_USER_BOOKING_SHORT_INFO = 5;
	const CONTEXT_USER_BOOKING_DETAIL_INFO = 6;
	const CONTEXT_USER_BOOKING_FURTHER_INFO = 7;
	const CONTEXT_ASSIGNED_TRAINING_DETAIL_INFO = 12;
	const CONTEXT_ADMIN_OVERVIEW_DETAIL_INFO = 14;
	const CONTEXT_ACCOMODATION_DEFAULT_INFO = 16;
	const CONTEXT_ICAL = 15;
	const CONTEXT_IS_BOOKABLE = 17;
	const CONTEXT_IDD_RELEVANT = 18;
	const CONTEXT_XETR_TIME_INFO = 19;
	const CONTEXT_GENERAL_BOOKING_LINK = 20;
	const CONTEXT_XCCL_TOPICS = 21;

	/**
	 * Get a label for this step in the process.
	 *
	 * @return	string
	 */
	public function getLabel();

	/**
	 * Get the value of this field.
	 *
	 * @return	string|array<string,string>
	 */
	public function getValue();

	/**
	 * Get a description for this step in the process.
	 *
	 * @return	string
	 */
	public function getDescription();

	/**
	 * Get the priority of the step.
	 *
	 * Lesser priorities means the step should be performed earlier.
	 *
	 * @return	int
	 */
	public function getPriority();

	/**
	 * Check if the info is relevant in the given context.
	 *
	 * @param	mixed	$context from the list of contexts in this class
	 * @return	bool
	 */
	public function hasContext($context);
}

