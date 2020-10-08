<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilSetupMetricsCollectedObjective implements Setup\Objective
{
    /**
     * @var Setup\Metrics\Storage
     */
    protected $storage;

    public function __construct(Setup\Metrics\Storage $storage)
    {
        $this->storage = $storage;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Collect common metrics for the ILIAS installation.";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $this->storage->storeStableBool(
            "is_installed",
            $ini !== null && $client_ini !== null
        );

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return true;
    }
}
