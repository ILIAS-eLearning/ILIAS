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

        require_once("./components/ILIAS/COPage/test/COPageTestBase.php");

        require_once("./components/ILIAS/COPage/test/EditorEditSessionRepositoryTest.php");
        $suite->addTestSuite("EditorEditSessionRepositoryTest");

        require_once("./components/ILIAS/COPage/test/PCMapEditorSessionRepositoryTest.php");
        $suite->addTestSuite("PCMapEditorSessionRepositoryTest");

        require_once("./components/ILIAS/COPage/test/PCParagraphTest.php");
        $suite->addTestSuite("PCParagraphTest");

        require_once("./components/ILIAS/COPage/test/PCSectionTest.php");
        $suite->addTestSuite("PCSectionTest");

        require_once("./components/ILIAS/COPage/test/class.ilUnitTestPageConfig.php");
        require_once("./components/ILIAS/COPage/test/PageObjectTest.php");
        require_once("./components/ILIAS/COPage/test/class.ilUnitTestPageObject.php");
        require_once("./components/ILIAS/COPage/test/class.ilUnitTestPageManager.php");
        require_once("./components/ILIAS/COPage/test/class.ilUnitTestPCDefinition.php");
        $suite->addTestSuite("PageObjectTest");

        require_once("./components/ILIAS/COPage/test/PCBlogTest.php");
        $suite->addTestSuite("PCBlogTest");

        require_once("./components/ILIAS/COPage/test/PCContentIncludeTest.php");
        $suite->addTestSuite("PCContentIncludeTest");

        require_once("./components/ILIAS/COPage/test/PCDataTableTest.php");
        $suite->addTestSuite("PCDataTableTest");

        require_once("./components/ILIAS/COPage/test/PCTableDataTest.php");
        $suite->addTestSuite("PCTableDataTest");

        require_once("./components/ILIAS/COPage/test/PCContentTemplateTest.php");
        $suite->addTestSuite("PCContentTemplateTest");

        require_once("./components/ILIAS/COPage/test/PCFileListTest.php");
        $suite->addTestSuite("PCFileListTest");

        require_once("./components/ILIAS/COPage/test/PCGridTest.php");
        $suite->addTestSuite("PCGridTest");

        require_once("./components/ILIAS/COPage/test/PCInteractiveImageTest.php");
        $suite->addTestSuite("PCInteractiveImageTest");

        require_once("./components/ILIAS/COPage/test/PCListTest.php");
        $suite->addTestSuite("PCListTest");

        require_once("./components/ILIAS/COPage/test/PCLoginPageElementTest.php");
        $suite->addTestSuite("PCLoginPageElementTest");

        require_once("./components/ILIAS/COPage/test/PCMapTest.php");
        $suite->addTestSuite("PCMapTest");

        require_once("./components/ILIAS/COPage/test/PCMediaObjectTest.php");
        $suite->addTestSuite("PCMediaObjectTest");

        require_once("./components/ILIAS/COPage/test/PCPlaceholderTest.php");
        $suite->addTestSuite("PCPlaceHolderTest");

        require_once("./components/ILIAS/COPage/test/PCPluggedTest.php");
        $suite->addTestSuite("PCPluggedTest");

        require_once("./components/ILIAS/COPage/test/PCProfileTest.php");
        $suite->addTestSuite("PCProfileTest");

        require_once("./components/ILIAS/COPage/test/PCQuestionTest.php");
        $suite->addTestSuite("PCQuestionTest");

        require_once("./components/ILIAS/COPage/test/PCResourcesTest.php");
        $suite->addTestSuite("PCResourcesTest");

        require_once("./components/ILIAS/COPage/test/PCSkillsTest.php");
        $suite->addTestSuite("PCSkillsTest");

        require_once("./components/ILIAS/COPage/test/PCSourceCodeTest.php");
        $suite->addTestSuite("PCSourceCodeTest");

        require_once("./components/ILIAS/COPage/test/PCTabsTest.php");
        $suite->addTestSuite("PCTabsTest");

        require_once("./components/ILIAS/COPage/test/PCVerificationTest.php");
        $suite->addTestSuite("PCVerificationTest");

        require_once("./components/ILIAS/COPage/test/ID/ContentIdManagerTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\ID\ContentIdManagerTest::class);

        require_once("./components/ILIAS/COPage/test/Compare/PageCompareTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\Compare\PageCompareTest::class);

        require_once("./components/ILIAS/COPage/test/Page/PageContentManagerTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\Page\PageContentManagerTest::class);

        require_once("./components/ILIAS/COPage/test/PC/PCFactoryTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\PC\PCFactoryTest::class);

        require_once("./components/ILIAS/COPage/test/PC/PCDefinitionTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\PC\PCDefinitionTest::class);

        require_once("./components/ILIAS/COPage/test/Link/LinkManagerTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\Link\LinkManagerTest::class);

        require_once("./components/ILIAS/COPage/test/PC/FileList/FileListManagerTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\PC\FileList\FileListManagerTest::class);

        require_once("./components/ILIAS/COPage/test/Layout/PageLayoutTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\Layout\PageLayoutTest::class);

        require_once("./components/ILIAS/COPage/test/Html/TransformUtilTest.php");
        $suite->addTestSuite(\ILIAS\COPage\Test\Html\TransformUtilTest::class);

        return $suite;
    }
}
