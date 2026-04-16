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

use ILIAS\Filesystem;
use ILIAS\Data\DataSize;
use ILIAS\FileUpload\FileUpload;
use Generator;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Repository\IRSS\IRSSWrapper;
use ILIAS\Filesystem\Stream\ZIPStream;
use ILIAS\Filesystem\Stream\FileStream;

class ImageFileRepo
{
    protected const DIR_PATH = "sty/sty_%id%/images";

    protected InternalDataService $factory;
    protected Filesystem\Filesystem $web_files;
    protected FileUpload $upload;

    public function __construct(
        InternalDataService $factory,
        Filesystem\Filesystem $web_files,
        FileUpload $upload,
        protected IRSSWrapper $irss
    ) {
        $this->web_files = $web_files;
        $this->factory = $factory;
        $this->upload = $upload;
    }

    // get image directory
    protected function dir(int $style_id): string
    {
        return str_replace("%id%", (string) $style_id, self::DIR_PATH);
    }

    /**
     * Get images of style
     * @param int $style_id
     * @return Generator
     * @throws Filesystem\Exception\DirectoryNotFoundException
     */
    public function getImages(
        int $style_id,
        string $rid,
        bool $include_size_info = false,
        bool $include_legacy_dir = true
    ): Generator {
        $has_images = false;
        if ($rid !== "") {
            $unzip = $this->irss->getContainerZip($rid);
            $uri = $this->irss->stream($rid)->getMetadata("uri");
            $zip_archive = new \ZipArchive();
            $zip_archive->open($uri, \ZipArchive::RDONLY);

            foreach ($unzip->getPaths() as $path) {
                if (str_starts_with($path, ".")) {
                    continue;
                }
                if (!str_starts_with($path, "images")) {
                    continue;
                }
                if (!in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ["jpg", "png", "gif", "svg"])) {
                    continue;
                }
                $att = $zip_archive->statName($path);
                if ($include_size_info) {
                    $full_path = $this->irss->getContainerUri($rid, $path);
                    try {
                        $stream = $this->irss->getStreamOfContainerEntry($rid, $path);
                        $content = $stream->getContents();
                        $image_size = getimagesizefromstring($content);
                    } catch (\Exception $e) {
                    }
                    $width = $image_size[0] ?? 0;
                    $height = $image_size[1] ?? 0;
                } else {
                    $width = 0;
                    $height = 0;
                }
                $has_images = true;
                yield $this->factory->image(
                    $this->irss->getContainerUri($rid, $path),
                    new DataSize($att["size"], DataSize::KB),
                    $width,
                    $height
                );
            }
        }

        if ($has_images || !$include_legacy_dir) {
            return;
        }

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

    public function hasLegacyDir(
        int $style_id
    ): bool {
        $dir = $this->dir($style_id);
        if ($this->web_files->hasDir($dir)) {
            return true;
        }
        return false;
    }

    public function hasImages(
        int $style_id,
        string $rid
    ): bool {
        $images = iterator_to_array($this->getImages($style_id, $rid, false, false));
        return count($images) > 0;
    }

    public function getImageStream(
        string $rid,
        string $image
    ): ZIPStream {
        return $this->irss->getStreamOfContainerEntry(
            $rid,
            "images/" . $image
        );
    }

    public function addStream(
        string $rid,
        string $image,
        FileStream $stream
    ): void {
        $this->irss->addStreamToContainer(
            $rid,
            $stream,
            "images/" . $image
        );
    }

    // get full web path for relative file path
    public function getWebPath(string $path): string
    {
        if (str_starts_with($path, "http")) {
            return $path;
        }
        return ILIAS_WEB_DIR . "/" . CLIENT_ID . "/" . $path;
    }

    // get image data object by filename
    public function getByFilename(int $style_id, string $rid, string $filename): ?Image
    {
        /** @var Image $i */
        foreach ($this->getImages($style_id, $rid) as $i) {
            if ($i->getFilename() == $filename) {
                return $i;
            }
        }
        return null;
    }

    // delete image
    public function deleteImageByFilename(int $style_id, string $filename): void
    {
        $dir = $this->dir($style_id);
        $this->web_files->delete($dir . "/" . $filename);
    }

    public function importFromUploadResult(
        string $rid,
        UploadResult $result,
    ): void {
        $this->irss->addUploadToContainer(
            $rid,
            $result
        );
    }
}
