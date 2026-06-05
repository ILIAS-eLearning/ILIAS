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

use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Config;
use ILIAS\Setup\UnachievableException;
use ilIniFilesLoadedObjective;
use ilIniFile;

class ConfigStoredObjective implements Objective
{
    protected Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Fill ini with settings for ILIAS\Logging";
    }

    public function isNotable(): bool
    {
        return false;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        /** @var ilIniFile $ini */
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);

        $logPath = '';
        $logFile = '';
        if ($this->config->getPathToLogfile()) {
            $logPath = dirname($this->config->getPathToLogfile());
            $logFile = basename($this->config->getPathToLogfile());
        }

        $ini->setVariable("log", "enabled", $this->config->isEnabled() ? "1" : "0");
        $ini->setVariable("log", "path", $logPath);
        $ini->setVariable("log", "file", $logFile);
        $ini->setVariable("log", "default_level", $this->config->getDefaultLevel()?->toString() ?? "");
        $ini->setVariable("log", "error_path", $this->config->getErrorlogDir() ?? '');

        if (!$ini->write()) {
            throw new UnachievableException("Could not write ilias.ini.php");
        }

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        /** @var ilIniFile $ini */
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        $enabled = $this->config->isEnabled() ? "1" : "0";
        $level = $this->config->getDefaultLevel()?->toString() ?? "";

        $logPath = '';
        $logFile = '';
        if ($this->config->getPathToLogfile()) {
            $logPath = dirname($this->config->getPathToLogfile());
            $logFile = basename($this->config->getPathToLogfile());
        }

        return
            $ini->readVariable("log", "path") !== $logPath ||
            $ini->readVariable("log", "file") !== $logFile ||
            $ini->readVariable("log", "default_level") !== $level ||
            $ini->readVariable("log", "error_path") !== $this->config->getErrorlogDir() ||
            $ini->readVariable("log", "enabled") !== $enabled
        ;
    }
}
