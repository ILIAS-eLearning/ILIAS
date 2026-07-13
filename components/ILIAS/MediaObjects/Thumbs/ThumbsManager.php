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

namespace ILIAS\MediaObjects\Thumbs;

use ILIAS\MediaObjects\InternalDataService;
use ILIAS\MediaObjects\InternalDomainService;
use ILIAS\Filesystem\Stream\Stream;
use ILIAS\Filesystem\Util\Convert\ImageOutputOptions;
use ILIAS\Filesystem\Util\Convert\Images;
use ILIAS\MediaObjects\InternalGUIService;
use ILIAS\Filesystem\Util\Convert\ImageConverter;
use ILIAS\Filesystem\Util\Convert\ImageConversionOptions;
use ILIAS\MediaObjects\InternalRepoService;

class ThumbsManager
{
    protected \ILIAS\MediaObjects\MediaObjectRepository $repo;
    protected \ILIAS\MediaObjects\MediaObjectManager $media_manager;
    protected ImageOutputOptions $output_options;
    protected Images $image_converters;

    public function __construct(
        protected InternalDataService $data,
        InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
        $this->media_manager = $this->domain->mediaObject();
        $this->image_converters = new Images(true);
        $this->output_options = new ImageOutputOptions();
        $this->repo = $repo->mediaObject();
    }

    protected function getThumbPath(): string
    {
        return "thumbs/Standard.png";
    }

    /**
     * For use in browser src of images
     */
    public function getThumbSrc(int $mob_id): string
    {
        $mob = new \ilObjMediaObject($mob_id);
        $item = $mob->getMediaItem("Standard");
        // for svg return standard src
        if ($item?->getFormat() === "image/svg+xml") {
            return $mob->getStandardSrc();
        }

        // if thumb exists -> return
        $thumb_path = $this->getThumbPath();
        if ($this->media_manager->hasLocalFile($mob_id, $thumb_path)) {
            return $this->media_manager->getLocalSrc($mob_id, $thumb_path);
        }

        // if preview exists -> return
        $preview_src = $this->getPreviewSrc($mob_id);
        if ($preview_src !== "") {
            return $preview_src;
        }

        // if not tried already, create thumb and return
        if ($item?->getLocationType() === "LocalFile") {
            if ($item?->getThumbTried() !== "y") {
                $this->createThumb(
                    $mob_id,
                    $item?->getLocation(),
                    $item?->getFormat(),
                    $thumb_path
                );
                $item?->writeThumbTried("y");
                if ($this->media_manager->hasLocalFile($mob_id, $thumb_path)) {
                    return $this->media_manager->getLocalSrc($mob_id, $thumb_path);
                }
            }
        } else {
            if (str_starts_with($item?->getFormat(), "image/")) {
                return $item?->getLocation();
            }
        }

        // send generic thumb src
        return \ilUtil::getImagePath("standard/icon_mob.svg");
    }

    protected function createThumb(
        int $mob_id,
        string $location,
        string $format,
        string $target_location,
    ): void {
        $is_image = is_int(strpos($format, "image/"));
        if ($is_image) {
            if (!$this->media_manager->hasLocalFile($mob_id, $location)) {
                return;
            }
            $width = $height = \ilObjMediaObject::DEFAULT_THUMB_SIZE;
            $image_quality = 90;

            // the zip stream is not seekable, which is needed by Imagick
            // so we create a seekable stream first
            $tempStream = fopen('php://temp', 'w+');
            stream_copy_to_stream($this->media_manager->getLocationStream($mob_id, $location)->detach(), $tempStream);
            rewind($tempStream);
            $stream = new Stream($tempStream);

            $converter = $this->resizeToFixedSize(
                $stream,
                $width,
                $height,
                true,
                $this->output_options
                    ->withQuality($image_quality)
                    ->withFormat(ImageOutputOptions::FORMAT_PNG)
            );
            $this->media_manager->addStream(
                $mob_id,
                $target_location,
                $converter->getStream()
            );
            fclose($tempStream);
        }
    }

    public function createPreview(
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

                $converter = $this->resizeToFixedSize(
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


    protected function resizeToFixedSize(
        Stream $stream,
        int $width,
        int $height,
        bool $crop_if_true_and_resize_if_false,
        ImageOutputOptions $output_options
    ): ImageConverter {
        $conversion_options = (new ImageConversionOptions())
            ->withMakeTemporaryFiles(false)
            ->withThrowOnError(false)
            ->withBackgroundColor('#FFFFFF');

        return new ImageConverter(
            $conversion_options
                ->withWidth($width)
                ->withHeight($height)
                ->withCrop($crop_if_true_and_resize_if_false)
                ->withKeepAspectRatio(true),
            $output_options,
            $stream
        );
    }


    /**
     * For use in browser src of images
     */
    public function getPreviewSrc(int $mob_id): string
    {
        $mob = new \ilObjMediaObject($mob_id);
        $item = $mob->getMediaItem("Standard");
        if ($item?->getLocationType() === "Reference" && str_starts_with($item?->getFormat(), "image/")) {
            return $item?->getLocation();
        }

        $ppics = array(
            "mob_vpreview.png",
            "mob_vpreview.jpg",
            "mob_vpreview.jpeg");
        foreach ($ppics as $pic) {
            if ($this->media_manager->hasLocalFile($mob_id, $pic)) {
                return $this->media_manager->getLocalSrc($mob_id, $pic);
            }
        }
        return "";
    }
}
