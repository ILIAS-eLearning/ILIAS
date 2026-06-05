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

namespace ILIAS\Logging\Setup;

use ILIAS\Setup\Config as ConfigInterface;
use InvalidArgumentException;
use ILIAS\Logging\ILIASLogLevel;

class Config implements ConfigInterface
{
    protected bool $enabled;
    protected ?string $path_to_logfile;
    protected ?ILIASLogLevel $level;
    protected ?string $errorlog_dir;

    public function __construct(
        bool $enabled,
        ?string $path_to_logfile,
        ?string $level,
        ?string $errorlog_dir
    ) {
        if ($enabled && !$path_to_logfile) {
            throw new InvalidArgumentException(
                "Expected a path to the logfile, if logging is enabled."
            );
        }
        $level = ILIASLogLevel::tryFromString($level);
        if ($enabled && !$level) {
            throw new InvalidArgumentException(
                "Expected a valid default log level, if logging is enabled."
            );
        }
        $this->enabled = $enabled;
        $this->path_to_logfile = $this->normalizePath($path_to_logfile);
        $this->level = $level;
        $this->errorlog_dir = $this->normalizePath($errorlog_dir);
    }

    protected function normalizePath(?string $p): ?string
    {
        if (!$p) {
            return null;
        }
        $p = preg_replace("/\\\\/", "/", $p);
        return preg_replace("%/+$%", "", $p);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getPathToLogfile(): ?string
    {
        return $this->path_to_logfile;
    }

    public function getDefaultLevel(): ?ILIASLogLevel
    {
        return $this->level;
    }

    public function getErrorlogDir(): ?string
    {
        return $this->errorlog_dir;
    }
}
