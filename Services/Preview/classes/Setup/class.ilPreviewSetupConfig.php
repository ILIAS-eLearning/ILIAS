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
class ilPreviewSetupConfig implements Setup\Config
{
    protected ?string $path_to_ghostscript;

    public function __construct(
        ?string $path_to_ghostscript
    ) {
        $this->path_to_ghostscript = $this->toLinuxConvention($path_to_ghostscript);
    }

    protected function toLinuxConvention(?string $p) : ?string
    {
        if (!$p) {
            return null;
        }
        return preg_replace("/\\\\/", "/", $p);
    }

    public function getPathToGhostscript() : ?string
    {
        return $this->path_to_ghostscript;
    }
}
