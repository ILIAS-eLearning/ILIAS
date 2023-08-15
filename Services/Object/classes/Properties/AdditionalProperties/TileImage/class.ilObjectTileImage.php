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

use ILIAS\Filesystem\Util\Convert\ImageOutputOptions;
use ILIAS\Filesystem\Util\LegacyPathHelper;
use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Util\Convert\LegacyImages;

class ilObjectTileImage
{
    private const TILE_IMAGE_SIZE = 512;
    private LegacyImages $image_converter;
    protected string $ext = '';

    public function __construct(
        protected Filesystem $filesystem,
        protected FileUpload $upload,
        protected int $object_id
    ) {
        global $DIC;
        $this->image_converter = $DIC->fileConverters()->legacyImages();
    }

    public function getExtension(): string
    {
        if ($this->ext === '') {
            $this->ext = ilContainer::_lookupContainerSetting($this->object_id, 'tile_image');
        }
        return $this->ext;
    }

    public function copy(int $target_obj_id): void
    {
        if (!$this->exists()) {
            ilContainer::_deleteContainerSettings($target_obj_id, 'tile_image');
            return;
        }

        try {
            $this->filesystem->copy(
                $this->getRelativePath(),
                preg_replace(
                    '/(' . "tile_image_" . ')(\d*)\/(.*)$/',
                    '${1}' . $target_obj_id . '/${3}',
                    $this->getRelativePath()
                )
            );

            ilContainer::_writeContainerSetting($target_obj_id, 'tile_image', $this->getExtension());
        } catch (Exception $e) {
            ilContainer::_deleteContainerSettings($target_obj_id, 'tile_image');
        }
    }

    public function delete(): void
    {
        if ($this->filesystem->hasDir($this->getRelativeDirectory())) {
            try {
                $this->filesystem->deleteDir($this->getRelativeDirectory());
            } catch (Exception $e) {
            }
        }

        ilContainer::_deleteContainerSettings($this->object_id, 'tile_image');
    }

    public function saveFromTempFileName(string $tempfile_name): void
    {
        $this->createDirectory();

        $relative_path = $this->getRelativePath();
        if ($this->filesystem->has($relative_path)) {
            $this->filesystem->delete($relative_path);
        }

        $this->ext = pathinfo($tempfile_name, PATHINFO_EXTENSION);

        rename(ilFileUtils::getDataDir() . '/temp/' . $tempfile_name, $this->getFullPath());

        $this->image_converter->croppedSquare(
            $this->getFullPath(),
            $this->getFullPath(),
            self::TILE_IMAGE_SIZE, // I suggest to use a constant here, in the old code it was the min length of either height or width of the original image which can be huge...
            ImageOutputOptions::FORMAT_KEEP,
            70
        );

        $this->persistImageState();
    }

    protected function persistImageState(): void
    {
        if ($this->filesystem->has($this->getRelativePath())) {
            ilContainer::_writeContainerSetting($this->object_id, 'tile_image', $this->ext);
        } else {
            ilContainer::_deleteContainerSettings($this->object_id, 'tile_image');
        }
    }

    /**
     * @throws IOException
     */
    protected function createDirectory(): void
    {
        $this->filesystem->createDir($this->getRelativeDirectory());
    }

    public function getRelativeDirectory(): string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            [
                'obj_data',
                'tile_image',
                'tile_image_' . $this->object_id
            ]
        );
    }

    protected function getFileName(): string
    {
        return 'tile_image.' . $this->getExtension();
    }

    protected function getRelativePath(): string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            [
                $this->getRelativeDirectory(),
                $this->getFileName()
            ]
        );
    }

    public function exists(): bool
    {
        if (!ilContainer::_lookupContainerSetting($this->object_id, 'tile_image', '0')) {
            return false;
        }

        return $this->filesystem->has($this->getRelativePath());
    }

    public function getFullPath(): string
    {
        // TODO: Currently there is no option to get the relative base directory of a filesystem
        return implode(
            DIRECTORY_SEPARATOR,
            [
                ilFileUtils::getWebspaceDir(),
                $this->getRelativePath()
            ]
        );
    }

    public function createFromImportDir(string $source_dir, string $ext): void
    {
        $target_dir = implode(
            DIRECTORY_SEPARATOR,
            [
                ilFileUtils::getWebspaceDir(),
                $this->getRelativeDirectory()
            ]
        );
        $sourceFS = LegacyPathHelper::deriveFilesystemFrom($source_dir);
        $targetFS = LegacyPathHelper::deriveFilesystemFrom($target_dir);

        $sourceDir = LegacyPathHelper::createRelativePath($source_dir);
        $targetDir = LegacyPathHelper::createRelativePath($target_dir);


        $sourceList = $sourceFS->listContents($sourceDir, true);

        foreach ($sourceList as $item) {
            if ($item->isDir()) {
                continue;
            }
            try {
                $itemPath = $targetDir . '/' . substr($item->getPath(), strlen($sourceDir));
                $stream = $sourceFS->readStream($item->getPath());
                $targetFS->writeStream($itemPath, $stream);
            } catch (FileAlreadyExistsException $e) {
                // Do nothing with that type of exception
            }
        }

        ilContainer::_writeContainerSetting($this->object_id, 'tile_image', $ext);
    }
}
