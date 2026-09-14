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

namespace ILIAS\Data\Privacy\Purpose;

/**
 * Marks a resolve through a not-yet-migrated code path (e.g. a deprecated
 * getter) where the actual purpose is unknown.
 *
 * Every occurrence is a migration TODO and is listed separately as
 * "Unmigrated (legacy access)" in the generated privacy documentation.
 * Never use this in new code — state the real purpose instead.
 */
final readonly class LegacyAccess implements Purpose
{
    /**
     * @param string $hint pointer to the unmigrated call site,
     *                     e.g. "profile_data_getter"
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
