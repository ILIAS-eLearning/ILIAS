<?php

declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Config;

class ilLoggingSetupConfig implements Config
{
    protected bool $enabled;

    protected ?string $path_to_logfile;
    protected ?string $path_to_errorlogfiles;
    protected ?string $errorlog_dir;

    public function __construct(
        bool $enabled,
        ?string $path_to_logfile,
        ?string $errorlog_dir
    ) {
        if ($enabled && !$path_to_logfile) {
            throw new \InvalidArgumentException(
                "Expected a path to the logfile, if logging is enabled."
            );
        }
        $this->enabled = $enabled;
        $this->path_to_logfile = $this->normalizePath($path_to_logfile);
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

    public function getErrorlogDir(): ?string
    {
        return $this->errorlog_dir;
    }
}
