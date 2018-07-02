<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

use CaT\Ente\Component;
use ILIAS\TMS\Wizard\Step as WStep;

/**
 * This is one step in the course creation process.
 */
interface Step extends Component, WStep {
	/**
	 * Get the priority of the step.
	 *
	 * Lesser priorities means the step should be performed earlier.
	 *
	 * @return	int
	 */
	public function getPriority();

	/**
	 * Find out if this step is applicable for the booking process of the
	 * given user.
	 *
	 * @return	bool
	 */
	public function isApplicable();

	/**
	 * Set the id for the user that wants to perform the step.
	 *
	 * @param	int	$usr_id
	 * @return	void
	 */
	public function setUserId($user_id);

	/**
	 * Set the request builder to be used when processing the step.
	 *
	 * @return void
	 */
	public function setRequestBuilder(RequestBuilder $request_builder);
}

