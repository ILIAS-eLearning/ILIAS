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

use enshrined\svgSanitize\Sanitizer;

/**
 * Small wrapper for svg sanitizer
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaSvgSanitizer
{
    /**
     * Sanitize (temporary solution for sec issue 20339, ILIAS 5.0-5.2, not using composer autoloading yet)
     * @param string $a_file file to be sanitized
     */
    public static function sanitizeFile(
        string $a_file
    ): void {
        $sanitizer = new Sanitizer();
        $dirtySVG = file_get_contents($a_file);
        $cleanSVG = $sanitizer->sanitize($dirtySVG);
        file_put_contents($a_file, $cleanSVG);
    }

    /**
     * Sanitize directory recursively
     */
    public static function sanitizeDir(
        string $a_path
    ): void {
        $path = realpath($a_path);

        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) == "svg") {
                self::sanitizeFile($name);
            }
        }
    }
}
