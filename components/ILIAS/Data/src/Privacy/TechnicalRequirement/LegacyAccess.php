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
 * Marks a resolve through a not-yet-migrated code path (e.g. a deprecated
 * getter) where the actual requirement is unknown.
 *
 * Every occurrence is a migration TODO and is listed separately as
 * "Not yet migrated" in the notes for authors of the generated privacy
 * documentation.
 * Never use this in new code — state the real requirement instead.
 */
readonly class LegacyAccess implements TechnicalRequirement
{
    /**
     * @param string $hint pointer to the unmigrated call site,
     *                     e.g. "profile_data_getter"
     */
    public function __construct(
        private string $hint = 'unclassified',
    ) {
    }

    public function describe(): string
    {
        return "legacy:{$this->hint}";
    }
}
