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

class ilDatabaseServerIsConnectableObjective extends \ilDatabaseObjective
{
    public function getHash(): string
    {
        $pw = $this->config->getPassword();
        return hash("sha256", implode("-", [
            self::class,
            $this->config->getHost(),
            $this->config->getPort(),
            $this->config->getUser(),
            $pw !== null ? $pw->toString() : ""
        ]));
    }

    public function getLabel(): string
    {
        return "The database server is connectable with the supplied configuration.";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $db = \ilDBWrapperFactory::getWrapper($this->config->getType());
        $db->initFromIniFile($this->config->toMockIniFile());
        try {
            $connect = $db->connect();
        } catch (PDOException $e) {
            // 1049 is "unknown database", which is ok because we propably didn't
            // install the db yet,.
            if ($e->getCode() !== 1049) {
                throw $e;
            }

            $connect = true;
        }
        if (!$connect) {
            throw new Setup\UnachievableException(
                "Database cannot be reached. Please check the credentials."
            );
        }
        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }
}
