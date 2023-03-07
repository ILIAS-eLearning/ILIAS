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
use ILIAS\Tests\Services\Database\Integrity\Suite as IntegritySuite;

/**
 * Database Test-Suite
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilServicesDatabaseSuite extends TestSuite
{
    /**
     * @throws ReflectionException
     */
    public static function suite(): \ilServicesDatabaseSuite
    {
        $suite = new self();
        /** @noRector */
        require_once('./Services/Database/test/Setup/ilDatabaseSetupSuite.php');
        $suite->addTestSuite(ilDatabaseSetupSuite::suite());

        require_once('./Services/Database/test/Integrity/Suite.php');
        $suite->addTestSuite(IntegritySuite::suite());

        return $suite;
    }
}
