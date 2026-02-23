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

/**
 * A Whoops error handler for testing.
 * This yields the same output as the plain text handler, but prints a nice message to the tester on top of
 * the page.
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTestingHandler extends ilPlainTextHandler
{
    public function generateResponse(): string
    {
        return "Dear Tester - it seems that something has gone wrong. Please include the following output as additional information in your bug report. Thank you.\n\n"
            . $this->getPlainTextExceptionOutput();
    }
}
