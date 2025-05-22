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
 * Build a locator for the view.
 *
 * The entries of the locator are understood to be given from general to specific,
 * e.g. Chapter 1 > Section 1.1 > Paragraph 1.1.a ...
 */
interface LocatorBuilder
{
    /**
     * Finish building the locator.
     */
    public function end(): ControlBuilder;

    /**
     * Build an entry in the locator.
     *
     * The parameter will be appended to the command when updating state.
     */
    public function item(string $label, int $parameter): LocatorBuilder;
}
