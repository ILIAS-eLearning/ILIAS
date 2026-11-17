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

namespace ILIAS\Data\Privacy\Types;

/**
 * The name of a person: first name, last name and title.
 *
 * This is the raw value wrapped by {@see PersonName} — never pass it around on
 * its own outside the code location that just resolved it.
 */
readonly class PersonNameValue
{
    public function __construct(
        public string $firstname = '',
        public string $lastname = '',
        public string $title = '',
    ) {
    }
}
