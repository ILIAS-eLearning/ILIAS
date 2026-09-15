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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

class ilObjLanguageExtGUISaveObjectTest extends TestCase
{
    use RestoresProcessState;

    use ilObjLanguageExtGUITestFixture;

    /** @var array<string, ilLanguageFile>|null */
    private ?array $global_language_files_before = null;

    /** ilLanguageFile and ilCachedLanguage keep static caches: start empty, restore afterwards. */
    #[Before]
    protected function isolateLanguageCaches(): void
    {
        $files = new ReflectionProperty(ilLanguageFile::class, 'global_file_objects');
        $this->global_language_files_before = $files->isInitialized() ? $files->getValue() : null;
        $files->setValue(null, []);
        (new ReflectionProperty(ilCachedLanguage::class, 'instances'))->setValue(null, []);
    }

    #[After]
    protected function restoreLanguageCaches(): void
    {
        $files = new ReflectionProperty(ilLanguageFile::class, 'global_file_objects');
        $files->setValue(null, $this->global_language_files_before ?? []);
        (new ReflectionProperty(ilCachedLanguage::class, 'instances'))->setValue(null, []);
    }

    /**
     * Partial mock with initEditEntriesForm() stubbed so the arguments of a rejected save can be captured.
     *
     * @param array<string, mixed> $parsed_body
     * @return ilObjLanguageExtGUI&MockObject
     */
    private function createGuiForSaveObjectWithParsedBody(
        array $parsed_body,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl
    ): ilObjLanguageExtGUI {
        /** @var ilObjLanguageExtGUI&MockObject $gui */
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['initEditEntriesForm'])
            ->getMock();

        $this->inject($gui, 'http', $this->mockHttpWithParsedBody($parsed_body));
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->inject($gui, 'object', $this->createFakeLanguageObject('de'));
        $this->inject($gui, 'refinery', new \ILIAS\Refinery\Factory(
            $this->createStub(\ILIAS\Data\Factory::class),
            $this->createStub(\ILIAS\Language\Language::class)
        ));

        return $gui;
    }

    private function templateExpectingTheSavedSuccess(): ilGlobalTemplateInterface
    {
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('success', 'language_variables_saved', true);

        return $tpl;
    }

    private function ctrlExpectingTheRedirectToView(): ilCtrl
    {
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with($this->isInstanceOf(ilObjLanguageExtGUI::class), 'view');

        return $ctrl;
    }

    /** Seeds the static cache of ilLanguageFile::_getGlobalLanguageFile() with an empty fake: findInvalidMarkupOfChangedValues() reads the shipped value from it. */
    private function seedEmptyGlobalLanguageFile(string $lang_key): void
    {
        $fake_global_file = (new ReflectionClass(ilLanguageFile::class))->newInstanceWithoutConstructor();
        $fake_global_file->setAllValues([]);
        $fake_global_file->setAllComments([]);
        $cache = new ReflectionProperty(ilLanguageFile::class, 'global_file_objects');
        $cache->setValue(null, ($cache->isInitialized() ? $cache->getValue() : []) + [$lang_key => $fake_global_file]);
    }

    /** Answers every SELECT with nothing and fails on any insert()/manipulate(): a rejected save writes nothing. */
    private function databaseThatMustNeverBeWrittenTo(): ilDBInterface
    {
        $empty_statement = $this->createStub(ilDBStatement::class);
        $empty_statement->method('fetchRow')->willReturn(null);
        $empty_statement->method('fetchAssoc')->willReturn(null);

        $db = $this->createMock(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value, string $type): string => "'" . (string) $value . "'");
        $db->method('query')->willReturn($empty_statement);
        $db->method('fetchAssoc')->willReturn(null);
        $db->expects($this->never())->method('insert');
        $db->expects($this->never())->method('manipulate');

        return $db;
    }

    public function testSaveEditedEntriesRejectsEverythingWhenAnyValueHasDisallowedMarkupAndNamesEveryAffectedKeyEscaped(): void
    {
        $this->seedEmptyGlobalLanguageFile('de');
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['ilDB'] = fn() => $this->databaseThatMustNeverBeWrittenTo();
        $GLOBALS['DIC']['lng'] = fn() => $this->createStub(ilLanguage::class);
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($this->createStub(ilLogger::class));
        $GLOBALS['DIC']['ilLoggerFactory'] = static fn() => $logger_factory;

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with(
            'failure',
            $this->callback(static function (string $message): bool {
                return str_contains($message, 'common#:#yes')
                    && str_contains($message, '&lt;b&gt;')
                    && !str_contains($message, '<b>#:#x')
                    && !str_contains($message, 'alert(1)')
                    && !str_contains($message, 'alert(2)')
                    && str_contains($message, 'common#:#yes: &lt;script&gt;');
            })
        );
        $tpl->expects($this->once())->method('setContent');
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->never())->method('redirect');

        $gui = $this->createGuiForSaveObjectWithParsedBody([
            // the name itself contains HTML-significant characters
            'entry_name' => ['common#:#yes', 'other<b>#:#x'],
            'translation' => ['<script>alert(1)</script>', '<script>alert(2)</script>'],
            'comment' => ['', ''],
            'expected_entry_count' => '2',
        ], $tpl, $ctrl);
        $this->inject($gui, 'object', $this->createFakeLanguageObject('de'));
        // shown again with the input of this save
        $gui->expects($this->once())->method('initEditEntriesForm')->with(
            ['common#:#yes', 'other<b>#:#x'],
            $this->callback(static fn(array $values): bool => ($values['common#:#yes'] ?? null) === '<script>alert(1)</script>'),
            $this->identicalTo(['common#:#yes' => '', 'other<b>#:#x' => ''])
        )->willReturn($this->createStub(ilPropertyFormGUI::class));

        $gui->saveEditedEntriesObject();
    }

    public function testSaveEditedEntriesTransformsLineBreaksAndDoubleLessThanBeforeCheckingThenSaves(): void
    {
        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        $this->seedEmptyGlobalLanguageFile('zz_saveobject_test');

        $empty_row_statement = $this->createStub(ilDBStatement::class);
        $empty_row_statement->method('fetchRow')->willReturn(null);
        $query_f_statement = $this->createStub(ilDBStatement::class);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value, string $type): string => "'" . (string) $value . "'");
        $db->method('query')->willReturn($empty_row_statement);
        $db->method('queryF')->willReturn($query_f_statement);
        $db->method('fetchAssoc')->willReturnCallback(
            static function (ilDBStatement $statement) use ($query_f_statement) {
                return $statement === $query_f_statement ? ['lang_array' => serialize(['x' => 'irrelevant'])] : null;
            }
        );
        $db->method('manipulate')->willReturnCallback(function (string $sql): int {
            if (str_starts_with($sql, /** @lang text */ 'INSERT INTO lng_data')) {
                $this->assertStringContainsString("'<b>x</b><br />'", $sql, 'the value must reach lng_data already transformed');
            }
            return 0;
        });
        $captured_lang_array = null;
        $db->method('insert')->willReturnCallback(
            function (string $table, array $values) use (&$captured_lang_array): int {
                if ($table === 'lng_modules') {
                    $captured_lang_array = unserialize($values['lang_array'][1] ?? '', ['allowed_classes' => false]);
                }
                return 1;
            }
        );

        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['ilDB'] = static fn() => $db;
        $GLOBALS['DIC']['lng'] = fn() => $this->createStub(ilLanguage::class);
        $GLOBALS['DIC']['ilErr'] = static fn() => null;

        $lng = $GLOBALS['DIC']->language();
        $gui = $this->createGuiForSaveObjectWithParsedBody([
            'entry_name' => ['testmodule' . $lng->separator . 'greeting'],
            'translation' => ["<b>x</b>\r\n"],
            'comment' => [''],
            'expected_entry_count' => '1',
        ], $this->templateExpectingTheSavedSuccess(), $this->ctrlExpectingTheRedirectToView());
        $this->inject($gui, 'object', $this->createFakeLanguageObject('zz_saveobject_test'));
        $gui->expects($this->never())->method('initEditEntriesForm');

        $gui->saveEditedEntriesObject();

        $this->assertSame(['greeting' => '<b>x</b><br />'], $captured_lang_array);
    }

    public function testSaveEditedEntriesIgnoresAValueAndACommentSentAsArray(): void
    {
        $this->seedEmptyGlobalLanguageFile('de');
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['ilDB'] = fn() => $this->databaseThatMustNeverBeWrittenTo();
        $GLOBALS['DIC']['lng'] = fn() => $this->createStub(ilLanguage::class);
        $gui = $this->createGuiForSaveObjectWithParsedBody([
            'entry_name' => ['common#:#yes'],
            'translation' => [['x']],
            'comment' => [['y']],
            'expected_entry_count' => '1',
        ], $this->templateExpectingTheSavedSuccess(), $this->ctrlExpectingTheRedirectToView());
        $gui->expects($this->never())->method('initEditEntriesForm');
        $warnings = [];
        set_error_handler(static function (int $number, string $message) use (&$warnings): bool {
            $warnings[] = $message;
            return true;
        });
        try {
            $gui->saveEditedEntriesObject();
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $warnings);
    }
}
