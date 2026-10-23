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
    private const string HEADER = "<!-- language file header -->\n<!-- language file start -->\n";

    private string $directory;
    private ?string $shipped_directory = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        $this->directory = sys_get_temp_dir() . '/ilias_check_all_' . bin2hex(random_bytes(4));
        mkdir($this->directory, 0775, true);
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->directory);
        if ($this->shipped_directory !== null) {
            MigratedPoFixture::removeShippedDirectory($this->shipped_directory);
        }

        parent::tearDown();
    }

    private function check(string $content, string $file_name = 'ilias_de.lang'): string
    {
        file_put_contents($this->directory . '/' . $file_name, $content);
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);
        $folder = (new ReflectionClass(ilObjLanguageFolder::class))->newInstanceWithoutConstructor();
        $folder->lang_path = $this->directory;
        $folder->separator = '#:#';
        (new ReflectionProperty(ilObject::class, 'lng'))->setValue($folder, $lng);

        return $folder->checkAllLanguages();
    }

    /** Every module is shipped in PO files: a file of only the header is valid. */
    public function testAFileOfOnlyTheHeaderIsValid(): void
    {
        $output = $this->check(self::HEADER);

        $this->assertStringContainsString('file_valid', $output);
        $this->assertStringNotContainsString('err_wrong_header', $output);
    }

    public function testAFileWithoutTheStartMarkerHasAWrongHeader(): void
    {
        $output = $this->check("<!-- language file header -->\ncommon#:#yes#:#Ja\n");

        $this->assertStringContainsString('file_not_valid err_wrong_header', $output);
    }

    public function testAHeaderFollowedByAMalformedLineIsAParameterError(): void
    {
        $output = $this->check(self::HEADER . "common#:#yes\n");

        $this->assertStringContainsString('err_2_param', $output);
        $this->assertStringContainsString('file_not_valid err_count_param', $output);
        $this->assertStringNotContainsString('err_wrong_header', $output);
    }

    public function testReportsTheUnreadableShippedPoOfAFoundLanguageWithItsPathRelativeToTheRoot(): void
    {
        $root = rtrim((string) ILIAS_ABSOLUTE_PATH, '/') . '/';
        $this->shipped_directory = 'components/ILIAS/Language/tests/tmp-check-all-' . bin2hex(random_bytes(4));
        MigratedPoFixture::writeShippedPo($this->shipped_directory, 'poll', 'de', MigratedPoFixture::catalog('poll', ['item' => 'Eintrag']));
        file_put_contents($root . $this->shipped_directory . '/tos_de.po', "msgid \"x\"\nmsgstr \"\xff\"\n");
        $this->setGlobalVariable(LanguageFileDirectoryManager::class, new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            MigratedPoFixture::directory('poll', $this->shipped_directory . '/'),
            MigratedPoFixture::directory('tos', $this->shipped_directory . '/')
        ));

        $output = $this->check(self::HEADER);

        $this->assertStringContainsString('<br/>file_valid', $output);
        $this->assertStringContainsString('file_not_valid: ' . $this->shipped_directory . '/tos_de.po', $output);
        $this->assertStringNotContainsString('poll_de.po', $output);
        $this->assertStringNotContainsString($root, $output);
    }

    public function testWithoutAFileDirectoryManagerNoPoIsCheckedAndNothingFails(): void
    {
        global $DIC;
        unset($DIC[LanguageFileDirectoryManager::class]);

        $output = $this->check(self::HEADER);

        $this->assertStringContainsString('<br/>file_valid', $output);
        $this->assertStringNotContainsString('.po', $output);
    }

    public function testHtmlInTheFileNameAndTheLinesIsEscapedAndAnInvalidLanguageKeyKeepsTheWorkingDirectory(): void
    {
        $cwd = getcwd();

        $output = $this->check(self::HEADER . "<i>m#:#<u>id#:#<s>v#:#extra\n<i>m#:#<u>id#:#<s>v\n", 'ilias_<b.lang');

        $this->assertSame($cwd, getcwd());
        $this->assertStringContainsString('langfile_found: ilias_&lt;b.lang', $output);
        $this->assertStringContainsString('module: &lt;i&gt;m, identifier: &lt;u&gt;id, value: &lt;s&gt;v', $output);
        $this->assertStringNotContainsString('<', str_replace(['<br/>', '<b/>', '</b>', '<b>'], '', $output));
    }
}
