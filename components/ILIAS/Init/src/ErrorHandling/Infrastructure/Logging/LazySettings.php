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

namespace ILIAS\Init\ErrorHandling\Infrastructure\Logging;

use ILIAS\Init\ErrorHandling\Application\ErrorLogDirectory;
use ILIAS\Init\ErrorHandling\Logging\Settings;

/**
 * Reads the path to the error log directory from the ilias.ini.php.
 *
 * The ini file is lazily fetched from the DIC on each call, so that
 * it does not need to be initialized before this class.
 */
class LazySettings implements ErrorLogDirectory
{
    public function path(): string
    {
        return $this->fetchSettings()?->directory() ?? '';
    }

    private function fetchSettings(): ?Settings
    {
        global $DIC;


        if ($DIC->offsetExists('ilIliasIniFile')) {
            return new Settings($DIC->iliasIni());
        }
        if ($DIC->offsetExists('ini')) {
            return new Settings($DIC['ini']);
        }
        return null;
    }
}
