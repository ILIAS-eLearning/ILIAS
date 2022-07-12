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

class ilServicesStyleSystemSuite extends TestSuite
{
    public static function suite() : TestSuite
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
