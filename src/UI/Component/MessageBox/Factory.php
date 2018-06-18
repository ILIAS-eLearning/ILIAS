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
	 *     The system failed to complete some actions and displays information about the failure.
	 *   composition: >
	 *     The div uses the alert-danger Bootstrap style.
	 *
	 * ---
	 *
	 * @return \ILIAS\UI\Component\MessageBox\MessageBox
	 */
	public function failure($message_text);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The system succeeded in finishing some action and displays a success message.
	 *   composition: >
	 *     The div uses the alert-success Bootstrap style.
	 *
	 * ---
	 *
	 * @return \ILIAS\UI\Component\MessageBox\MessageBox
	 */
	public function success($message_text);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The system informs the user about obstacles standing in the way of completing a workflow
	 *     or about side-effects of his or her actions on other users.
	 *   composition: >
	 *     The div uses the alert-info Bootstrap style.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\MessageBox\MessageBox
	 */
	public function info($message_text);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The system needs input from the user.
	 *   composition: >
	 *     The div uses the alert-warning Bootstrap style.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\MessageBox\MessageBox
	 */
	public function confirmation($message_text);

}