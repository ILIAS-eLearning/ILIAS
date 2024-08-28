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

namespace ILIAS\UI\Component\Progress\Instruction;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     Progress Bar Instructions are used to order a clientside Progress Bar to perform a desired
     *     update, when pulled asynchronously from a source.
     *   composition: >
     *     Progress Bar Instructions cary information about the Progress Bar status and progress (value),
     *     and optionally provide a message for the user.
     *
     * context:
     *   - Progress Bar Instruction's are used by Progress Bar's which pull updates asynchrnously
     *     from a source.
     *
     * rules:
     *   usage:
     *     1: >
     *       Progress Bar Instructions MUST NOT be used for anything other than updating a
     *       Progress Bar asynchronously.
     * ---
     * @return Bar\Factory
     */
    public function bar(): Bar\Factory;
}
