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
	 * rules:
	 *   usage:
	 *      1: >
	 *          The Failure Message Boxes MUST be used, if a user interaction has failed. The message
	 *          SHOULD inform the user why the interaction has failed. The message SHOULD inform the user how
	 *          to the problem can be fixed.
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
	 * rules:
	 *   usage:
	 *      1: >
	 *          The Success Message Boxes MUST be used, if a user interaction has successfully ended. The message
	 *          SHOULD summarize how the system state has been changed due to the user interaction.
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
	 *
	 * rules:
	 *   usage:
	 *      1: >
	 *          The Info Message Boxes MAY be used to describe a state or condition of the system that help the
	 *          user to understand the provided (or missing) interactions on a screen.
	 *      2: >
	 *          The Info Message Boxes MUST NOT be used at the end of a user interaction. Instead Success or Failure
	 *          Message Boxes SHOULD be used.
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
	 *
	 * rules:
	 *   usage:
	 *      1: >
	 *          The Confirmation Message Boxes MUST be used, if a deletion interaction is being processed. The Buttons
	 *          MUST provide a confirm and a cancel option.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\MessageBox\MessageBox
	 */
	public function confirmation($message_text);

}