<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
namespace ILIAS\UI\Component\MessageBox;

/**
 * This is how a factory for Message Boxes looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The system failed to complete some actions and displays information about the failure.
     *   composition: >
     *     The alert-danger style is used for the message.
     * rules:
     *   usage:
     *      1: >
     *          The Failure Message Boxes MUST be used, if a user interaction has failed.
     *      2: >
     *          The message SHOULD inform the user why the interaction has failed.
     *      3: >
     *          The message SHOULD inform the user how to the problem can be fixed.
     * ---
     * @return \ILIAS\UI\Component\MessageBox\MessageBox
     */
    public function failure(string $message_text) : MessageBox;

    /**
     * ---
     * description:
     *   purpose: >
     *     The system succeeded in finishing some action and displays a success message.
     *   composition: >
     *     The alert-success style is used for the message.
     *
     * rules:
     *   usage:
     *      1: >
     *          The Success Message Boxes MUST be used, if a user interaction has successfully ended.
     *      2: >
     *          The message SHOULD summarize how the system state has been changed due to the user interaction.
     * ---
     * @return \ILIAS\UI\Component\MessageBox\MessageBox
     */
    public function success(string $message_text) : MessageBox;

    /**
     * ---
     * description:
     *   purpose: >
     *     The system informs the user about obstacles standing in the way of completing a workflow
     *     or about side effects of his or her actions on other users.
     *   composition: >
     *     The alert-info style is used for the message.
     *
     * rules:
     *   usage:
     *      1: >
     *          The Info Message Boxes MAY be used to describe a state or condition of the system that help the
     *          user to understand the interactions provided on or missing from a screen.
     *      2: >
     *          The Info Message Boxes MUST NOT be used at the end of a user interaction. Instead Success or Failure
     *          Message Boxes SHOULD be used.
     * ---
     * @return \ILIAS\UI\Component\MessageBox\MessageBox
     */
    public function info(string $message_text) : MessageBox;

    /**
     * ---
     * description:
     *   purpose: >
     *     The system makes sure that an action should really be performed.
     *   composition: >
     *     The alert-warning style is used for the message.
     *
     * rules:
     *   usage:
     *      1: >
     *          The Confirmation Message Boxes MUST be used, if a deletion interaction is being processed. The Buttons
     *          MUST provide a confirmation and a cancel option.
     * ---
     * @return \ILIAS\UI\Component\MessageBox\MessageBox
     */
    public function confirmation(string $message_text) : MessageBox;
}
