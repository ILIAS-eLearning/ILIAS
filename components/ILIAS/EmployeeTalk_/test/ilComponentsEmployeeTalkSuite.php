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

/** @noRector */
require_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/vendor/composer/vendor/autoload.php';

class ilComponentsEmployeeTalkSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();
        /** @noRector */
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/EmployeeTalk_/test/ilModulesEmployeeTalkVEventTest.php");
        $suite->addTestSuite("ilModulesEmployeeTalkVEventTest");

        return $suite;
    }
}
