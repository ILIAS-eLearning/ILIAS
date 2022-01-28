<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use \ILIAS\Filesystem;
use \ILIAS\Data\DataSize;
use \ILIAS\FileUpload\FileUpload;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ImageFileRepo
{
    protected const DIR_PATH = "sty/sty_%id%/images";

    /**
     * @var DataFactory
     */
    protected $factory;

    /**
     * @var Filesystem\Filesystem
     */
    protected $web_files;

    /**
     * @var FileUpload
     */
    protected $upload;

    public function __construct(
        DataFactory $factory,
        Filesystem\Filesystem $web_files,
        FileUpload $upload
    ) {
        $this->web_files = $web_files;
        $this->factory = $factory;
        $this->upload = $upload;
    }

    // get image directory
    protected function dir(int $style_id) : string
    {
        return str_replace("%id%", $style_id, self::DIR_PATH);
    }

    /**
     * Get images of style
     * @param int $style_id
     * @return \Generator
     * @throws Filesystem\Exception\DirectoryNotFoundException
     */
    public function getImages(
        int $style_id
    ) : \Generator {
        $dir = $this->dir($style_id);
        if ($this->web_files->hasDir($dir)) {
            foreach ($this->web_files->listContents($dir) as $meta) {
                if ($meta->isFile()) {
                    $size = $this->web_files->getSize(
                        $meta->getPath(),
                        DataSize::KB
                    );
                    $image_size = getimagesize($this->getWebPath($meta->getPath()));
                    $width = $image_size[0] ?? 0;
                    $height = $image_size[1] ?? 0;
                    yield $this->factory->image(
                        $meta->getPath(),
                        $size,
                        $width,
                        $height
                    );
                }
            }
        }
    }

    // get full web path for relative file path
    public function getWebPath(string $path) : string
    {
        return ILIAS_WEB_DIR . "/" . CLIENT_ID . "/" . $path;
    }

    // get image data object by filename
    public function getByFilename(int $style_id, string $filename) : ?Image
    {
        /** @var Image $i */
        foreach ($this->getImages($style_id) as $i) {
            if ($i->getFilename() == $filename) {
                return $i;
            }
        }
        return null;
    }

    /**
     * @param int    $style_id
     * @throws Filesystem\Exception\IOException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    public function uploadImage(int $style_id) : void
    {
        $upload = $this->upload;

        $dir = $this->dir($style_id);
        if (!$this->web_files->hasDir($dir)) {
            $this->web_files->createDir($dir);
        }
        if ($upload->hasUploads() && !$upload->hasBeenProcessed()) {
            $upload->process();
            $result = array_values($upload->getResults())[0];
            if ($result->getStatus() == \ILIAS\FileUpload\DTO\ProcessingStatus::OK) {
                $upload->moveFilesTo($dir, \ILIAS\FileUpload\Location::WEB);
            }
        }
    }

    // delete image
    public function deleteImageByFilename(int $style_id, string $filename) : void
    {
        $dir = $this->dir($style_id);
        $this->web_files->delete($dir . "/" . $filename);
    }
}
