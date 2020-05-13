<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


use ILIAS\Setup;

class ilSetupConfigStoredObjective extends ilSetupObjective
{
    /**
     * @var \ilSetupPasswordManager
     */
    protected $password_manager;

    public function __construct(
        \ilSetupConfig $config,
        \ilSetupPasswordManager $password_manager
    ) {
        parent::__construct($config);
        $this->password_manager = $password_manager;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Fill ini with common settings";
    }

    public function isNotable() : bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesPopulatedObjective($this->config)
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        $ini->setVariable(
            "setup",
            "pass",
            $this->password_manager->encodePassword(
                $this->config->getMasterPassword()->toString()
            )
        );

        $ini->setVariable("server", "absolute_path", dirname(__DIR__, 2));
        $ini->setVariable(
            "server",
            "timezone",
            $this->config->getServerTimeZone()->getName()
        );

        $ini->setVariable("clients", "default", $this->config->getClientId());

        if (!$ini->write()) {
            throw new Setup\UnachievableException("Could not write ilias.ini.php");
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        return
            $ini->readVariable("setup", "pass") !== $this->password_manager->encodePassword(
                $this->config->getMasterPassword()->toString()
            ) ||
            $ini->readVariable("server", "absolute_path") !== dirname(__DIR__, 2) ||
            $ini->readVariable("server", "timezone") !== $this->config->getServerTimeZone()->getName() ||
            $ini->readVariable("clients", "default") !== $this->config->getClientId()
        ;
    }
}
