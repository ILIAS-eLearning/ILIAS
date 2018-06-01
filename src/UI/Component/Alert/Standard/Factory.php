<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Alert\Standard;

/**
 * This is how a factory for Standard Alerts looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The system failed to complete some actions and displays some information about
	 *     the failure (brand-danger). In forms these Failure Standard Alerts are always accompanied
	 *     by Validation Pointers: The Failure Standard Alert on top of the screen and the Validation Pointers
	 *     scattered across the form to point users to the location that raises the issue.
	 *
	 * rules:
	 *   interaction: >
	 *     Failure Standard Alerts MUST NOT be interactive.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Alert\Standard\Standard
	 */
	public function failure();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The system succeeded in finishing some action and displays a success message (brand-success).
	 *
	 * rules:
	 *   interaction: >
	 *     Success Standard Alerts MUST NOT be interactive.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Alert\Standard\Standard
	 */
	public function success();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The system informs the user about obstacles standing in the way of completing a workflow
	 *     or about side-effects of his or her actions on other users.
	 *
	 * rules:
	 *   interaction: >
	 *     Info Standard Alert MAY contain shortcuts or actions displayed as Buttons. There are exceptions if
	 *     a Button inside the Info Standard Alert takes the user directly to the location where the issue
	 *     can be solved by the user (i.e. Participants-Tab of Survey to delete participant data before editing questions).
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Alert\Standard\Standard
	 */
	public function info();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The system needs input from the user (usually inside an Interruptive Modal).
	 *
	 * rules:
	 *   interaction: >
	 *     Confirmation Standard Alert MUST be interactive: Users are presented with the table listing the objects
	 *     to be (bulk) deleted and the Buttons “Delete” and “Cancel”. In some exceptional cases additional information
	 *     is presented: >
	 *       - User: e-mail, last login >
	 *       - Wiki pages: other pages linking to this page, contributors >
	 *       - If other references exist they are listed and linked. References can be included to the delete action
	 *         by checking a checkbox. >
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Alert\Standard\Standard
	 */
	public function confirmation();

}