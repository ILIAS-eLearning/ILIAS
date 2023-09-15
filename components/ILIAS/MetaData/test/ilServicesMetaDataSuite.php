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

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestCase;

class ilServicesMetaDataSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilServicesMetaDataSuite();

        $dir = new RecursiveDirectoryIterator(__DIR__);
        $iterator = new RecursiveIteratorIterator($dir);
        $test_files = new RegexIterator($iterator, '/Test\.php$/');

        foreach ($test_files as $test_file) {
            /** @var SplFileInfo $test_file */
            require_once $test_file->getPathname();

            $className = preg_replace('/(.*?)(\.php)/', '$1', $test_file->getBasename());

            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                if (
                    !$reflection->isAbstract() &&
                    !$reflection->isInterface() &&
                    $reflection->isSubclassOf(TestCase::class)
                ) {
                    $suite->addTestSuite($className);
                }
            }
        }

        return $suite;
    }
}
