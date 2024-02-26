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

/**
 * Base class for all file (directory) operations
 * This class is abstract and needs to be extended
 *
 * @deprecated Will be removed in ILIAS 10. Use ILIAS ResourceStorageService as replacement.
 */
abstract class ilFile
{
    protected string $path;

    /**
     * delete trailing slash of path variables
     */
    public function deleteTrailingSlash(string $a_path): string
    {
        // DELETE TRAILING '/'
        if (substr($a_path, -1) == '/' or substr($a_path, -1) == "\\") {
            $a_path = substr($a_path, 0, -1);
        }

        return $a_path;
    }
}
