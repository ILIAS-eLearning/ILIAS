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
use ILIAS\Setup\Objective\ObjectiveWithPreconditions;

class ilExportZipBuiltObjective extends ilSetupObjective
{
    protected const FILENAME = "ILIAS_EXPORT.zip";

    protected $cwd;

    public function __construct(Setup\Config $config)
    {
        parent::__construct($config);
        $this->tmp_dir = $this->createTempDir();
        if (is_null($this->tmp_dir)) {
            throw new RuntimeException("Can't create temporary directory!");
        }
        $this->cwd = $_SERVER["PWD"];
    }

    /**
     * @inheritdoc
     */
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return "Export ILIAS to $this->cwd/" . self::FILENAME;
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
        $dumper = new MysqlIfsnopDumper($this->config->getExportHooksPath());

        return [
            new ilExportMetadataGatheredObjective(),
            new Setup\ObjectiveCollection(
                "",
                false,
                new ObjectiveWithPreconditions(
                    new ilFileSystemClientDirectoryRenamedObjective(
                        $this->tmp_dir . "/public/data/"
                    ),
                    new ilFileSystemDirectoryCopiedRecursivelyObjective("", $this->tmp_dir . "/public/data", true)
                ),
                new ObjectiveWithPreconditions(
                    new ilFileSystemClientDirectoryRenamedObjective(
                        $this->tmp_dir . "/web_data/"
                    ),
                    new ilFileSystemDirectoryCopiedRecursivelyObjective("public/data", $this->tmp_dir . "/web_data"),
                ),
            ),
            new ilFileSystemDirectoryCopiedRecursivelyObjective("Customizing", $this->tmp_dir . "/Customizing"),
            new ilDatabaseDumpedToDirectoryObjective($this->tmp_dir . "/dump", $dumper)
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $meta = $environment->getConfigFor(ilExportMetadataGatheredObjective::EXPORT_META);
        file_put_contents($this->tmp_dir . "/meta.txt", implode("\n", $meta) . "\n", FILE_APPEND);

        // This will be recreated during import with new data for the imported instance.
        $this->deleteDirRecursive($this->tmp_dir . "/web_data/default/client.ini.php");

        $this->addFolderToZip($this->tmp_dir . "/web_data", $this->tmp_dir . "/web_data.zip");
        $this->addFolderToZip($this->tmp_dir . "/Customizing", $this->tmp_dir . "/Customizing.zip");
        $this->addFolderToZip($this->tmp_dir . "/public/data", $this->tmp_dir . "/data.zip");
        $this->addFolderToZip($this->tmp_dir . "/dump", $this->tmp_dir . "/dump.zip");

        $this->deleteDirRecursive($this->tmp_dir . "/web_data");
        $this->deleteDirRecursive($this->tmp_dir . "/Customizing");
        $this->deleteDirRecursive($this->tmp_dir . "/public/data");
        $this->deleteDirRecursive($this->tmp_dir . "/dump");

        $this->addFolderToZip($this->tmp_dir, $this->cwd . "/" . self::FILENAME);

        $this->deleteDirRecursive($this->tmp_dir);

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return is_writable($this->cwd);
    }

    protected function addFolderToZip($source, $destination, $flags = ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE): bool
    {
        if (!file_exists($source)) {
            throw new RuntimeException("File does not exist: " . $source);
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, $flags)) {
            throw new RuntimeException("Cannot open zip archive: " . $destination);
        }


        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $sourceWithSeparator = $source . DIRECTORY_SEPARATOR;

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isDir()) {
                $zip->addEmptyDir(str_replace($sourceWithSeparator, '', $file . DIRECTORY_SEPARATOR));
            }
            if ($file->isFile()) {
                $zip->addFile($file->getPathname(), str_replace($sourceWithSeparator, '', $file->getPathname()));
            }
        }

        return $zip->close();
    }

    protected function deleteDirRecursive(string $path): void
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

        rmdir($path);
    }

    public function createTempDir(): ?string
    {
        $path = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . mt_rand() . microtime(true);
        if (mkdir($path)) {
            return $path;
        }
        return null;
    }
}
