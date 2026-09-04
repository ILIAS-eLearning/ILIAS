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

namespace ILIAS\Refinery\Decode;

use ILIAS\Refinery\Decode\Transformation\Json;
use ILIAS\Refinery\Transformation;

final class Group
{
    /**
     * Decodes a JSON string into native PHP values, JSON objects become associative arrays.
     *
     * @param int $max_depth Maximum nesting depth of the structure being decoded, counting the
     *                       scalars at the very bottom as one level. Defaults to the depth PHP
     *                       itself uses for `json_decode`.
     */
    public function json(int $max_depth = Json::DEFAULT_MAX_DEPTH): Transformation
    {
        return new Json($max_depth);
    }
}
