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

namespace ILIAS\Filesystem;

/**
 * This Util class is a collection of static helper methods to provide file system related functionality.
 * Currently you can use it to sanitize file names which are compatible with the ILIAS file system.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Util
{
    private const FUNKY_WHITESPACES = '#\p{C}+#u';
    private const ZERO_JOINER = '/\\x{00ad}|\\x{0083}|\\x{200c}|\\x{200d}|\\x{2062}|\\x{2063}/iu';
    private const SOFT_HYPHEN = "/\\x{00a0}/iu";
    private const CONTROL_CHARACTER = "/\\x{00a0}/iu";

    public static function sanitizeFileName(string $filename) : string
    {
        // remove control characters
        $filename = preg_replace('/[\x00-\x1F\x7F]/u', '', $filename);
        $filename = preg_replace(self::CONTROL_CHARACTER, '', $filename);

        // remove other characters
        $filename = preg_replace(self::FUNKY_WHITESPACES, '', $filename);
        $filename = preg_replace(self::SOFT_HYPHEN, ' ', $filename);
        $filename = preg_replace(self::ZERO_JOINER, '', $filename);

        // UTF normalization form C
        return \UtfNormal::NFC($filename);
    }
}
