<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/bootstrap.php';

/**
 * Class ilServicesMailSuite
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilServicesMailSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * @return self
     * @throws \ReflectionException
     */
    public static function suite()
    {
        $suite = new self();

        foreach (new \RegExIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(__DIR__, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            ),
            '/BaseTest\.php$/'
        ) as $file) {
            /** @var \SplFileInfo $file */
            require_once $file->getPathname();
        }

        foreach (new \RegExIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(__DIR__, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            ),
            '/(?<!Base)Test\.php$/'
        ) as $file) {
            /** @var \SplFileInfo $file */
            require_once $file->getPathname();

            $className = preg_replace('/(.*?)(\.php)/', '$1', $file->getBasename());
            if (class_exists($className)) {
                $reflection = new \ReflectionClass($className);
                if (
                    !$reflection->isAbstract() &&
                    !$reflection->isInterface() &&
                    $reflection->isSubclassOf(\PHPUnit_Framework_TestCase::class)) {
                    $suite->addTestSuite($className);
                }
            }
        }

        return $suite;
    }
}
