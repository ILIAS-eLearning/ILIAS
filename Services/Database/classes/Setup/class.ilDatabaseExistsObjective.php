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

class ilDatabaseExistsObjective extends \ilDatabaseObjective
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
        return "The database exists on the server.";
    }

    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @return array<\ilDatabaseServerIsConnectableObjective|\ilDatabaseCreatedObjective>
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        $preconditions = [
            new \ilDatabaseServerIsConnectableObjective($this->config)
        ];
        if ($this->config->getCreateDatabase()) {
            $preconditions[] = new \ilDatabaseCreatedObjective($this->config);
        }
        return $preconditions;
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $db = \ilDBWrapperFactory::getWrapper($this->config->getType());
        $db->initFromIniFile($this->config->toMockIniFile());
        $connect = $db->connect(true);
        if (!$connect) {
            throw new Setup\UnachievableException(
                "Database cannot be connected. Please check the credentials."
            );
        }
        return $environment->withResource(Setup\Environment::RESOURCE_DATABASE, $db);
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }
}
