<?php declare(strict_types=1);

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

/**
 * Class ilModulesScorm2004Suite
 * @author Uwe Kohnle <support@internetlehrer-gmbh.de>
 */
class ilModulesScorm2004Suite extends TestSuite
{
    /**
     * @throws ReflectionException
     */
    public static function suite() : self
    {
        if (!defined("ILIAS_HTTP_PATH")) {
            define("ILIAS_HTTP_PATH", "http://localhost");
        }

        if (!defined("DEBUG")) {
            define("DEBUG", false);
        }

        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", false);
        }

        if (!defined("ROOT_FOLDER_ID")) {
            define("ROOT_FOLDER_ID", 1);
        }

        if (!defined("IL_INST_ID")) {
            define("IL_INST_ID", 0);
        }
        if (!defined("CLIENT_DATA_DIR")) {
            define("CLIENT_DATA_DIR", "/tmp");
        }

        if (!defined("CLIENT_ID")) {
            define("CLIENT_ID", 1);
        }

        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
//            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
//            ilUnitUtil::performInitialisation();
        } else {
            chdir(__DIR__);
            chdir('../../../');
        }

        $suite = new self();

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
