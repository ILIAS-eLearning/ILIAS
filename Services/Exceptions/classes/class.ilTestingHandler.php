<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * A Whoops error handler for testing.
 * This yields the same output as the plain text handler, but prints a nice message to the tester on top of
 * the page.
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTestingHandler extends ilPlainTextHandler
{
    /**
     * Get the header for the page.
     */
    protected function pageHeader() : string
    {
        return "DEAR TESTER! AN ERROR OCCURRED... PLEASE INCLUDE THE FOLLOWING OUTPUT AS ADDITIONAL INFORMATION IN YOUR BUG REPORT.\n\n";
    }
}
