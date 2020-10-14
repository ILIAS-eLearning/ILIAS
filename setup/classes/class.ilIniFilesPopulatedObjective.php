<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilIniFilesPopulatedObjective implements Setup\Objective
{
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "The ilias.ini.php and client.ini.php are populated.";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);
        if ($client_id === null) {
            throw new \LogicException(
                "Expected a client_id in the environment."
            );
        }
        $client_dir = $this->getClientDir($client_id);

        // TODO: This shows an unfortunate connection between the webdir and the
        // client.ini.php. Why does the client.ini reside in the webdir? If we
        // remove the client-feature, the client-ini will go away...
        return [
            new Setup\Objective\DirectoryCreatedObjective(dirname(__DIR__, 2) . "/data"),
            new Setup\Objective\DirectoryCreatedObjective($client_dir),
            new Setup\Condition\CanCreateFilesInDirectoryCondition($client_dir),
            new Setup\Condition\CanCreateFilesInDirectoryCondition(dirname(__DIR__, 2))
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);

        $path = $this->getILIASIniPath();
        if (!file_exists($path)) {
            $ini = new ilIniFile($path);
            $ini->GROUPS = parse_ini_file(__DIR__ . "/../ilias.master.ini.php", true);
            $ini->write();
            $environment = $environment
                ->withResource(Setup\Environment::RESOURCE_ILIAS_INI, $ini);
        }

        $path = $this->getClientIniPath($client_id);
        if (!file_exists($path)) {
            $client_ini = new ilIniFile($path);
            $client_ini->GROUPS = parse_ini_file(__DIR__ . "/../client.master.ini.php", true);
            $client_ini->write();
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
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);

        return !file_exists($this->getILIASIniPath())
            || !file_exists($this->getClientIniPath($client_id));
    }

    protected function getClientDir(string $client_id) : string
    {
        return dirname(__DIR__, 2) . "/data/" . $client_id;
    }

    protected function getClientIniPath(string $client_id) : string
    {
        return $this->getClientDir($client_id) . "/client.ini.php";
    }

    protected function getILIASIniPath() : string
    {
        return dirname(__DIR__, 2) . "/ilias.ini.php";
    }
}
