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

namespace ILIAS\User\Export;

use ilFileUtils;

/**
 * Wiki HTML exporter class
 */
class UserHtmlExport
{
    public function __construct()
    {
    }

    /**
     * @param int[]  $user_ids
     */
    public function exportUserImages(string $dir, array $user_ids): void
    {
        $base_dir = $dir . "/data/" . CLIENT_ID . "/usr_images";
        ilFileUtils::makeDirParents($base_dir);
        foreach ($user_ids as $id) {
            $source = "./data/" . CLIENT_ID . "/usr_images/usr_$id.jpg";
            if (is_file($source)) {
                copy($source, $base_dir . "/usr_$id.jpg");
            }
        }
    }
}
