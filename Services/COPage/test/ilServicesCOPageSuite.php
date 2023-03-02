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

require_once 'libs/composer/vendor/autoload.php';

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilServicesCOPageSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();

        require_once("./Services/COPage/test/COPageTestBase.php");

        require_once("./Services/COPage/test/EditorEditSessionRepositoryTest.php");
        $suite->addTestSuite("EditorEditSessionRepositoryTest");

        require_once("./Services/COPage/test/PCMapEditorSessionRepositoryTest.php");
        $suite->addTestSuite("PCMapEditorSessionRepositoryTest");

        require_once("./Services/COPage/test/PCParagraphTest.php");
        $suite->addTestSuite("PCParagraphTest");

        require_once("./Services/COPage/test/PCSectionTest.php");
        $suite->addTestSuite("PCSectionTest");

        require_once("./include/inc.xml5compliance.php");
        require_once("./include/inc.xsl5compliance.php");
        require_once("./Services/COPage/test/class.ilUnitTestPageConfig.php");
        require_once("./Services/COPage/test/PageObjectTest.php");
        require_once("./Services/COPage/test/class.ilUnitTestPageObject.php");
        $suite->addTestSuite("PageObjectTest");

        require_once("./Services/COPage/test/PCBlogTest.php");
        $suite->addTestSuite("PCBlogTest");

        require_once("./Services/COPage/test/PCContentIncludeTest.php");
        $suite->addTestSuite("PCContentIncludeTest");

        require_once("./Services/COPage/test/PCDataTableTest.php");
        $suite->addTestSuite("PCDataTableTest");

        return $suite;
    }
}
