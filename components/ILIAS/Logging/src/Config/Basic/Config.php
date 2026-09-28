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

use ILIAS\Logging\ILIASLogLevel;

class Config implements ConfigInterface
{
    protected bool $is_enabled;
    protected string $log_file;
    protected string $log_directory;
    protected ILIASLogLevel $default_level;

    public function __construct(
        protected IniReaderInterface $reader
    ) {
    }

    public function isLoggingEnabled(): bool
    {
        return $this->is_enabled ??= (bool) $this->reader->isLoggingEnabled();
    }

    public function pathToLogFile(): string
    {
        return $this->log_file ??=
            rtrim($this->reader->logPath(), '/') . '/' . ltrim($this->reader->logFile(), '/');
    }

    public function pathToLogDirectory(): string
    {
        return $this->log_directory ??= $this->reader->logPath();
    }

    public function defaultLevel(): ILIASLogLevel
    {
        return $this->default_level ??= $this->logLevelFromString($this->reader->defaultLevel());
    }

    protected function logLevelFromString(string $raw_level): ILIASLogLevel
    {
        return ILIASLogLevel::tryFromString(strtoupper($raw_level)) ??
            ILIASLogLevel::tryFrom((int) $raw_level) ??
            ILIASLogLevel::INFO;
    }
}
