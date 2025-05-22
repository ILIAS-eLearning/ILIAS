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

declare(strict_types=1);

namespace ILIAS\KioskMode;

use ILIAS\UI;

/**
 * Build a nested table of contents for the view.
 */
interface TOCBuilder
{
    public const LP_NOT_STARTED = 0;
    public const LP_IN_PROGRESS = 1;
    public const LP_COMPLETED = 2;
    public const LP_FAILED = 3;

    /**
     * Finish building the TOC.
     *
     * @return	ControlBuilder|TOCBuilder depending on the nesting level.
     */
    public function end();

    /**
     * Build a sub tree in the TOC.
     *
     * If a parameter is provided, the node in the TOC can be accessed itself.
     *
     * The $parameter can be used to pass additional information to View::updateGet
     * if required, e.g. about a chapter in the content.
     *
     * @param	mixed $state one of the LP_ constants from TOCBuilder
     */
    public function node(string $label, ?int $parameter = null, ?int $lp = null): TOCBuilder;

    /**
     * Build an entry in the TOC.
     *
     * The parameter will be appended to the command when updating the state.
     *
     * The $parameter can be used to pass additional information to View::updateGet
     * if required, e.g. about a chapter in the content.
     *
     * @param mixed $state one of the LP_ constants from TOCBuilder
     * @param bool $current is this the currently active item?
     */
    public function item(string $label, int $parameter, $state = null, bool $current = false): TOCBuilder;
}
