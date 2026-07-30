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

namespace ILIAS\Init\ErrorHandling\Infrastructure\Environment;

use ILIAS\Init\ErrorHandling\Application\DevmodeState;

/**
 * Devmode state as established by the ILIAS runtime via the global DEVMODE constant.
 *
 * The constant is read on each call, so the state is resolved lazily and reflects
 * the runtime even when DEVMODE is defined after this object is created.
 */
final class RuntimeDevmodeState implements DevmodeState
{
    public function isActive(): bool
    {
        return \defined('DEVMODE') && (int) DEVMODE === 1;
    }
}
