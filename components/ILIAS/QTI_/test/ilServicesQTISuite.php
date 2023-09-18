<?php

declare(strict_types=1);

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
 ********************************************************************
 */

use PHPUnit\Framework\TestSuite;

require_once __DIR__ . '/bootstrap.php';

/**
 * @author Lukas Scharmer <lscharmer@databay.de>
 */
class ilServicesQTISuite extends TestSuite
{
    public static function suite(): self
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

    private static function notSelf(string $className): bool
    {
        return self::class !== $className;
    }
}
