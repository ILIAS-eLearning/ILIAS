<?php

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilServicesStyleSystemSuite extends TestSuite
{
    public static function suite(): TestSuite
    {
        $suite = new ilServicesStyleSystemSuite();

        $base_dir = './Services/Style/System/test/';
        $rec_it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));

        foreach ($rec_it as $file) {
            if (strpos($file->getFilename(), 'Test.php') !== false) {
                include_once($file->getPathname());
                $test_class = str_replace('.php', '', $file->getFilename());
                $suite->addTestSuite($test_class);
            }
        }
        return $suite;
    }
}
