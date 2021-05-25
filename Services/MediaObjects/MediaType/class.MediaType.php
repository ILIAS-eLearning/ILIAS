<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\MediaObjects\MediaType;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class MediaType
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * This has been introduced for applets long time ago an been available for
     * all mime times for several years.
     * @param $mime
     * @return bool
     */
    public function usesParameterProperty(string $mime) : bool
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
    public function usesAutoStartParameterOnly(string $location, string $mime) : bool
    {
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

    /**
     */
    public function isImage(string $mime) : bool
    {
        return is_int(strpos($mime, "image"));
    }

    /**
     */
    public function usesAltTextProperty(string $mime) : bool
    {
        return $this->isImage($mime);
    }
}
