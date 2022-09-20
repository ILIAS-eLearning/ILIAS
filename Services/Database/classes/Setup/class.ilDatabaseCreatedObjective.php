<?php

declare(strict_types=1);

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

use ILIAS\Setup;

class ilDatabaseCreatedObjective extends ilDatabaseObjective
{
    public function getHash(): string
    {
        return hash("sha256", implode("-", [
            self::class,
            $this->config->getHost(),
            $this->config->getPort(),
            $this->config->getDatabase()
        ]));
    }

    public function getLabel(): string
    {
        return "The database is created on the server.";
    }

    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @return \ilDatabaseServerIsConnectableObjective[]
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilDatabaseServerIsConnectableObjective($this->config)
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $c = $this->config;
        $db = \ilDBWrapperFactory::getWrapper($this->config->getType());
        $db->initFromIniFile($c->toMockIniFile());

        if (!$db->createDatabase($c->getDatabase(), "utf8", $c->getCollation())) {
            throw new Setup\UnachievableException(
                "Database cannot be created."
            );
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        $c = $this->config;
        $db = \ilDBWrapperFactory::getWrapper($this->config->getType());
        $db->initFromIniFile($c->toMockIniFile());

        $connect = $db->connect(true);
        return !$connect;
    }
}
