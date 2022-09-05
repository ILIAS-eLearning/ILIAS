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

namespace ILIAS\MediaObjects\MediaType;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class MediaTypeManager
{
    public function __construct()
    {
    }

    /**
     * This has been introduced for applets long time ago and been available for
     * all mime times for several years.
     */
    public function usesParameterProperty(string $mime): bool
    {
        return !in_array($mime, ["image/x-ms-bmp", "image/gif", "image/jpeg", "image/x-portable-bitmap",
                     "image/png", "image/psd", "image/tiff", "application/pdf"]);
    }

    /**
     * Check whether only autostart parameter should be supported (instead
     * of parameters input field)
     *
     * This should be the same behaviour as mp3/flv in page.xsl
     */
    public function usesAutoStartParameterOnly(
        string $location,
        string $mime
    ): bool {
        $lpath = pathinfo($location);
        if ($lpath["extension"] == "mp3" && $mime == "audio/mpeg") {
            return true;
        }
        if ($lpath["extension"] == "flv") {
            return true;
        }
        if (in_array($mime, array("video/mp4", "video/webm"))) {
            return true;
        }
        return false;
    }

    public function isImage(string $mime): bool
    {
        return is_int(strpos($mime, "image"));
    }

    public function isAudio(string $mime): bool
    {
        return in_array($mime, $this->getAudioMimeTypes());
    }

    public function usesAltTextProperty(string $mime): bool
    {
        return $this->isImage($mime);
    }

    /**
     * @return string[]
     */
    public function getVideoMimeTypes(): array
    {
        return [
            "video/vimeo",
            "video/youtube",
            "video/mp4"
        ];
    }

    /**
     * @return string[]
     */
    public function getVideoSuffixes(): array
    {
        return [
            "mp4"
        ];
    }

    /**
     * @return string[]
     */
    public function getAudioMimeTypes(): array
    {
        return [
            "audio/mpeg"
        ];
    }

    /**
     * @return string[]
     */
    public function getAudioSuffixes(): array
    {
        return [
            "mp3"
        ];
    }

    /**
     * @return string[]
     */
    public function getImageMimeTypes(): array
    {
        return [
            "image/png",
            "image/jpeg",
            "image/gif"
        ];
    }

    /**
     * @return string[]
     */
    public function getImageSuffixes(): array
    {
        return [
            "jpeg", "jpg", "png"
        ];
    }
}
