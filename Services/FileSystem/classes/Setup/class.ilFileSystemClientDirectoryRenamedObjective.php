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

use ILIAS\Setup;

class ilFileSystemClientDirectoryRenamedObjective implements Setup\Objective
{
    public const DEFAULT_CLIENT_ID = "default";

    protected string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class) . $this->path;
    }

    public function getLabel(): string
    {
        return "Switch client names for export/import";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesPopulatedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);

        $old_name = $this->path . DIRECTORY_SEPARATOR . $client_id;
        $new_name = $this->path . DIRECTORY_SEPARATOR . self::DEFAULT_CLIENT_ID;

        if ($environment->hasConfigFor(Setup\CLI\InstallCommand::IMPORT)) {
            $old_name = $this->path . DIRECTORY_SEPARATOR . self::DEFAULT_CLIENT_ID;
            $new_name = $this->path . DIRECTORY_SEPARATOR . $client_id;
        }

        rename($old_name, $new_name);

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
