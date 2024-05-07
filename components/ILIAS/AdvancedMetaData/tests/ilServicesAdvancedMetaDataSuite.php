<?php

use PHPUnit\Framework\TestSuite as TestSuite;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for advanced meta data
 */
class ilServicesAdvancedMetaDataSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();

        // Load namespaced tests:
        $dir = new RecursiveDirectoryIterator(__DIR__);
        $iterator = new RecursiveIteratorIterator($dir);
        $test_files = new RegexIterator($iterator, '/Test\.php$/');

        foreach ($test_files as $test_file) {
            /** @var SplFileInfo $test_file */
            require_once $test_file->getPathname();

            $class_name = preg_replace('/.*tests\/(.*?)(\.php)/', '$1', $test_file->getPathname());
            $class_name = str_replace('/', '\\', $class_name);
            $class_name = '\\ILIAS\\AdvancedMetaData\\' . $class_name;

            if (class_exists($class_name)) {
                $reflection = new ReflectionClass($class_name);
                if (
                    !$reflection->isAbstract() &&
                    !$reflection->isInterface() &&
                    $reflection->isSubclassOf(TestCase::class)
                ) {
                    $suite->addTestSuite($class_name);
                }
            }
        }

        // Load non-namespaced tests:
        include_once './components/ILIAS/AdvancedMetaData/tests/record/ilAdvancedMDRecordObjectOrderingsTest.php';
        $suite->addTestSuite(ilAdvancedMDRecordObjectOrderingsTest::class);

        return $suite;
    }
}
