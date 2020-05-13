<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilIniFilesLoadedObjective extends ilSetupObjective
{
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "The ilias.ini.php and client.ini.php are loaded";
    }

    public function isNotable() : bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $path = dirname(__DIR__, 2) . "/ilias.ini.php";
        $ini = new ilIniFile($path);

        $path = $this->getClientDir() . "/client.ini.php";
        $client_ini = new ilIniFile($path);

        return $environment
            ->withResource(Setup\Environment::RESOURCE_ILIAS_INI, $ini)
            ->withResource(Setup\Environment::RESOURCE_CLIENT_INI, $client_ini);
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        return is_null($ini) || is_null($client_ini);
    }

    protected function getClientDir() : string
    {
        return dirname(__DIR__, 2) . "/data/" . $this->config->getClientId();
    }
}
