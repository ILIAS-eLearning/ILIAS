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
 */

namespace ILIAS\UI\Component\Progress\Instruction\Bar;

use ILIAS\UI\Component\Progress\Instruction\Instruction;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     Factors a Progress Bar Instruction which orders the clientside Progress Bar
     *     to change its status to "indeterminate". This state indicates that progress
     *     is being made, but no exact progress can (yet) be calculated. This is
     *     typically used in order to start the Progress Bar.
     *   composition: >
     *     The Instruction consists of an optional message.
     *   effect: >
     *     The Progress Bar will change to "indeterminate".
     *     The Progress Bar shows the given message.
     *   rivals:
     *     Determinate: use a determinate Instruction if the progress can be calculated.
     *
     * rules:
     *   usage:
     *     1: You SHOULD NOT provide a message, if the progress can be calculated soon.
     *     2: The provided message MUST BE concise and short. E.g. "determining progress".
     * ---
     * @param string|null $message
     * @return Instruction
     */
    public function indeterminate(?string $message = null): Instruction;

    /**
     * ---
     * description:
     *   purpose: >
     *     Factors a Progress Bar Instruction which orders the clientside Progress Bar
     *     to change its status to "determinate". This state shows the exact amount of
     *     progress being made.
     *   composition: >
     *     The Instruction consists of a percentage (0-1), representing the exact progress,
     *     and an optional message.
     *   effect: >
     *     The Progress Bar will change to "determinate" and show the progress.
     *     The Progress Bar shows the given message.
     *   rivals:
     *     Indeterminate: use an indeterminate Instruction if the progress cannot be calculated.
     *     Success: use a success Instruction if the progress is 1 (100%).
     *     Failure: use a failure Instruction if the underlying task failed.
     *
     * rules:
     *   usage:
     *     1: The progress percentage MUST BE a floating point number between (0 and 1).
     *     2: You MUST NOT use this Instruction, if the percentage is 1.
     *     3: The provided message MUST BE concise and short. E.g. "Processing task XY".
     * ---
     * @param float       $progress_percentage
     * @param string|null $message
     * @return Instruction
     */
    public function determinate(float $progress_percentage, ?string $message = null): Instruction;

    /**
     * ---
     * description:
     *   purpose: >
     *     Factors a Progress Bar Instruction which orders the clientside Progress Bar to
     *     finish with success.
     *   composition: >
     *     The Instruction consists of a message.
     *   effect: >
     *     The Progress Bar fills up to 100% and shows a success Glyph and the provided
     *     message.
     *   rivals:
     *     Determinate: use a determinate Instruction if the progress is below 100%.
     *     Failure: use a failure Instruction if the underlying task failed.
     *
     * rules:
     *   usage:
     *     1: The provided message MUST BE concise and short. E.g. "Task XY done".
     * ---
     * @param string $message
     * @return Instruction
     */
    public function success(string $message): Instruction;

    /**
     * ---
     * description:
     *   purpose: >
     *     Factors a Progress Bar Instruction which orders the clientside Progress Bar to
     *     finish with failure.
     *   composition: >
     *     The Instruction consists of a message.
     *   effect: >
     *     The Progress Bar fills up to 100% and shows a failure Glyph and the provided
     *     message.
     *   rivals:
     *     Determinate: use a determinate Instruction if the progress is below 100%.
     *     Success: use a success Instruction if the underlying task was successful.
     *
     * rules:
     *   usage:
     *     1: The provided message MUST BE concise and short. E.g. "Task XY failed."
     * ---
     * @param string $message
     * @return Instruction
     */
    public function failure(string $message): Instruction;
}
