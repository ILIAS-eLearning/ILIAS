<?php

use ILIAS\Setup;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilPDFGenerationSetupConfig implements Setup\Config
{
    /**
     * @var string|null
     */
    protected ?string $path_to_phantom_js;

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
