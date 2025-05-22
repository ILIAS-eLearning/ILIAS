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

use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\UnachievableException;

class ilDatabaseInitializedObjective implements Objective
{
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "The database object is initialized.";
    }

    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @return array<\ilIniFilesLoadedObjective>|array<\ilDatabaseConfigStoredObjective|\ilDatabasePopulatedObjective>
     */
    public function getPreconditions(Environment $environment): array
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

    public function achieve(Environment $environment): Environment
    {
        if ($environment->getResource(Environment::RESOURCE_DATABASE)) {
            return $environment;
        }

        $client_ini = $environment->getResource(Environment::RESOURCE_CLIENT_INI);

        $type = $client_ini->readVariable("db", "type");
        if ($type === "") {
            $type = ilDBConstants::TYPE_INNODB;
        }

        $db = \ilDBWrapperFactory::getWrapper($type);
        $db->initFromIniFile($client_ini);
        $connect = $db->connect(true);
        if (!$connect) {
            throw new UnachievableException(
                "Database cannot be connected."
            );
        }

        return $environment->withResource(Environment::RESOURCE_DATABASE, $db);
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Environment $environment): bool
    {
        return $environment->getResource(Environment::RESOURCE_CLIENT_INI) !== null;
    }
}
