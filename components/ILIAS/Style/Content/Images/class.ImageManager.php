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

namespace ILIAS\Style\Content;

use ILIAS\Style\Content\Access;
use ILIAS\Filesystem;
use ilShellUtil;
use Generator;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Style\Content\Style\StyleRepo;
use ILIAS\Filesystem\Stream\Stream;
use ILIAS\Filesystem\Util\Convert\Images;

/**
 * Main business logic for content style images
 * @author Alexander Killing <killing@leifos.de>
 */
class ImageManager
{
    protected Images $img_convert;
    protected ImageFileRepo $repo;
    protected StyleRepo $style_repo;
    protected Access\StyleAccessManager $access_manager;
    protected int $style_id;
    private Filesystem\Util\Convert\LegacyImages $image_conversion;

    public function __construct(
        int $style_id,
        Access\StyleAccessManager $access_manager,
        InternalRepoService $repo,
        protected ResourceStakeholder $stakeholder
    ) {
        global $DIC;
        $this->repo = $repo->image();
        $this->style_repo = $repo->style();
        $this->access_manager = $access_manager;
        $this->style_id = $style_id;
        $this->image_conversion = $DIC->fileConverters()->legacyImages();
        $this->img_convert = $DIC->fileConverters()->images();
    }

    /**
     * Get images of style
     * @return Generator
     * @throws Filesystem\Exception\DirectoryNotFoundException
     */
    public function getImages(bool $include_size_info = false): Generator
    {
        $rid = $this->style_repo->readRid($this->style_id);
        return $this->repo->getImages($this->style_id, $rid, $include_size_info);
    }

    public function hasLegacyDirAndNoImages(): bool
    {
        return $this->repo->hasLegacyDir($this->style_id) &&
            !$this->repo->hasImages(
                $this->style_id,
                $this->style_repo->readRid($this->style_id)
            );
    }

    public function filenameExists(string $filename): bool
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
    public function getWebPath(Image $image): string
    {
        return $this->repo->getWebPath($image->getPath());
    }

    // get image data object by filename
    public function getByFilename(string $filename): Image
    {
        $rid = $this->style_repo->readRid($this->style_id);
        return $this->repo->getByFilename($this->style_id, $rid, $filename);
    }

    // resize image
    public function resizeImage(
        string $filename,
        int $width,
        int $height,
        bool $constrain_proportions
    ): void {
        $rid = $this->style_repo->readRid($this->style_id);
        if ($this->filenameExists($filename)) {
            // the zip stream is not seekable, which is needed by Imagick
            // so we create a seekable stream first
            $tempStream = fopen('php://temp', 'w+');
            stream_copy_to_stream($this->repo->getImageStream($rid, $filename)->detach(), $tempStream);
            rewind($tempStream);
            $stream = new Stream($tempStream);

            $converter = $this->img_convert->resizeToFixedSize(
                $stream,
                $width,
                $height
            );
            $this->repo->addStream(
                $rid,
                $filename,
                $converter->getStream()
            );
            fclose($tempStream);
        }
    }

    // resize image
    public function supportsResize(
        Image $image
    ): bool {
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

    public function deleteByFilename(string $filename): void
    {
        $this->repo->deleteImageByFilename($this->style_id, $filename);
    }

    public function importFromUploadResult(
        UploadResult $result
    ): void {
        $rid = $this->style_repo->readRid($this->style_id);
        if ($rid !== "") {
            $this->repo->importFromUploadResult(
                $rid,
                $result
            );
        }
    }

}
