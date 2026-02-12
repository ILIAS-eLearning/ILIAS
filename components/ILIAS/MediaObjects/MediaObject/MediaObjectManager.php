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
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Filesystem\Stream\ZIPStream;
use ILIAS\Filesystem\Stream\FileStream;

class MediaObjectManager
{
    protected \ilLogger $logger;
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
        $this->logger = \ilLoggerFactory::getLogger('mob');
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

    public function addLocalDirectory(int $mob_id, string $dir): void
    {
        $this->repo->addLocalDirectory($mob_id, $dir);
    }

    public function addFileFromLegacyUpload(int $mob_id, string $tmp_name, string $target_path = ""): void
    {
        $this->repo->addFileFromLegacyUpload($mob_id, $tmp_name, $target_path);
    }

    public function addFileFromUpload(
        int $mob_id,
        UploadResult $result,
        string $path = "/"
    ): void {
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

    public function getLocationContent(
        int $mob_id,
        string $location
    ): string {
        return $this->repo->getLocationContent($mob_id, $location);
    }

    public function addStream(
        int $mob_id,
        string $location,
        FileStream $stream
    ): void {
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
            } catch (\Exception $e) {
            }
        }
        return $src;
    }

    public function hasLocalFile(int $mob_id, string $location): bool
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
    ): array {
        return $this->repo->getInfoOfEntry(
            $mob_id,
            $path
        );
    }

    public function deliverEntry(
        int $mob_id,
        string $path
    ): void {
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

    public function resizeImage(
        int $mob_id,
        string $location,
        string $format,
        int $width,
        int $height,
        bool $a_constrain_prop = false
    ): string {

        if (!is_int(strpos($format, "image/"))) {
            return "";
        }

        $file_path = pathinfo($location);
        $new_location = substr($file_path["basename"], 0, strlen($file_path["basename"]) -
                strlen($file_path["extension"]) - 1) . "_" .
            $width . "_" .
            $height . "." . $file_path["extension"];


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
            false,
            $this->output_options
            //->withQuality($image_quality)
            //->withFormat(ImageOutputOptions::FORMAT_PNG)
        );
        $this->repo->addStream(
            $mob_id,
            $new_location,
            $converter->getStream()
        );
        fclose($tempStream);
        return $new_location;
    }

    public function addPreviewFromUrl(
        int $mob_id,
        string $url,
        string $target_location
    ): void {
        $log = $this->logger;
        try {
            $log->debug('Trying to fetch thumbnail from URL: {thumbnail_url}', [
                'thumbnail_url' => $url,
            ]);
            $curl = new \ilCurlConnection($url);
            $curl->init(true);
            $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
            $curl->setOpt(CURLOPT_VERBOSE, true);
            $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
            $curl->setOpt(CURLOPT_TIMEOUT_MS, 5000);
            $curl->setOpt(CURLOPT_TIMEOUT, 5);
            $curl->setOpt(CURLOPT_FAILONERROR, true);
            $curl->setOpt(CURLOPT_SSL_VERIFYPEER, 1);
            $curl->setOpt(CURLOPT_SSL_VERIFYHOST, 2);

            $str = $curl->exec();
            $info = $curl->getInfo();

            $log->debug('cURL Info: {info}', [
                'info' => print_r($info, true)
            ]);

            $status = $info['http_code'] ?? '';
            if ((int) $status === 200) {
                $log->debug('Successfully fetched preview file from URL: Received {bytes} bytes', [
                    'bytes' => (string) strlen($str),
                ]);
            } else {
                $log->error('Could not fetch thumbnail from YouTube: {thumbnail_url}', [
                    'thumbnail_url' => $url,
                ]);
            }

            $res = fopen('php://memory', 'r+');
            fwrite($res, $str);
            rewind($res);
            $stream = new Stream($res);
            $this->repo->addStream(
                $mob_id,
                $target_location,
                $stream
            );
        } catch (\Exception $e) {
            $log->error('Could not fetch thumbnail from Url: {message}', [
                'message' => $e->getMessage(),
            ]);
            $log->error($e->getTraceAsString());
        }
    }

    public function getSrtFiles(int $mob_id, bool $vtt_only = false): array
    {
        $srt_files = [];
        $valid_suffixes = $vtt_only
            ? ["vtt"]
            : ["srt", "vtt"];
        foreach ($this->getFilesOfPath($mob_id, "/srt") as $i) {
            $name = explode(".", $i["basename"]);
            if (in_array($name[1], $valid_suffixes) && substr($name[0], 0, 9) == "subtitle_") {
                $srt_files[] = [
                    "file" => $i["basename"],
                    "full_path" => $i["path"],
                    "src" => $this->getLocalSrc($mob_id, $i["path"]),
                    "language" => substr($name[0], 9, 2)
                ];
            }
        }
        return $srt_files;
    }

    public function generateMissingVTT(int $mob_id): void
    {
        $names = array_map(static function (array $i) {
            return $i["file"];
        }, $this->getSrtFiles($mob_id));
        $missing_vtt = [];
        foreach ($names as $name) {
            if (str_ends_with($name, ".srt")) {
                $vtt = str_replace(".srt", ".vtt", $name);
                if (!in_array($vtt, $names) && !in_array($vtt, $missing_vtt)) {
                    $missing_vtt[] = $vtt;
                }
            }
        }
        foreach ($missing_vtt as $vtt_name) {
            $srt_name = str_replace(".vtt", ".srt", $vtt_name);
            $srt_content = stream_get_contents($this->repo->getLocationStream($mob_id, "srt/" . $srt_name)->detach());
            $vtt_content = $this->srtToVtt($srt_content);
            $this->repo->addString($mob_id, "/srt/" . $vtt_name, $vtt_content);
        }
    }

    public function srtToVtt(string $srt_text): string
    {
        // Remove UTF-8 BOM if present
        $srt_text = preg_replace('/^\xEF\xBB\xBF/', '', $srt_text);

        // Normalise line-endings and split cues
        $srt_text = preg_replace('~\r\n?~', "\n", $srt_text);
        $blocks = preg_split("/\n{2,}/", trim($srt_text));

        $vttLines = ['WEBVTT', ''];          // header + blank line

        foreach ($blocks as $block) {
            $lines = explode("\n", $block);

            if (count($lines) < 2) {
                continue;                    // malformed cue
            }

            /* cue number? allow BOM or spaces either side */
            if (preg_match('/^\s*\d+\s*$/u', $lines[0])) {
                array_shift($lines);         // drop it
            }

            /* now $lines[0] *is* the time-code line → , → . */
            $lines[0] = preg_replace(
                '/(\d{2}:\d{2}:\d{2}),(\d{3})/',
                '$1.$2',
                $lines[0]
            );

            $vttLines = array_merge($vttLines, $lines, ['']);
        }

        return implode("\n", $vttLines);
    }

    public function getLastChangeTimestamp(int $mob_id): int
    {
        return $this->repo->getLastChangeTimestamp($mob_id);
    }

    public function updateLastChange(int $mob_id): void
    {
        $this->repo->updateLastChangeTimestamp($mob_id, time());
    }
}
