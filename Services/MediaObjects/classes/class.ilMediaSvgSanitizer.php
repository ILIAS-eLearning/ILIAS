<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use enshrined\svgSanitize\Sanitizer;

/**
 * Small wrapper for svg sanitizer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaSvgSanitizer
{
    /**
     * Sanitize (temporary solution for sec issue 20339, ILIAS 5.0-5.2, not using composer autoloading yet)
     *
     * @param string $a_file file to be sanitized
     */
    public static function sanitizeFile($a_file)
    {
        $sanitizer = new Sanitizer();
        $dirtySVG = file_get_contents($a_file);
        $cleanSVG = $sanitizer->sanitize($dirtySVG);
        file_put_contents($a_file, $cleanSVG);
    }

    /**
     * Sanitize directory recursively
     *
     * @param $a_path
     */
    public static function sanitizeDir($a_path)
    {
        $path = realpath($a_path);

        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) == "svg") {
                self::sanitizeFile($name);
            }
        }
    }
}
