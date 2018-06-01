<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Alert;

/**
 * This is how a factory for Alerts looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Standard Alerts provide information about actual or imminent consequences of a recent input
	 *     within the context the input happened in.
	 *   composition: >
	 *     There are four main types of Standard Alerts, each is displayed in the according color:
	 *     1: Failure
	 *     2: Success
	 *     3: Info
	 *     4: Confirmation
	 *   rivals:
	 *     1: Interruptive Modal: Interruptive Modal always contain a Confirmation Standard Alert in form of a question
	 *        asking for confirmation of the deletion of the items listed in the table underneath.
	 *     2: Validation Pointer: Validation Pointer accompany Failure Standard Alert pointing to the location
	 *        raising a form validation error.
	 * ---
	 *
	 * @return 	\ILIAS\UI\Component\Alert\Standard\Factory
	 **/
	public function standard();
}