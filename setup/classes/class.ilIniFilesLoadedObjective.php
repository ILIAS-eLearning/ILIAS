<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilIniFilesLoadedObjective implements Setup\Objective
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
        return [
            new Setup\Objective\ClientIdReadObjective(),
            new ilIniFilesPopulatedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);
        if ($client_id === null) {
            throw new Setup\UnachievableException(
                "To initialize the ini-files, we need a client id, but it does not " .
                "exist in the environment."
            );
        }

        if ($environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI) == null) {
            $path = dirname(__DIR__, 2) . "/ilias.ini.php";
            $ini = new ilIniFile($path);
            $ini->read();
            $environment = $environment
                ->withResource(Setup\Environment::RESOURCE_ILIAS_INI, $ini);
        }

        if ($environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI) == null) {
            $path = $this->getClientDir($client_id) . "/client.ini.php";
            $client_ini = new ilIniFile($path);
            $client_ini->read();
            $environment = $environment
                ->withResource(Setup\Environment::RESOURCE_CLIENT_INI, $client_ini);
        }

        return $environment;
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

    protected function getClientDir($client_id) : string
    {
        return dirname(__DIR__, 2) . "/data/$client_id";
    }
}
