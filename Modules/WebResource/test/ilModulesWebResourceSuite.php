<?php declare(strict_types=1);

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

class ilModulesWebResourceSuite extends TestSuite
{
    public static function suite() : self
    {
        require_once("./Modules/WebResource/test/ilWebResourceParameterTest.php");
        require_once("./Modules/WebResource/test/ilWebResourceItemsContainerTest.php");
        require_once("./Modules/WebResource/test/ilWebResourceDatabaseRepositoryTest.php");
        require_once("./Modules/WebResource/test/ilWebResourceItemTest.php");
        require_once("./Modules/WebResource/test/ilWebResourceItemInternalTest.php");
        require_once("./Modules/WebResource/test/ilWebResourceItemExternalTest.php");

        $suite = new self();
        $suite->addTestSuite(ilWebResourceParameterTest::class);
        $suite->addTestSuite(ilWebResourceItemsContainerTest::class);
        $suite->addTestSuite(ilWebResourceDatabaseRepositoryTest::class);
        $suite->addTestSuite(ilWebResourceItemTest::class);
        $suite->addTestSuite(ilWebResourceItemInternalTest::class);
        $suite->addTestSuite(ilWebResourceItemExternalTest::class);
        return $suite;
    }
}
