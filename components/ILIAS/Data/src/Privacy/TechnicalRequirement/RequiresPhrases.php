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

namespace ILIAS\Data\Privacy\TechnicalRequirement;

/**
 * The phrases of a technical requirement end up in the generated privacy
 * documentation, so none of them may be empty.
 */
trait RequiresPhrases
{
    private static function requirePhrases(string ...$phrases): void
    {
        foreach ($phrases as $name => $phrase) {
            if (trim($phrase) === '') {
                throw new \InvalidArgumentException("A technical requirement needs a non-empty {$name}.");
            }
        }
    }
}
