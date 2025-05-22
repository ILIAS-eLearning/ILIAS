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

use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\UnachievableException;

class ilFileSystemConfigNotChangedObjective implements Objective
{
    public function __construct(protected \ilFileSystemSetupConfig $config)
    {
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Config for Filesystems did not change.";
    }

    public function isNotable(): bool
    {
        return false;
    }

    /**
     * @return \ilFileSystemDirectoriesCreatedObjective[]|\ilIniFilesLoadedObjective[]
     */
    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilFileSystemDirectoriesCreatedObjective($this->config)
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);

        $current = $ini->readVariable("clients", "datadir");
        $new = $this->config->getDataDir();
        if ($current !== $new) {
            throw new UnachievableException(
                "You seem to try to move the ILIAS data-directory from '$current' " .
                "to '$new', the client.ini.php contains a different path then the " .
                "config you are using. This is not supported by the setup."
            );
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Environment $environment): bool
    {
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);

        return $ini->readVariable("clients", "datadir") !== $this->config->getDataDir();
    }
}
