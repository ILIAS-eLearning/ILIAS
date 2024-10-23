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
        string $title
    ): void {
        $this->repo->create(
            $id,
            $title,
            $this->stakeholder
        );
    }

    public function addFileFromLegacyUpload(int $mob_id, string $tmp_name): void
    {
        $this->repo->addFileFromLegacyUpload($mob_id, $tmp_name);
    }

    public function addFileFromUpload(int $mob_id, UploadResult $result): void
    {
        $this->repo->addFileFromUpload($mob_id, $result);
    }

    public function getLocationSrc(int $mob_id, string $location): string
    {
        return $this->repo->getLocationSrc($mob_id, $location);
    }

    public function generatePreview(
        int $mob_id,
        string $std_location,
        bool $local,
        string $format,
        int $sec = 1
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
                stream_copy_to_stream($this->repo->getLocationStream($mob_id, $std_location)->detach(), $tempStream);
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
                    "mob_vpreview.png",
                    $converter->getStream()
                );
                fclose($tempStream);
            }
            if ($is_video) {
                $zip_uri = $this->repo->getContainerPath($mob_id);
                $image_str = \ilFFmpeg::extractPNGFromVideoInZip(
                    $zip_uri,
                    $std_location,
                    $sec
                );
                $png_res = fopen('php://memory', 'r+');
                fwrite($png_res, $image_str);
                rewind($png_res);
                $png_stream = new Stream($png_res);
                $this->repo->addStream(
                    $mob_id,
                    "mob_vpreview.png",
                    $png_stream
                );
            }
        }
    }

}
