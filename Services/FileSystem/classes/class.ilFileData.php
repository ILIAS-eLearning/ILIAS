<?php
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
/**
 * @deprecated
 */
class ilFileData extends ilFile
{
    public function __construct()
    {
        $this->path = defined('CLIENT_DATA_DIR') ? CLIENT_DATA_DIR : '';
    }

    public function checkPath(string $a_path): bool
    {
        if (is_writable($a_path)) {
            return true;
        } else {
            return false;
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
