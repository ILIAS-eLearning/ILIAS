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

/**
 * Class ilCtrlPathSuite
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlPathSuite extends TestSuite
{
    /**
     * @return self
     */
    public static function suite(): self
    {
        $suite = new self();

        require_once __DIR__ . '/ilCtrlAbstractPathTest.php';
        $suite->addTestSuite(ilCtrlAbstractPathTest::class);

        require_once __DIR__ . '/ilCtrlArrayClassPathTest.php';
        $suite->addTestSuite(ilCtrlArrayClassPathTest::class);

        require_once __DIR__ . '/ilCtrlExistingPathTest.php';
        $suite->addTestSuite(ilCtrlExistingPathTest::class);

        require_once __DIR__ . '/ilCtrlPathFactoryTest.php';
        $suite->addTestSuite(ilCtrlPathFactoryTest::class);

        require_once __DIR__ . '/ilCtrlSingleClassPathTest.php';
        $suite->addTestSuite(ilCtrlSingleClassPathTest::class);

        return $suite;
    }
}
