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

require_once __DIR__ . '/bootstrap.php';

use PHPUnit\Framework\TestSuite;

/**
 * @author  <killing@leifos.de>
 */
class ilServicesTasksSuite extends TestSuite
{
    public static function suite(): ilServicesTasksSuite
    {
        //PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

        $suite = new self();

        include_once("./Services/Tasks/test/ilDerivedTaskTest.php");
        $suite->addTestSuite("ilDerivedTaskTest");

        include_once("./Services/Tasks/test/ilDerivedTaskFactoryTest.php");
        $suite->addTestSuite("ilDerivedTaskFactoryTest");

        include_once("./Services/Tasks/test/ilDerivedTaskCollectorTest.php");
        $suite->addTestSuite("ilDerivedTaskCollectorTest");

        return $suite;
    }
}
