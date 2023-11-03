<?php

declare(strict_types=1);

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

class ilServicesAccessControlSuite extends TestSuite
{
    public static function suite(): TestSuite
    {
        $suite = new ilServicesAccessControlSuite();
        /** @noRector */
        include_once("./Services/AccessControl/test/ilRBACTest.php");
        $suite->addTestSuite(ilRBACTest::class);
        return $suite;
    }
}
