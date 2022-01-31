<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjectTileImage implements ilObjectTileImageInterface
{
    protected ilObjectService $service;
    protected int $obj_id;
    protected \ILIAS\Filesystem\Filesystem $web;
    protected \ILIAS\FileUpload\FileUpload $upload;
    protected string $ext;

    public function __construct(ilObjectService $service, int $obj_id)
    {
        $this->service = $service;
        $this->obj_id = $obj_id;
        $this->web = $service->filesystem()->web();
        $this->upload = $service->upload();
        $this->ext = ilContainer::_lookupContainerSetting($obj_id, 'tile_image');
    }

    public function getExtension() : string
    {
        return $this->ext;
    }

    public function copy(int $target_obj_id) : void
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

    public function delete() : void
    {
        if ($this->web->hasDir($this->getRelativeDirectory())) {
            try {
                $this->web->deleteDir($this->getRelativeDirectory());
            } catch (Exception $e) {
            }
        }

        ilContainer::_deleteContainerSettings($this->obj_id, 'tile_image');
    }

    public function saveFromHttpRequest(string $tmpname) : void
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

            /** @var \ILIAS\FileUpload\DTO\UploadResult $result */
            $results = $this->upload->getResults();
            if (isset($results[$tmpname])) {
                $result = $results[$tmpname];
                $this->ext = pathinfo($result->getName(), PATHINFO_EXTENSION);
                $file_name = $this->getRelativePath();
                if ($result->isOK()) {
                    $this->upload->moveOneFileTo(
                        $result,
                        $this->getRelativeDirectory(),
                        \ILIAS\FileUpload\Location::WEB,
                        $this->getFileName(),
                        true
                    );


                    $fullpath = CLIENT_WEB_DIR . '/' . $this->getRelativeDirectory() . '/' . $this->getFileName();
                    [$width, $height, $type, $attr] = getimagesize($fullpath);
                    $min = min($width, $height);
                    ilUtil::execConvert($fullpath . "[0] -geometry " . $min . "x" . $min . "^ -gravity center -extent " . $min . "x" . $min . " " . $fullpath);
                }
            }
        }
        $this->persistImageState($file_name);
    }

    protected function persistImageState(string $filename) : void
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if ($this->web->has($filename)) {
            ilContainer::_writeContainerSetting($this->obj_id, 'tile_image', $ext);
        } else {
            ilContainer::_deleteContainerSettings($this->obj_id, 'tile_image');
        }
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function createDirectory() : void
    {
        $this->web->createDir($this->getRelativeDirectory());
    }

    public function getRelativeDirectory() : string
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

    protected function getFileName() : string
    {
        return 'tile_image.' . $this->getExtension();
    }

    protected function getRelativePath() : string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            [
                $this->getRelativeDirectory(),
                $this->getFileName()
            ]
        );
    }

    public function exists() : bool
    {
        if (!ilContainer::_lookupContainerSetting($this->obj_id, 'tile_image', '0')) {
            return false;
        }

        return $this->web->has($this->getRelativePath());
    }

    public function getFullPath() : string
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

    public function createFromImportDir(string $source_dir, string $ext) : void
    {
        $target_dir = implode(
            DIRECTORY_SEPARATOR,
            [
                ilFileUtils::getWebspaceDir(),
                $this->getRelativeDirectory()
            ]
        );
        ilUtil::rCopy($source_dir, $target_dir);
        ilContainer::_writeContainerSetting($this->obj_id, 'tile_image', $ext);
    }
}
