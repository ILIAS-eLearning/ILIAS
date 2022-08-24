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

require_once __DIR__ . "/ilTestBaseTestCase.php";

class ilModulesTestSuite extends TestSuite
{
    public static function suite(): ilModulesTestSuite
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

        chdir(dirname(__FILE__));
        chdir('../../../');


        $suite = new ilModulesTestSuite();

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
            if (!self::addClass($suite, $className)) {
                $results = [];
                if (preg_match('/^namespace ([[:alnum:]_\\\\]+);$/m', file_get_contents($file->getPathname()), $results)) {
                    self::addClass($suite, $results[1] . '\\' . $className);
                }
            }
        }

        return $suite;
    }

    private static function addClass(self $suite, string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }
        $reflection = new ReflectionClass($className);
        if (
            !$reflection->isAbstract() &&
            !$reflection->isInterface() &&
            $reflection->isSubclassOf(TestCase::class)) {
            $suite->addTestSuite($className);
        }

        return true;
    }
}
