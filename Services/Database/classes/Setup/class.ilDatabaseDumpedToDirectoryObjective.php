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

class ilDatabaseDumpedToDirectoryObjective implements Setup\Objective
{
    protected string $target;
    protected MysqlDumper $dumper;

    public function __construct(string $target, MysqlDumper $dumper)
    {
        $this->target = $target;
        $this->dumper = $dumper;
    }

    /**
     * @inheritdoc
     */
    public function getHash(): string
    {
        return hash("sha256", self::class . $this->target);
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return "Dump database to $this->target/" . MysqlIfsnopDumper::FILE_NAME;
    }

    /**
     * @inheritdoc
     */
    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        if (file_exists($this->target)) {
            $this->deleteDirRecursive($this->target);
        }
        mkdir($this->target, 0755);

        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $host = $client_ini->readVariable("db", "host");
        $user = $client_ini->readVariable("db", "user");
        $password = $client_ini->readVariable("db", "pass");
        $name = $client_ini->readVariable("db", "name");
        $port = $client_ini->readVariable("db", "port");

        $this->dumper->createDump($host, $user, $password, $name, $port, $this->target);

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return is_writable(pathinfo($this->target, PATHINFO_DIRNAME));
    }

    protected function deleteDirRecursive(string $path): void
    {
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

        rmdir($path);
    }
}
