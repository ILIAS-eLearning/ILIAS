<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilIniFilesPopulatedObjective extends ilSetupObjective
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
        // TODO: This shows an unfortunate connection between the webdir and the
        // client.ini.php. Why does the client in reside in the webdir? If we
        // remove the client-feature, the client-ini will go away...
        return [
            new \ilOverwritesExistingInstallationConfirmed($this->config),
            new Setup\Objective\DirectoryCreatedObjective(dirname(__DIR__, 2) . "/data"),
            new Setup\Objective\DirectoryCreatedObjective($this->getClientDir()),
            new Setup\Condition\CanCreateFilesInDirectoryCondition($this->getClientDir()),
            new Setup\Condition\CanCreateFilesInDirectoryCondition(dirname(__DIR__, 2)),
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $path = dirname(__DIR__, 2) . "/ilias.ini.php";
        $ini = new ilIniFile($path);
        $ini->GROUPS = parse_ini_file(__DIR__ . "/../ilias.master.ini.php", true);
        $ini->write();

        $path = $this->getClientDir() . "/client.ini.php";
        $client_ini = new ilIniFile($path);
        $client_ini->GROUPS = parse_ini_file(__DIR__ . "/../client.master.ini.php", true);
        $client_ini->write();

        return $environment
            ->withResource(Setup\Environment::RESOURCE_ILIAS_INI, $ini)
            ->withResource(Setup\Environment::RESOURCE_CLIENT_INI, $client_ini);
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        // if ini file exists we assume that there is some relevant content
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        return is_null($ini) || is_null($client_ini);
    }

    protected function getClientDir() : string
    {
        return dirname(__DIR__, 2) . "/data/" . $this->config->getClientId();
    }
}
