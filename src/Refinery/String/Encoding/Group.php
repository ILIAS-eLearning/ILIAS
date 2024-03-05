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

namespace ILIAS\Refinery\String\Encoding;

class Group
{
    /**
     * Creates a transformation to convert a Latin1 string to UTF-8 encoded string.
     */
    public function latin1ToUtf8(): Encoding
    {
        return new Encoding(
            Encoding::LATIN_1,
            Encoding::UTF_8
        );
    }

    /**
     * Creates a transformation to convert a ASCII string to UTF-8 encoded string.
     */
    public function asciiToUtf8(): Encoding
    {
        return new Encoding(
            Encoding::ASCII,
            Encoding::UTF_8
        );
    }
}
