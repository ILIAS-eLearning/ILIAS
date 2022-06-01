<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Config;
use ILIAS\Setup\UnachievableException;

class ilLoggingConfigStoredObjective implements Objective
{
    protected Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Fill ini with settings for Services/Logging";
    }

    public function isNotable() : bool
    {
        return false;
    }

    public function getPreconditions(Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Environment $environment) : Environment
    {
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
        $ini->setVariable(
            "log",
            "error_path",
            $this->config->getErrorlogDir() ?? ''
        );

        if (!$ini->write()) {
            throw new UnachievableException("Could not write ilias.ini.php");
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Environment $environment) : bool
    {
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        $enabled = $this->config->isEnabled() ? "1" : "0";

        $logPath = '';
        $logFile = '';
        if ($this->config->getPathToLogfile()) {
            $logPath = dirname($this->config->getPathToLogfile());
            $logFile = basename($this->config->getPathToLogfile());
        }

        return
            $ini->readVariable("log", "path") !== $logPath ||
            $ini->readVariable("log", "file") !== $logFile ||
            $ini->readVariable("log", "error_path") !== $this->config->getErrorlogDir() ||
            $ini->readVariable("log", "enabled") !== $enabled
        ;
    }
}
