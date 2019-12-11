<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilPDFGenerationSetupConfig implements Setup\Config
{
    /**
     * @var string|null
     */
    protected $path_to_phantom_js;

    public function __construct(
        ?string $path_to_phantom_js
    ) {
        $this->path_to_phantom_js = $this->toLinuxConvention($path_to_phantom_js);
    }

    protected function toLinuxConvention(?string $p) : ?string
    {
        if (!$p) {
            return null;
        }
        return preg_replace("/\\\\/", "/", $p);
    }

    public function getPathToPhantomJS() : ?string
    {
        return $this->path_to_phantom_js;
    }
}
