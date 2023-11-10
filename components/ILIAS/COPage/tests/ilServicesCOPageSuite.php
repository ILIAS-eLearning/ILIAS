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

require_once 'vendor/composer/vendor/autoload.php';

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilServicesCOPageSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();

        require_once("./components/ILIAS/COPage/tests/COPageTestBase.php");

        require_once("./components/ILIAS/COPage/tests/EditorEditSessionRepositoryTest.php");
        $suite->addTestSuite("EditorEditSessionRepositoryTest");

        require_once("./components/ILIAS/COPage/tests/PCMapEditorSessionRepositoryTest.php");
        $suite->addTestSuite("PCMapEditorSessionRepositoryTest");

        require_once("./components/ILIAS/COPage/tests/PCParagraphTest.php");
        $suite->addTestSuite("PCParagraphTest");

        require_once("./components/ILIAS/COPage/tests/PCSectionTest.php");
        $suite->addTestSuite("PCSectionTest");

        require_once("./components/ILIAS/COPage/tests/class.ilUnitTestPageConfig.php");
        require_once("./components/ILIAS/COPage/tests/PageObjectTest.php");
        require_once("./components/ILIAS/COPage/tests/class.ilUnitTestPageObject.php");
        require_once("./components/ILIAS/COPage/tests/class.ilUnitTestPageManager.php");
        require_once("./components/ILIAS/COPage/tests/class.ilUnitTestPCDefinition.php");
        $suite->addTestSuite("PageObjectTest");

        require_once("./components/ILIAS/COPage/tests/PCBlogTest.php");
        $suite->addTestSuite("PCBlogTest");

        require_once("./components/ILIAS/COPage/tests/PCContentIncludeTest.php");
        $suite->addTestSuite("PCContentIncludeTest");

        require_once("./components/ILIAS/COPage/tests/PCDataTableTest.php");
        $suite->addTestSuite("PCDataTableTest");

        require_once("./components/ILIAS/COPage/tests/PCTableDataTest.php");
        $suite->addTestSuite("PCTableDataTest");

        require_once("./components/ILIAS/COPage/tests/PCContentTemplateTest.php");
        $suite->addTestSuite("PCContentTemplateTest");

        require_once("./components/ILIAS/COPage/tests/PCFileListTest.php");
        $suite->addTestSuite("PCFileListTest");

        require_once("./components/ILIAS/COPage/tests/PCGridTest.php");
        $suite->addTestSuite("PCGridTest");

        require_once("./components/ILIAS/COPage/tests/PCInteractiveImageTest.php");
        $suite->addTestSuite("PCInteractiveImageTest");

        require_once("./components/ILIAS/COPage/tests/PCListTest.php");
        $suite->addTestSuite("PCListTest");

        require_once("./components/ILIAS/COPage/tests/PCLoginPageElementTest.php");
        $suite->addTestSuite("PCLoginPageElementTest");

        require_once("./components/ILIAS/COPage/tests/PCMapTest.php");
        $suite->addTestSuite("PCMapTest");

        require_once("./components/ILIAS/COPage/tests/PCMediaObjectTest.php");
        $suite->addTestSuite("PCMediaObjectTest");

        require_once("./components/ILIAS/COPage/tests/PCPlaceholderTest.php");
        $suite->addTestSuite("PCPlaceHolderTest");

        require_once("./components/ILIAS/COPage/tests/PCPluggedTest.php");
        $suite->addTestSuite("PCPluggedTest");

        require_once("./components/ILIAS/COPage/tests/PCProfileTest.php");
        $suite->addTestSuite("PCProfileTest");

        require_once("./components/ILIAS/COPage/tests/PCQuestionTest.php");
        $suite->addTestSuite("PCQuestionTest");

        require_once("./components/ILIAS/COPage/tests/PCResourcesTest.php");
        $suite->addTestSuite("PCResourcesTest");

        require_once("./components/ILIAS/COPage/tests/PCSkillsTest.php");
        $suite->addTestSuite("PCSkillsTest");

        require_once("./components/ILIAS/COPage/tests/PCSourceCodeTest.php");
        $suite->addTestSuite("PCSourceCodeTest");

        require_once("./components/ILIAS/COPage/tests/PCTabsTest.php");
        $suite->addTestSuite("PCTabsTest");

        require_once("./components/ILIAS/COPage/tests/PCVerificationTest.php");
        $suite->addTestSuite("PCVerificationTest");

        require_once("./components/ILIAS/COPage/tests/ID/ContentIdManagerTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\ID\ContentIdManagerTest::class);

        require_once("./components/ILIAS/COPage/tests/Compare/PageCompareTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\Compare\PageCompareTest::class);

        require_once("./components/ILIAS/COPage/tests/Page/PageContentManagerTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\Page\PageContentManagerTest::class);

        require_once("./components/ILIAS/COPage/tests/PC/PCFactoryTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\PC\PCFactoryTest::class);

        require_once("./components/ILIAS/COPage/tests/PC/PCDefinitionTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\PC\PCDefinitionTest::class);

        require_once("./components/ILIAS/COPage/tests/Link/LinkManagerTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\Link\LinkManagerTest::class);

        require_once("./components/ILIAS/COPage/tests/PC/FileList/FileListManagerTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\PC\FileList\FileListManagerTest::class);

        require_once("./components/ILIAS/COPage/tests/Layout/PageLayoutTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\Layout\PageLayoutTest::class);

        require_once("./components/ILIAS/COPage/tests/Html/TransformUtilTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\Html\TransformUtilTest::class);

        return $suite;
    }
}
