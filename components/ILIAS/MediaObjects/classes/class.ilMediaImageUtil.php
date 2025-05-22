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
 * Image utility class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaImageUtil
{
    /**
     * Get image size from location
     * @throws ilCurlConnectionException
     */
    public static function getImageSize(string $a_location): ?array
    {
        try {
            $size = getimagesizefromstring(file_get_contents($a_location));
        } catch (Exception $e) {
            $size = false;
        }

        if (!isset($size) || $size === false) {
            $size = [0,0];
        }
        return $size;
    }
}
