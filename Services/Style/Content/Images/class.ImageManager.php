<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use \ILIAS\Style\Content\Access;
use \ILIAS\Filesystem;

/**
 * Main business logic for content style images
 * @author Alexander Killing <killing@leifos.de>
 */
class ImageManager
{
    /**
     * @var ImageFileRepo
     */
    protected $repo;

    /**
     * @var Access\StyleAccessManager
     */
    protected $access_manager;

    /**
     * @var int
     */
    protected $style_id;

    public function __construct(
        int $style_id,
        Access\StyleAccessManager $access_manager,
        ImageFileRepo $repo
    ) {
        $this->repo = $repo;
        $this->access_manager = $access_manager;
        $this->style_id = $style_id;
    }

    /**
     * Get images of style
     * @return \Generator
     * @throws Filesystem\Exception\DirectoryNotFoundException
     */
    public function getImages() : \Generator
    {
        return $this->repo->getImages($this->style_id);
    }

    public function filenameExists(string $filename) : bool
    {
        /** @var Image $i */
        foreach ($this->getImages() as $i) {
            if ($i->getFilename() == $filename) {
                return true;
            }
        }
        return false;
    }

    // get web data dir path for output
    public function getWebPath(Image $image) : string
    {
        return $this->repo->getWebPath($image->getPath());
    }

    // get image data object by filename
    public function getByFilename(string $filename) : Image
    {
        return $this->repo->getByFilename($this->style_id, $filename);
    }

    // resize image
    public function resizeImage(
        string $filename,
        int $width,
        int $height,
        bool $constrain_proportions
    ) : void {
        if ($this->filenameExists($filename)) {
            $file = $this->getWebPath($this->getByFilename($filename));
            \ilUtil::resizeImage(
                $file,
                $file,
                (int) $width,
                (int) $height,
                $constrain_proportions
            );
        }
    }

    // resize image
    public function supportsResize(
        Image $image
    ) : bool {
        // for svg, see
        // https://stackoverflow.com/questions/6532261/how-do-you-get-the-width-and-height-of-an-svg-picture-in-php
        if (in_array(
            strtolower(pathinfo($image->getFilename(), PATHINFO_EXTENSION)),
            ["jpg", "jpeg", "gif", "png"]
        )) {
            return true;
        }
        return false;
    }

    // upload image
    public function uploadImage() : void
    {
        $this->repo->uploadImage($this->style_id);
    }

    public function deleteByFilename(string $filename) : void
    {
        $this->repo->deleteImageByFilename($this->style_id, $filename);
    }
}
