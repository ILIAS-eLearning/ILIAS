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

use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\Language\Language;

/**
 * Stand-in for \ilObjLanguage, which needs the global $DIC; injected via UninstallLanguage's
 * $obj_language_factory closure. Counts uninstall() calls.
 */
final class FakeLanguageObject
{
    private int $uninstall_calls = 0;

    /**
     * @param string $uninstall_return_value '' mimics \ilObjLanguage::uninstall() rejecting the call despite the outer guards
     */
    /**
     * @param list<string> $unwritten_overlay_modules
     */
    public function __construct(
        private readonly bool $is_system_language,
        private readonly bool $is_user_language,
        private readonly bool $is_installed,
        private readonly string $uninstall_return_value = 'uninstalled',
        private readonly array $unwritten_overlay_modules = [],
    ) {
    }

    public function isSystemLanguage(): bool
    {
        return $this->is_system_language;
    }

    public function isUserLanguage(): bool
    {
        return $this->is_user_language;
    }

    public function isInstalled(): bool
    {
        return $this->is_installed;
    }

    public function uninstall(): string
    {
        $this->uninstall_calls++;

        return $this->uninstall_return_value;
    }

    /**
     * @return list<string> modules whose overlay the last uninstall() could not remove
     */
    public function getModulesWithUnwrittenOverlay(): array
    {
        return $this->unwritten_overlay_modules;
    }

    public function uninstallCallCount(): int
    {
        return $this->uninstall_calls;
    }
}
