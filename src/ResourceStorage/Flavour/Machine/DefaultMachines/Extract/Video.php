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
        $duration = (float) explode('=', $duration)[1];

        // Calculate frame rate
        $frame_rate = shell_exec(
            "ffprobe -v error -select_streams v:0 -show_entries stream=r_frame_rate -of default=noprint_wrappers=1 $input_video"
        );
        [$numerator, $denominator] = explode('/', $frame_rate);
        $frame_rate = (int) ((int) $numerator / (int) $denominator);

        // Loop to extract preview images
        for ($i = 1; $i <= $amount_of_previews; $i++) {
            $timestamp = escapeshellarg((string) (($i - 1) / $amount_of_previews * $duration));
            $exec = "$ffmpeg -ss $timestamp -i $input_video -vf \"select='eq(n\,$i)',scale=$max_size:-1:flags=lanczos\" -vframes 1 -f image2pipe -";
            $img->readImageBlob(shell_exec($exec) ?? '');
        }

        return $img;
    }

}
