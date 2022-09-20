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

use ILIAS\Filesystem\Util\LegacyPathHelper;
use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;

class ilObjectTileImage implements ilObjectTileImageInterface
{
    protected ilObjectService $service;
    protected int $obj_id;
    protected Filesystem $web;
    protected FileUpload $upload;
    protected string $ext;

    public function __construct(ilObjectService $service, int $obj_id)
    {
        $this->service = $service;
        $this->obj_id = $obj_id;
        $this->web = $service->filesystem()->web();
        $this->upload = $service->upload();
        $this->ext = ilContainer::_lookupContainerSetting($obj_id, 'tile_image');
    }

    public function getExtension(): string
    {
        return $this->ext;
    }

    public function copy(int $target_obj_id): void
    {
        if (!$this->exists()) {
            ilContainer::_deleteContainerSettings($target_obj_id, 'tile_image');
            return;
        }

        try {
            $this->web->copy(
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
        if ($this->web->hasDir($this->getRelativeDirectory())) {
            try {
                $this->web->deleteDir($this->getRelativeDirectory());
            } catch (Exception $e) {
            }
        }

        ilContainer::_deleteContainerSettings($this->obj_id, 'tile_image');
    }

    public function saveFromHttpRequest(string $tmp_name): void
    {
        $this->createDirectory();

        $file_name = $this->getRelativePath();
        if ($this->web->has($file_name)) {
            $this->web->delete($file_name);
        }

        if ($this->upload->hasUploads()) {
            if (!$this->upload->hasBeenProcessed()) {
                $this->upload->process();
            }

            /** @var UploadResult $result */
            $results = $this->upload->getResults();
            if (isset($results[$tmp_name])) {
                $result = $results[$tmp_name];
                $this->ext = pathinfo($result->getName(), PATHINFO_EXTENSION);
                $file_name = $this->getRelativePath();
                if ($result->isOK()) {
                    $this->upload->moveOneFileTo(
                        $result,
                        $this->getRelativeDirectory(),
                        Location::WEB,
                        $this->getFileName(),
                        true
                    );


                    $fullpath = CLIENT_WEB_DIR . '/' . $this->getRelativeDirectory() . '/' . $this->getFileName();
                    [$width, $height, , ] = getimagesize($fullpath);
                    $min = min($width, $height);
                    ilShellUtil::execConvert(
                        $fullpath .
                        "[0] -geometry " .
                        $min .
                        "x" .
                        $min .
                        "^ -gravity center -extent " .
                        $min .
                        "x" .
                        $min .
                        " " .
                        $fullpath
                    );
                }
            }
        }
        $this->persistImageState($file_name);
    }

    protected function persistImageState(string $filename): void
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if ($this->web->has($filename)) {
            ilContainer::_writeContainerSetting($this->obj_id, 'tile_image', $ext);
        } else {
            ilContainer::_deleteContainerSettings($this->obj_id, 'tile_image');
        }
    }

    /**
     * @throws IOException
     */
    protected function createDirectory(): void
    {
        $this->web->createDir($this->getRelativeDirectory());
    }

    public function getRelativeDirectory(): string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            [
                'obj_data',
                'tile_image',
                'tile_image_' . $this->obj_id
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
        if (!ilContainer::_lookupContainerSetting($this->obj_id, 'tile_image', '0')) {
            return false;
        }

        return $this->web->has($this->getRelativePath());
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

        ilContainer::_writeContainerSetting($this->obj_id, 'tile_image', $ext);
    }
}
