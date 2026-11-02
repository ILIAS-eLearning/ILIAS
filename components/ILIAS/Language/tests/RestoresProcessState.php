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

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * Whatever a test does to the process (locale, LANGUAGE/LC_*, LOCPATH, the static state of the runtime read path),
 * the next test starts as this one did.
 */
trait RestoresProcessState
{
    /** @var array{locale: string|false, env: array<string, string|false>}|null */
    private ?array $process_state_before_test = null;

    #[Before]
    protected function captureProcessStateBeforeTest(): void
    {
        $this->process_state_before_test = MigratedPoFixture::captureProcessState();
    }

    #[After]
    protected function restoreProcessStateAfterTest(): void
    {
        if ($this->process_state_before_test !== null) {
            MigratedPoFixture::restoreProcessState($this->process_state_before_test);
        }
    }
}
