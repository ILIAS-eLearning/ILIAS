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
use ILIAS\Setup\CLI\InstallCommand;

class ilFileSystemClientDirectoryRenamedObjective implements Objective
{
    public const DEFAULT_CLIENT_ID = "default";

    public function __construct(protected string $path)
    {
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

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesPopulatedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $client_id = $environment->getResource(Environment::RESOURCE_CLIENT_ID);

        $old_name = $this->path . DIRECTORY_SEPARATOR . $client_id;
        $new_name = $this->path . DIRECTORY_SEPARATOR . self::DEFAULT_CLIENT_ID;

        if ($environment->hasConfigFor(InstallCommand::IMPORT)) {
            $old_name = $this->path . DIRECTORY_SEPARATOR . self::DEFAULT_CLIENT_ID;
            $new_name = $this->path . DIRECTORY_SEPARATOR . $client_id;
        }

        rename($old_name, $new_name);

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Environment $environment): bool
    {
        return true;
    }
}
