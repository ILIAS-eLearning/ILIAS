<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

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
    //public function end(): ControlBuilder;
    public function end();

    /**
     * Build an entry in the locator.
     *
     * The parameter will be appended to the command when updating state.
     */
    public function item(string $label, int $parameter) : LocatorBuilder;
}
