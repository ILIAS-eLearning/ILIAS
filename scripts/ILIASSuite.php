<?php

/**
 * This is the global ILIAS test suite. It searches automatically for
 * components test suites by scanning all Components/.../tests directories for test suite files.
 *
 * Test suite files are identified automatically, if they are named
 * "ilServices[ServiceName]Suite.php" or ilModules[ModuleName]Suite.php".
 *
 * @author	<alex.killing@gmx.de>
 */

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
    public const REGEX_TEST_FILENAME = "#[a-zA-Z]+Test\.php#";
    public const PHP_UNIT_PARENT_CLASS = TestCase::class;

    public const COMPONENTS = [
        "App",
        "BackgroundTasks",
        "Cache",
        "DI",
        "Data",
        "FileUpload",
        "Filesystem",
        "GlobalCache",
        "GlobalScreen",
        "HTTP",
        "KioskMode",
        "Language",
        "Refinery",
        "ResourceStorage",
        "Setup",
        "Types",
        "UI",
        "VirusScanner"
    ];

    public static function suite()
    {
        $suite = new ILIASSuite();
        echo "ILIAS PHPUnit-Tests need installed dev-requirements, please install using 'composer install' in ./vendor/composer \n";
        echo "\n";

        // scan Modules and Services directories
        $basedirs = array("components/ILIAS");
        foreach ($basedirs as $basedir) {
            // read current directory
            $dir = opendir($basedir);

            while ($file = readdir($dir)) {
                if (!in_array($file, self::COMPONENTS, true)) {
                    if ($file != "." && $file != ".." && is_dir($basedir . "/" . $file)) {
                        $file_name = str_replace("_", "", $file);
                        $suite_path =
                            $basedir . "/" . $file . "/tests/ilComponents" . $file_name . "Suite.php";
                        if (is_file($suite_path)) {
                            include_once($suite_path);
                            $file_name = str_replace("_", "", $file);
                            $name = "ilComponents" . $file_name . "Suite";
                            $s = $name::suite();
                            echo "Adding Suite: " . $name . "\n";
                            $suite->addTest($s);
                            //$suite->addTestSuite("ilSettingTest");
                        }
                    }
                }
            }
        }

        $suite = self::addTestFolderToSuite($suite);

        echo "\n";

        return $suite;
    }

    /**
     * Find and add all testSuits beneath ILIAS_ROOL/test - folder
     */
    protected static function addTestFolderToSuite(ILIASSuite $suite): ILIASSuite
    {
        $test_directories = [];
        foreach (self::COMPONENTS as $component) {
            $test_directories[] = "components/ILIAS/" . $component . "/tests";
        }

        while ($aux_dir = current($test_directories)) {
            if ($handle = opendir($aux_dir)) {
                $aux_dir .= DIRECTORY_SEPARATOR;
                while (false !== ($entry = readdir($handle))) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }
                    if (is_dir($aux_dir . $entry)) {
                        $test_directories[] = $aux_dir . $entry;
                    } else {
                        if (1 === preg_match(self::REGEX_TEST_FILENAME, $entry)) {
                            $ref_declared_classes = get_declared_classes();
                            require_once $aux_dir . "/" . $entry;
                            $new_declared_classes = array_diff(get_declared_classes(), $ref_declared_classes);
                            foreach ($new_declared_classes as $entry_class) {
                                $reflection = new ReflectionClass($entry_class);
                                if (!$reflection->isAbstract() && $reflection->isSubclassOf(self::PHP_UNIT_PARENT_CLASS)) {
                                    echo "Adding Test-Suite: " . $entry_class . "\n";
                                    $suite->addTestSuite($entry_class);
                                }
                            }
                        }
                    }
                }
            }
            next($test_directories);
        }
        return $suite;
    }
}
