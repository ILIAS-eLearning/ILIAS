<?php declare(strict_types=1);

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
