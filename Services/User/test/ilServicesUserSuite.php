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

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestCase;

class ilServicesUserSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new ilServicesUserSuite();

        foreach (new RegExIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            ),
            '/BaseTest\.php$/'
        ) as $file) {
            /** @var SplFileInfo $file */
            require_once $file->getPathname();
        }

        foreach (new RegExIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            ),
            '/(?<!Base)Test\.php$/'
        ) as $file) {
            /** @var SplFileInfo $file */
            require_once $file->getPathname();

            $className = preg_replace('/(.*?)(\.php)/', '$1', $file->getBasename());
            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                if (
                    !$reflection->isAbstract() &&
                    !$reflection->isInterface() &&
                    $reflection->isSubclassOf(TestCase::class)) {
                    $suite->addTestSuite($className);
                }
            }
        }
        return $suite;
    }
}
