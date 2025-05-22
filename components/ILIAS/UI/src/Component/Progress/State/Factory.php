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

namespace ILIAS\UI\Component\Progress\State;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     Progress Bar State's are used to provide a particular version of a Progress Bar, which
     *     will be used to performe a desired update when pulled asynchronously from a source.
     *   composition: >
     *     Progress Bar State's cary information about the Progress Bar status and progress (value),
     *     and optionally provide a message for the user.
     *
     * context:
     *   - Progress Bar State's are used by Progress Bar's which pull updates asynchrnously
     *     from a source.
     *
     * rules:
     *   usage:
     *     1: >
     *       A Progress Bar State MUST NOT be used for anything other than updating a
     *       Progress Bar asynchronously.
     * ---
     * @return \ILIAS\UI\Component\Progress\State\Bar\Factory
     */
    public function bar(): Bar\Factory;
}
