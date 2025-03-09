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

namespace ILIAS\MediaObjects;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Filesystem\Util\Convert\Images;
use ILIAS\Filesystem\Util\Convert\ImageOutputOptions;
use ILIAS\Filesystem\Stream\Stream;
use _PHPStan_9815bbba4\Nette\Neon\Exception;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Filesystem\Stream\ZIPStream;
use ILIAS\Filesystem\Stream\FileStream;

class MediaObjectManager
{
    protected ImageOutputOptions $output_options;
    protected Images $image_converters;
    protected MediaObjectRepository $repo;

    public function __construct(
        protected InternalDataService $data,
        InternalRepoService $repo,
        protected InternalDomainService $domain,
        protected \ilMobStakeholder $stakeholder
    ) {
        $this->repo = $repo->mediaObject();
        $this->image_converters = new Images(true);
        $this->output_options = new ImageOutputOptions();
    }

    public function create(
        int $id,
        string $title,
        int $from_mob_id = 0
    ): void {
        $this->repo->create(
            $id,
            $title,
            $this->stakeholder,
            $from_mob_id
        );
    }

    public function addLocalDirectory(int $mob_id, string $dir) : void
    {
        $this->repo->addLocalDirectory($mob_id, $dir);
    }

    public function addFileFromLegacyUpload(int $mob_id, string $tmp_name): void
    {
        $this->repo->addFileFromLegacyUpload($mob_id, $tmp_name);
    }

    public function addFileFromUpload(
        int $mob_id,
        UploadResult $result,
        string $path = "/"
    ): void
    {
        $this->repo->addFileFromUpload($mob_id, $result, $path);
    }

    public function addFileFromLocal(int $mob_id, string $tmp_name, string $path): void
    {
        $this->repo->addFileFromLocal($mob_id, $tmp_name, $path);
    }

    public function removeLocation(
        int $mob_id,
        string $location
    ): void {
        $this->repo->removeLocation($mob_id, $location);
    }

    public function getLocationStream(
        int $mob_id,
        string $location
    ): ZIPStream {
        return $this->repo->getLocationStream($mob_id, $location);
    }

    public function addStream(
        int $mob_id,
        string $location,
        FileStream $stream
    ) : void {
        $this->repo->addStream($mob_id, $location, $stream);
    }

    public function getLocalSrc(int $mob_id, string $location): string
    {
        $src = $this->repo->getLocalSrc(
            $mob_id,
            $location
        );
        if ($src === "") {  // fallback: old source
            $path_to_file = \ilObjMediaObject::_getURL($mob_id) . "/" . $location;
            try {
                $src = \ilWACSignedPath::signFile($path_to_file);
            } catch (Exception $e) {
            }
        }
        return $src;
    }

    public function hasLocalFile(int $mob_id, string $location) : bool
    {
        return $this->repo->hasLocalFile($mob_id, $location);
    }

    public function getContainerResource(
        int $mob_id
    ): ?StorableResource {
        return $this->repo->getContainerResource($mob_id);
    }

    public function getContainerResourceId(
        int $mob_id
    ): ?ResourceIdentification {
        return $this->repo->getContainerResourceId($mob_id);
    }

    public function getFilesOfPath(
        int $mob_id,
        string $dir_path
    ): array {
        return $this->repo->getFilesOfPath($mob_id, $dir_path);
    }

    public function getInfoOfEntry(
        int $mob_id,
        string $path
    ) : array
    {
        return $this->repo->getInfoOfEntry(
            $mob_id,
            $path
        );
    }

    public function deliverEntry(
        int $mob_id,
        string $path
    ) : void
    {
        $this->repo->deliverEntry($mob_id, $path);
    }


    public function generatePreview(
        int $mob_id,
        string $location,
        bool $local,
        string $format,
        int $sec = 1,
        string $target_location = "mob_vpreview.png"
    ): void {

        $is_image = is_int(strpos($format, "image/"));
        $is_video = in_array($format, ["video/mp4", "video/webm"]);

        if ($local) {
            if ($is_image) {
                $width = $height = \ilObjMediaObject::DEFAULT_PREVIEW_SIZE;
                $image_quality = 60;

                // the zip stream is not seekable, which is needed by Imagick
                // so we create a seekable stream first
                $tempStream = fopen('php://temp', 'w+');
                stream_copy_to_stream($this->repo->getLocationStream($mob_id, $location)->detach(), $tempStream);
                rewind($tempStream);
                $stream = new Stream($tempStream);

                $converter = $this->image_converters->resizeToFixedSize(
                    $stream,
                    $width,
                    $height,
                    true,
                    $this->output_options
                        ->withQuality($image_quality)
                        ->withFormat(ImageOutputOptions::FORMAT_PNG)
                );
                $this->repo->addStream(
                    $mob_id,
                    $target_location,
                    $converter->getStream()
                );
                fclose($tempStream);
            }
            if ($is_video) {
                $zip_uri = $this->repo->getContainerPath($mob_id);
                $image_str = \ilFFmpeg::extractPNGFromVideoInZip(
                    $zip_uri,
                    $location,
                    $sec
                );
                $png_res = fopen('php://memory', 'r+');
                fwrite($png_res, $image_str);
                rewind($png_res);
                $png_stream = new Stream($png_res);
                $this->repo->addStream(
                    $mob_id,
                    $target_location,
                    $png_stream
                );
            }
        }
    }

    public function addPreviewFromUrl(
        int $mob_id,
        string $url,
        string $target_location
    ) : void
    {
        $str = file_get_contents($url);
        $res = fopen('php://memory', 'r+');
        fwrite($res, $str);
        rewind($res);
        $stream = new Stream($res);
        $this->repo->addStream(
            $mob_id,
            $target_location,
            $stream
        );
    }

}
