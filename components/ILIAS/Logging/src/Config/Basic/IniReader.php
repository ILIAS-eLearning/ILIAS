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

namespace ILIAS\Logging\Config\Basic;

use ILIAS\Environment\Configuration\Installation\IliasIni;

class IniReader implements IniReaderInterface
{
    public function __construct(
        protected IliasIni $ilias_ini_file
    ) {
    }

    public function isLoggingEnabled(): string
    {
        return $this->ilias_ini_file->isLogEnabled() ? '1' : '0';
    }

    public function logFile(): string
    {
        return $this->ilias_ini_file->getLogFile();
    }

    public function logPath(): string
    {
        return $this->ilias_ini_file->getLogPath();
    }

    public function defaultLevel(): string
    {
        return $this->ilias_ini_file->getDefaultLogLevel();
    }
}
