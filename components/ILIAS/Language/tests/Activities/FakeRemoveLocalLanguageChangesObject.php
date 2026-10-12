<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Language;

/**
 * Stand-in for \ilObjLanguage, which needs the global $DIC; injected via RemoveLocalLanguageChanges's
 * $obj_language_factory closure. Counts removeLocalChanges() calls.
 */
final class FakeRemoveLocalLanguageChangesObject
{
    private int $remove_local_changes_calls = 0;

    /**
     * @param bool $remove_local_changes_return_value false mimics \ilObjLanguage::removeLocalChanges() rejecting the call
     * @param list<string> $modules_with_unwritten_overlay migrated modules whose PO/MO overlay could not be written
     */
    public function __construct(
        private readonly bool $is_installed,
        private readonly bool $remove_local_changes_return_value = true,
        private readonly array $modules_with_unwritten_overlay = [],
    ) {
    }

    public function isInstalled(): bool
    {
        return $this->is_installed;
    }

    public function removeLocalChanges(): bool
    {
        $this->remove_local_changes_calls++;

        return $this->remove_local_changes_return_value;
    }

    /**
     * @return list<string>
     */
    public function getModulesWithUnwrittenOverlay(): array
    {
        return $this->modules_with_unwritten_overlay;
    }

    public function removeLocalChangesCallCount(): int
    {
        return $this->remove_local_changes_calls;
    }
}
