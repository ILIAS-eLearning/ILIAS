<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

require_once __DIR__ . '/bootstrap.php';

/**
 * @author Lukas Scharmer <lscharmer@databay.de>
 */
class ilServicesQTISuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();

        $dir = __DIR__;
        $classes = [];

        $files = new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($files, RecursiveIteratorIterator::LEAVES_ONLY);
        $files = new RegExIterator($files, '/\.php$/');

        foreach ($files as $file) {
            $file = $file->getPathname();
            $className = preg_replace(['@^.*/@', '/^class./', '/\.php$/'], '', $file);
            require_once $file;
            $classes[] = $className;
        }
        array_map([$suite, 'addTestSuite'], array_filter(array_filter($classes, 'class_exists'), [self::class, 'notSelf']));

        return $suite;
    }

    private static function notSelf(string $className) : bool
    {
        return self::class !== $className;
    }
}
