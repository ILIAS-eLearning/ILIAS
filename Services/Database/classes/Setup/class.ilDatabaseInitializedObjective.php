<?php declare(strict_types=1);

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

    /**
     * @return array<\ilIniFilesLoadedObjective>|array<\ilDatabaseConfigStoredObjective|\ilDatabasePopulatedObjective>
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        // If there is no config for the database the existing config seems
        // to be ok, and we can just connect.
        if (!$environment->hasConfigFor("database")) {
            return [
                new ilIniFilesLoadedObjective()
            ];
        }

        $config = $environment->getConfigFor("database");
        return [
            new ilDatabasePopulatedObjective($config),
            new ilDatabaseConfigStoredObjective($config)
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        if ($environment->getResource(Setup\Environment::RESOURCE_DATABASE)) {
            return $environment;
        }

        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        $type = $client_ini->readVariable("db", "type");
        if ($type === "") {
            $type = ilDBConstants::TYPE_PDO_MYSQL_INNODB;
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
