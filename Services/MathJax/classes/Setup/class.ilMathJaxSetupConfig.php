<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilMathJaxSetupConfig implements Setup\Config
{
    /**
     * @var string|null
     */
    protected $path_to_latex_cgi;

    public function __construct(
        ?string $path_to_latex_cgi
    ) {
        $this->path_to_latex_cgi = $this->toLinuxConvention($path_to_latex_cgi);
    }

    protected function toLinuxConvention(?string $p) : ?string
    {
        if (!$p) {
            return null;
        }
        return preg_replace("/\\\\/", "/", $p);
    }

    public function getPathToLatexCGI() : ?string
    {
        return $this->path_to_latex_cgi;
    }
}
