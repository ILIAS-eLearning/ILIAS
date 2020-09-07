<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\KioskMode;

use ILIAS\UI;

/**
 * Build a nested table of contents for the view.
 */
interface TOCBuilder
{
    const LP_NOT_STARTED = 0;
    const LP_IN_PROGRESS = 1;
    const LP_COMPLETED = 2;
    const LP_FAILED = 3;

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
    public function node($label, int $parameter = null, $lp = null) : TOCBuilder;

    /**
     * Build an entry in the TOC.
     *
     * The parameter will be appended to the command when updating the state.
     *
     * The $parameter can be used to pass additional information to View::updateGet
     * if required, e.g. about a chapter in the content.
     *
     * @param	mixed $state one of the LP_ constants from TOCBuilder
     */
    public function item(string $label, int $parameter, $state = null) : TOCBuilder;
}
