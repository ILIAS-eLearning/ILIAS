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

use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;

class ilObjLanguageFolderCheckAllLanguagesTest extends ilLanguageBaseTestCase
{
    private string $fixture_directory;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        $this->fixture_directory = 'components/ILIAS/Language/tests/tmp-check-all-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeShippedDirectory($this->fixture_directory);

        parent::tearDown();
    }

    public function testReportsTheUnreadableShippedPoOfAFoundLanguageWithItsPathRelativeToTheRoot(): void
    {
        $root = rtrim((string) ILIAS_ABSOLUTE_PATH, '/') . '/';
        MigratedPoFixture::writeShippedPo($this->fixture_directory . '/lang', 'poll', 'de', MigratedPoFixture::catalog('poll', ['item' => 'Eintrag']));
        file_put_contents($root . $this->fixture_directory . '/lang/tos_de.po', "msgid \"x\"\nmsgstr \"\xff\"\n");
        file_put_contents($root . $this->fixture_directory . '/ilias_de.lang', "<!-- language file start -->\ncommon#:#login#:#Anmelden\n");
        $this->setGlobalVariable(LanguageFileDirectoryManager::class, new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            MigratedPoFixture::directory('poll', $this->fixture_directory . '/lang/'),
            MigratedPoFixture::directory('tos', $this->fixture_directory . '/lang/')
        ));

        $output = $this->folder($root . $this->fixture_directory)->checkAllLanguages();

        $this->assertStringContainsString('<br/>file_valid', $output);
        $this->assertStringContainsString('file_not_valid: ' . $this->fixture_directory . '/lang/tos_de.po', $output);
        $this->assertStringNotContainsString('poll_de.po', $output);
        $this->assertStringNotContainsString($root, $output);
    }

    public function testWithoutAFileDirectoryManagerNoPoIsCheckedAndNothingFails(): void
    {
        global $DIC;
        unset($DIC[LanguageFileDirectoryManager::class]);
        $root = rtrim((string) ILIAS_ABSOLUTE_PATH, '/') . '/';
        mkdir($root . $this->fixture_directory, 0777, true);
        file_put_contents($root . $this->fixture_directory . '/ilias_de.lang', "<!-- language file start -->\ncommon#:#login#:#Anmelden\n");

        $output = $this->folder($root . $this->fixture_directory)->checkAllLanguages();

        $this->assertStringContainsString('<br/>file_valid', $output);
        $this->assertStringNotContainsString('.po', $output);
    }

    public function testHtmlInTheFileNameAndTheLinesIsEscapedAndAnInvalidLanguageKeyKeepsTheWorkingDirectory(): void
    {
        $root = rtrim((string) ILIAS_ABSOLUTE_PATH, '/') . '/';
        mkdir($root . $this->fixture_directory, 0777, true);
        file_put_contents($root . $this->fixture_directory . '/ilias_<b.lang', "<!-- language file start -->\n<i>m#:#<u>id#:#<s>v#:#extra\n<i>m#:#<u>id#:#<s>v\n");
        $cwd = getcwd();

        $output = $this->folder($root . $this->fixture_directory)->checkAllLanguages();

        $this->assertSame($cwd, getcwd());
        $this->assertStringContainsString('langfile_found: ilias_&lt;b.lang', $output);
        $this->assertStringContainsString('module: &lt;i&gt;m, identifier: &lt;u&gt;id, value: &lt;s&gt;v', $output);
        $this->assertStringNotContainsString('<', str_replace(['<br/>', '<b/>', '</b>', '<b>'], '', $output));
    }

    private function folder(string $lang_path): ilObjLanguageFolder
    {
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);
        $folder = (new ReflectionClass(ilObjLanguageFolder::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(ilObject::class, 'lng'))->setValue($folder, $lng);
        $folder->lang_path = $lang_path;
        $folder->separator = '#:#';

        return $folder;
    }
}
