<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MessageBox;

/**
 * This is how a factory for Message Boxes looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The system failed to complete some actions and displays information about the failure (brand-danger).
	 *
	 * rules:
	 *   interaction: >
	 *     Failure Message Boxes MUST NOT be interactive.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\MessageBox\MessageBox
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
	 *     Success Message Boxes MUST NOT be interactive.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\MessageBox\MessageBox
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
	 *     Info Message Boxes MAY contain shortcuts or actions displayed as Buttons. Buttons being used as shortcuts
	 *     SHOULD be exceptions, e.g. if a Button inside the Info Message Box takes the user directly to the location where
	 *     the issue can be solved by the user (i.e. Participants-Tab of Survey to delete participant data before editing questions).
	 * ---
	 *
	 * @return \ILIAS\UI\Component\MessageBox\MessageBox
	 */
	public function info();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The system needs input from the user.
	 *
	 * rules:
	 *   interaction: >
	 *     Confirmation Message Boxes MUST be interactive.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\MessageBox\MessageBox
	 */
	public function confirmation();

}