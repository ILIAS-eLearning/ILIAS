<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilDatabaseInitializedObjective implements Setup\Objective
{
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "The database object is initialized.";
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
        if ($environment->getResource(Setup\Environment::RESOURCE_DATABASE)) {
            return $environment;
        }

        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        $type = $client_ini->readVariable("db", "type");
        if ($type == "") {
            $type = "mysql";
        }

        $db = \ilDBWrapperFactory::getWrapper($type);
        $db->initFromIniFile($client_ini);
        $connect = $db->connect(true);
        if (!$connect) {
            throw new Setup\UnachievableException(
                "Database cannot be connected."
            );
        }
        return $environment->withResource(Setup\Environment::RESOURCE_DATABASE, $db);
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI) !== null;
    }
}
