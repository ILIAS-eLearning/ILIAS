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
 * Class ilCtrlStructureSuite
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureSuite extends TestSuite
{
    /**
     * @return static
     */
    public static function suite(): self
    {
        $suite = new self();

        require_once __DIR__ . '/ilCtrlStructureCidGeneratorTest.php';
        $suite->addTestSuite(ilCtrlStructureCidGeneratorTest::class);

        require_once __DIR__ . '/ilCtrlStructureHelperTest.php';
        $suite->addTestSuite(ilCtrlStructureHelperTest::class);

        require_once __DIR__ . '/ilCtrlStructureMapperTest.php';
        $suite->addTestSuite(ilCtrlStructureMapperTest::class);

        require_once __DIR__ . '/ilCtrlStructureReaderTest.php';
        $suite->addTestSuite(ilCtrlStructureReaderTest::class);

        require_once __DIR__ . '/ilCtrlStructureTest.php';
        $suite->addTestSuite(ilCtrlStructureTest::class);

        return $suite;
    }
}
