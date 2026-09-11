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
 * Plain value object for a postal address (multiple fields as one value).
 *
 * This is the raw value wrapped by {@see PostalAddress} — never pass it
 * around on its own outside the code location that just resolved it.
 */
final readonly class PostalAddressValue
{
    public function __construct(
        public string $street = '',
        public string $city = '',
        public string $zipcode = '',
        public string $country = '',
    ) {
    }
}
