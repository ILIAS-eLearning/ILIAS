<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use PHPUnit\Framework\TestSuite;

require_once 'libs/composer/vendor/autoload.php';

class ilServicesFileSystemSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();

        require_once("./Services/FileSystem/test/ilServicesFileSystemTest.php");
        $suite->addTestSuite("ilServicesFileSystemTest");

        return $suite;
    }
}
