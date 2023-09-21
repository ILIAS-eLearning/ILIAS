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

class ilSetupConfigStoredObjective extends ilSetupObjective
{
    public function __construct(
        Setup\Config $config
    ) {
        parent::__construct($config);
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Fill ini with common settings";
    }

    public function isNotable(): bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        $ini->setVariable("server", "absolute_path", dirname(__DIR__, 2));
        $ini->setVariable(
            "server",
            "timezone",
            $this->config->getServerTimeZone()->getName()
        );

        $ini->setVariable("clients", "default", (string) $this->config->getClientId());

        if (!$ini->write()) {
            throw new Setup\UnachievableException("Could not write ilias.ini.php");
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        return
            $ini->readVariable("server", "absolute_path") !== dirname(__DIR__, 2) ||
            $ini->readVariable("server", "timezone") !== $this->config->getServerTimeZone()->getName() ||
            $ini->readVariable("clients", "default") !== (string) $this->config->getClientId()
        ;
    }
}
