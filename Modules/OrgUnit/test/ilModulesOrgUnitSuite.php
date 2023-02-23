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

/** @noRector */
require_once 'libs/composer/vendor/autoload.php';

class ilModulesOrgUnitSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();
        /** @noRector */
        require_once("./Modules/OrgUnit/test/ilModulesOrgUnitTest.php");
        $suite->addTestSuite("ilModulesOrgUnitTest");
        /** @noRector */
        require_once("./Modules/OrgUnit/test/ilOrgUnitOperationContextRegisteredObjectiveTest.php");
        $suite->addTestSuite("ilOrgUnitOperationContextRegisteredObjectiveTest");
        /** @noRector */
        require_once("./Modules/OrgUnit/test/ilOrgUnitOperationRegisteredObjectiveTest.php");
        $suite->addTestSuite("ilOrgUnitOperationRegisteredObjectiveTest");

        return $suite;
    }
}
