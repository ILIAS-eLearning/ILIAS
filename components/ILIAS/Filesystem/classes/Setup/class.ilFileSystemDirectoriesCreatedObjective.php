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
use ILIAS\Setup\Objective\DirectoryCreatedObjective;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\CLI\InstallCommand;
use ILIAS\Setup\UnachievableException;
use ILIAS\Setup\Objective\ObjectiveWithPreconditions;

class ilFileSystemDirectoriesCreatedObjective implements Objective
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
        return "ILIAS directories are created";
    }

    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @return DirectoryCreatedObjective[]|\ilIniFilesPopulatedObjective[]
     */
    public function getPreconditions(Environment $environment): array
    {
        $client_id = $environment->getResource(Environment::RESOURCE_CLIENT_ID);
        $data_dir = $this->config->getDataDir();
        $web_dir = dirname(__DIR__, 5) . "/public/data";
        $root = dirname(__DIR__, 5);

        $client_data_dir = $data_dir . '/' . $client_id;
        $client_web_dir = $web_dir . '/' . $client_id;

        $web_dir_objective = new DirectoryCreatedObjective($client_web_dir);
        $data_dir_objective = new DirectoryCreatedObjective($client_data_dir);
        $customizing_dir_objective = new NullObjective();

        if ($environment->hasConfigFor(InstallCommand::IMPORT)) {
            $tmp_dir = $environment->getConfigFor("tmp_dir");

            $web_dir_objective = new ObjectiveWithPreconditions(
                new ilFileSystemClientDirectoryRenamedObjective(
                    $web_dir
                ),
                new ilFileSystemDirectoryCopiedRecursivelyObjective(
                    $tmp_dir . DIRECTORY_SEPARATOR . "web_data",
                    $web_dir,
                    false,
                    true
                )
            );
            $data_dir_objective = new ObjectiveWithPreconditions(
                new ilFileSystemClientDirectoryRenamedObjective(
                    $data_dir
                ),
                new ilFileSystemDirectoryCopiedRecursivelyObjective(
                    $tmp_dir . DIRECTORY_SEPARATOR . "data",
                    $data_dir,
                    false,
                    true
                )
            );
            $customizing_dir_objective = new ilFileSystemDirectoryCopiedRecursivelyObjective(
                $tmp_dir . DIRECTORY_SEPARATOR . "Customizing",
                $root . "/public/Customizing",
                false,
                true
            );
        }

        return [
            new ilIniFilesPopulatedObjective(),
            new DirectoryCreatedObjective($data_dir),
            new DirectoryCreatedObjective($web_dir),
            $web_dir_objective,
            $data_dir_objective,
            $customizing_dir_objective
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);

        $ini->setVariable("clients", "datadir", $this->config->getDataDir());
        if (!$ini->write()) {
            throw new UnachievableException("Could not write ilias.ini.php");
        }

        if ($environment->hasConfigFor("tmp_dir")) {
            $tmp_dir = $environment->getConfigFor("tmp_dir");
            if (!is_null($tmp_dir)) {
                $this->deleteRecursive($tmp_dir, true);
            }
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Environment $environment): bool
    {
        if ($environment->hasConfigFor(InstallCommand::IMPORT)) {
            return true;
        }

        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);

        return $ini->readVariable("clients", "datadir") !== $this->config->getDataDir();
    }

    protected function deleteRecursive(string $path, bool $delete_base_dir = false): void
    {
        if (is_file($path)) {
            unlink($path);
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file_info) {
            if ($file_info->isDir()) {
                rmdir($file_info->getRealPath());
                continue;
            }
            unlink($file_info->getRealPath());
        }

        if ($delete_base_dir) {
            rmdir($path);
        }
    }
}
