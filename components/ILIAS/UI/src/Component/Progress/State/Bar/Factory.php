<?php

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

namespace ILIAS\UI\Component\Progress\State\Bar;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     Creates a Progress Bar State, which will be used by the clientside Progress Bar
     *     to change its status to "indeterminate". This State indicates that progress is
     *     being made, but no exact progress can (yet) be calculated. This is typically used
     *     in order to start the Progress Bar.
     *   composition: >
     *     The State consists of an optional message.
     *   effect: >
     *     The Progress Bar will change to "indeterminate".
     *     The Progress Bar shows the given message.
     *   rivals:
     *     Determinate: use a determinate State, if the progress can be calculated.
     *
     * rules:
     *   usage:
     *     1: You SHOULD NOT provide a message, if the progress can be calculated soon.
     *     2: The provided message MUST BE concise and short. E.g. "determining progress".
     * ---
     * @param string|null $message
     * @return \ILIAS\UI\Component\Progress\State\Bar\State
     */
    public function indeterminate(?string $message = null): State;

    /**
     * ---
     * description:
     *   purpose: >
     *     Creates a Progress Bar State, which will be used by the clientside Progress Bar
     *     to change its status to "determinate". This State shows the exact amount of
     *     progress being made.
     *   composition: >
     *     The State consists of a visual progress value (0-100), close to the exact progress,
     *     and an optional message.
     *   effect: >
     *     The Progress Bar will change to "determinate" and show the progress.
     *     The Progress Bar shows the given message.
     *   rivals:
     *     Indeterminate: use an indeterminate State if the progress cannot be calculated.
     *     Success: use a success State if the visual progress value is 100.
     *     Failure: use a failure State if the underlying task failed.
     *
     * rules:
     *   usage:
     *     1: The visual progress value MUST BE a whole number between 0 and 100.
     *     2: You MUST NOT use this State if the visual progress value is 100.
     *     3: The provided message MUST BE concise and short. E.g. "Processing task XY".
     * ---
     * @param int         $visual_progress_value
     * @param string|null $message
     * @return \ILIAS\UI\Component\Progress\State\Bar\State
     */
    public function determinate(int $visual_progress_value, ?string $message = null): State;

    /**
     * ---
     * description:
     *   purpose: >
     *     Creates a Progress Bar State, which will be used by the clientside Progress Bar
     *     to finish with success.
     *   composition: >
     *     The State consists of a message.
     *   effect: >
     *     The Progress Bar entirely fills up and shows a success Glyph and the provided
     *     message.
     *   rivals:
     *     Determinate: use a determinate State if the visual progress value is below 100.
     *     Failure: use a failure State if the underlying task failed.
     *
     * rules:
     *   usage:
     *     1: The provided message MUST BE concise and short. E.g. "Task XY done".
     * ---
     * @param string $message
     * @return \ILIAS\UI\Component\Progress\State\Bar\State
     */
    public function success(string $message): State;

    /**
     * ---
     * description:
     *   purpose: >
     *     Creates a Progress Bar State, which will be used by the clientside Progress Bar
     *     to finish with failure.
     *   composition: >
     *     The State consists of a message.
     *   effect: >
     *     The Progress Bar entirely fills up and shows a failure Glyph and the provided
     *     message.
     *   rivals:
     *     Determinate: use a determinate State if the visual progress value is below 100.
     *     Success: use a success State if the underlying task was successful.
     *
     * rules:
     *   usage:
     *     1: The provided message MUST BE concise and short. E.g. "Task XY failed."
     * ---
     * @param string $message
     * @return \ILIAS\UI\Component\Progress\State\Bar\State
     */
    public function failure(string $message): State;
}
