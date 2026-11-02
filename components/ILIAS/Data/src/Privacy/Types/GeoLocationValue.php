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
 * A position on a map, as a person entered it: latitude, longitude and zoom level.
 *
 * This is the raw value wrapped by {@see GeoLocation} — never pass it around on
 * its own outside the code location that just resolved it.
 */
readonly class GeoLocationValue
{
    public function __construct(
        public string $latitude = '',
        public string $longitude = '',
        public int $zoom = 0,
    ) {
    }
}
