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

namespace ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\Extract;

use ILIAS\Filesystem\Stream\Stream;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Video extends General implements Extractor
{
    private const BLACK_PICTURE_THRESHOLD = 99;

    public function readImage(\Imagick $img, Stream $stream, PagesToExtract $definition): \Imagick
    {
        $amount_of_previews = $definition->getMaxPages();
        $max_size = escapeshellarg((string) ($definition->getMaxSize() * 1));
        $ffmpeg = escapeshellcmd(PATH_TO_FFMPEG);
        $input_video = escapeshellarg($stream->getMetadata()['uri']);

        // Get total duration of the video
        $duration = shell_exec(
            "ffprobe -v error -select_streams v:0 -show_entries stream=duration -of default=noprint_wrappers=1 $input_video"
        );
        // extract e.g. 14.4000... from duration=14.400000
        preg_match('/.*=([\d.]*)/m', $duration, $matches);

        $duration = (float) ($matches[1] ?? 0.0);

        // Calculate frame rate
        $frame_rate = shell_exec(
            "ffprobe -v error -select_streams v:0 -show_entries stream=r_frame_rate -of default=noprint_wrappers=1 $input_video"
        );

        preg_match('/.*=([\d]*)\/([\d]*)/m', $frame_rate, $matches);
        $numerator = (int) ($matches[1] ?? 30);
        $denominator = (int) ($matches[2] ?? 1);

        $frame_rate = (int) ((int) $numerator / (int) $denominator);
        $frame_rate = $frame_rate === 0 ? 30 : $frame_rate;

        // Loop to extract preview images
        for ($i = 1; $i <= $amount_of_previews; $i++) {
            $timestamp = escapeshellarg((string) (($i - 1) / $amount_of_previews * $duration));
            $exec = "$ffmpeg -ss $timestamp -i $input_video -vf \"select='eq(n\,$i)',scale=$max_size:-1:flags=lanczos\" -vframes 1 -f image2pipe -vcodec mjpeg -";
            $preview = shell_exec($exec) ?? '';
            if (empty($preview)) {
                continue;
            }
            if ($this->isPreviewMostlyBlack($preview)) {
                continue;
            }
            $img->readImageBlob($preview);
        }

        return $img;
    }

    public function getTargetFormat(): string
    {
        return 'jpg';
    }

    private function isPreviewMostlyBlack(string $image_blob): bool
    {
        try {
            $image = new \Imagick();
            $image->readImageBlob($image_blob);
        } catch (\ImagickException $e) {
            return true;
        }

        // Get image dimensions
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        // Define the step size for sampling (adjust according to image size)
        $stepSize = 10; // Sample every 10 pixels

        // Set threshold for considering a pixel as black
        $blackThreshold = 10; // Adjust this value according to your needs

        // Count black pixels
        $blackPixelCount = 0;
        $totalSamples = 0;

        // Loop through the image and sample pixels
        for ($x = 0; $x < $width; $x += $stepSize) {
            for ($y = 0; $y < $height; $y += $stepSize) {
                // Get pixel color at position (x, y)
                $pixelColor = $image->getImagePixelColor($x, $y);

                // Get color channels
                $colors = $pixelColor->getColor();

                // Calculate pixel brightness (average of RGB values)
                $brightness = ($colors['r'] + $colors['g'] + $colors['b']) / 3;

                // Check if pixel is black (below threshold)
                if ($brightness < $blackThreshold) {
                    $blackPixelCount++;
                }

                $totalSamples++;
            }
        }

        // Calculate percentage of black pixels
        $blackPercentage = ($blackPixelCount / $totalSamples) * 100;
        // Clear Imagick object
        $image->clear();
        $image->destroy();
        // Determine if image is mostly empty or black
        return $blackPercentage >= self::BLACK_PICTURE_THRESHOLD;
    }

}
