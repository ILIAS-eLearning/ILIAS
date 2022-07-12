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
class ilFileSystemSetupConfig implements Setup\Config
{
    protected string $data_dir;

    public function __construct(
        string $data_dir
    ) {
        $this->data_dir = $this->normalizePath($data_dir);
    }

    protected function normalizePath(string $p) : ?string
    {
        $p = preg_replace("/\\\\/", "/", $p);
        return preg_replace("%/+$%", "", $p);
    }

    public function getDataDir() : string
    {
        return $this->data_dir;
    }

    public function getWebDir() : string
    {
        return dirname(__DIR__, 4) . "/data";
    }
}
