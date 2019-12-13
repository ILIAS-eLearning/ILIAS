<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilMediaObjectSetupConfig implements Setup\Config
{
    /**
     * @var string|null
     */
    protected $path_to_ffmpeg;

    public function __construct(
        ?string $path_to_ffmpeg
    ) {
        $this->path_to_ffmpeg = $this->toLinuxConvention($path_to_ffmpeg);
    }

    protected function toLinuxConvention(?string $p) : ?string
    {
        if (!$p) {
            return null;
        }
        return preg_replace("/\\\\/", "/", $p);
    }

    public function getPathToFFMPEG() : ?string
    {
        return $this->path_to_ffmpeg;
    }
}
