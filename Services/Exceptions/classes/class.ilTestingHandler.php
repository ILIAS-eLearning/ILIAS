<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
* A Whoops error handler for testing.
*
* This yields the same output as the plain text handler, but prints a nice message to the tester on top of
* the page.
*
* @author Richard Klees <richard.klees@concepts-and-training.de>
* @version $Id$
*/

require_once("Services/Exceptions/classes/class.ilPlainTextHandler.php");

class ilTestingHandler extends ilPlainTextHandler
{
    /**
     * Get the header for the page.
     *
     * @return string
     */
    protected function pageHeader()
    {
        return "DEAR TESTER! AN ERROR OCCURRED... PLEASE INCLUDE THE FOLLOWING OUTPUT AS ADDITIONAL INFORMATION IN YOUR BUG REPORT.\n\n";
    }
}
