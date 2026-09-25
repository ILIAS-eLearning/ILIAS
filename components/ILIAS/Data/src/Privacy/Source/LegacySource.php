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

namespace ILIAS\Data\Privacy\Source;

/**
 * Marks a value written through a not-yet-migrated code path (e.g. a
 * deprecated setter) where the actual origin is unknown.
 *
 * Every occurrence is a migration TODO: replace it with the real source
 * ({@see UserInput}, {@see ExternalApi}, ...) when the call site is
 * migrated.
 */
final readonly class LegacySource implements Source
{
    /**
     * @param string $hint optional pointer to the unmigrated call site,
     *                     e.g. "ilObjUser::setStreet"
     */
    public function __construct(
        private string $hint = 'unclassified',
    ) {
    }

    public function getHint(): string
    {
        return $this->hint;
    }

    public function describe(): string
    {
        return "legacy:{$this->hint}";
    }
}
