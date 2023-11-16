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
use PHPUnit\Runner\Filter\Factory as FilterFactory;
use PHPUnit\Runner\Filter\ExcludeGroupFilterIterator as GroupExcludeFilter;

class ILIASSuite extends TestSuite
{
    /**
     * @var	string
     */
    public const PHP_UNIT_PARENT_CLASS = TestCase::class;

    public const COMPONENTS_DIRECTORY = __DIR__ . "/../../components";
    public const TEST_DIRECTORY = "tests";
    public const REGEX_SUITE_FILENAME = "#^[^.]+Suite\.php$#";
    public const REGEX_TEST_FILENAME = "#^[^.]+Test\.php$#";

    public static function suite()
    {
        $ilias_suite = new ILIASSuite();
        echo "ILIAS PHPUnit-Tests need installed dev-requirements, please install 'composer install' in root directory if you didn't do so already...\n";
        echo "\n";

        foreach (self::getComponents() as $component) {
            [$path, $suites] = self::getTestsOf((string) $component);
            if (is_null($path)) {
                echo "No tests for component:   " . realpath((string) $component) . "\n";
                continue;
            }
            if (empty($suites)) {
                echo "Adding tests from folder: " . $path . "\n";
                $ilias_suite->addTestFiles(self::getTestFilesIn($path));
                continue;
            }
            foreach ($suites as [$suite, $path]) {
                require_once($path);
                $suite = $suite::suite();
                echo "Adding tests from suite:  " . $path . "\n";
                $ilias_suite->addTest($suite);
            }
        }

        echo "\n";

        return $ilias_suite;
    }

    public static function getComponents(): array
    {
        $vendors = new DirectoryIterator(self::COMPONENTS_DIRECTORY);
        $dirs = [];
        foreach ($vendors as $vendor) {
            if (str_starts_with((string) $vendor, ".")) {
                continue;
            }
            $components = new DirectoryIterator(self::COMPONENTS_DIRECTORY . "/" . $vendor);
            foreach ($components as $component) {
                if (str_starts_with((string) $component, ".")) {
                    continue;
                }
                $dirs[] = $component->getPathname();
            }
        }
        sort($dirs);
        return $dirs;
    }

    public static function getTestsOf(string $component): array
    {
        $test_dir = $component . "/" . self::TEST_DIRECTORY;
        if (!is_dir($test_dir)) {
            return [null, []];
        }
        $tests = new DirectoryIterator($test_dir);
        $suites = [];
        foreach ($tests as $test) {
            if (preg_match(self::REGEX_SUITE_FILENAME, (string) $test)) {
                $suites[] = [$test->getBaseName(".php"), realpath($test->getPathname())];
            }
        }
        return [realpath($test_dir), $suites];
    }

    public static function getTestFilesIn(string $path): array
    {
        $tests = [];
        $ds = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($ds as $d) {
            if (!$d->isFile()) {
                continue;
            }
            if (preg_match(self::REGEX_TEST_FILENAME, (string) $d)) {
                $tests[] = realpath($d->getPathname());
            }
        }
        return $tests;
    }
}
