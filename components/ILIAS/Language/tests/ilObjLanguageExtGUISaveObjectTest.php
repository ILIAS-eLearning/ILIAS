<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
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
     * Partial mock with viewObject() stubbed so its call arguments can be captured.
     *
     * @param array<string, string|list<string>> $parsed_body
     * @return ilObjLanguageExtGUI&MockObject
     */
    private function createGuiForSaveObjectWithParsedBody(array $parsed_body): ilObjLanguageExtGUI
    {
        /** @var ilObjLanguageExtGUI&MockObject $gui */
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['viewObject'])
            ->getMock();

        $this->inject($gui, 'http', $this->mockHttpWithParsedBody($parsed_body));
        $this->inject($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->inject($gui, 'object', $this->createFakeLanguageObject('de'));
        $this->inject($gui, 'refinery', new \ILIAS\Refinery\Factory(
            $this->createStub(\ILIAS\Data\Factory::class),
            $this->createStub(\ILIAS\Language\Language::class)
        ));

        return $gui;
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

    public function testSaveObjectRejectsEverythingWhenAnyValueHasDisallowedMarkupAndNamesEveryAffectedKeyEscaped(): void
    {
        $this->seedEmptyGlobalLanguageFile('de');
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['ilDB'] = fn() => $this->databaseThatMustNeverBeWrittenTo();
        $GLOBALS['DIC']['lng'] = fn() => $this->createStub(ilLanguage::class);
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($this->createStub(ilLogger::class));
        $GLOBALS['DIC']['ilLoggerFactory'] = static fn() => $logger_factory;

        $gui = $this->createGuiForSaveObjectWithParsedBody([
            'common#:#yes' => '<script>alert(1)</script>',
            'common#:#yes#:#comment' => '',
            // the KEY itself contains HTML-significant characters (a form field name)
            'other<b>#:#x' => '<script>alert(2)</script>',
            'other<b>#:#x#:#comment' => '',
        ]);
        $this->inject($gui, 'object', $this->createFakeLanguageObject('de'));
        $gui->expects($this->once())->method('viewObject')->with(
            0,
            [],
            $this->callback(static function (array $messages): bool {
                $message = $messages[0] ?? '';
                return str_contains($message, 'common#:#yes')
                    && str_contains($message, '&lt;b&gt;')
                    && !str_contains($message, '<b>#:#x')
                    && !str_contains($message, 'alert(1)')
                    && !str_contains($message, 'alert(2)')
                    && str_contains($message, 'common#:#yes: &lt;script&gt;');
            }),
            $this->callback(static fn(array $values): bool => ($values['common#:#yes'] ?? null) === '<script>alert(1)</script>'),
            $this->identicalTo(['common#:#yes' => '', 'other<b>#:#x' => ''])
        );

        $gui->saveObject();
    }

    public function testSaveObjectTransformsLineBreaksAndDoubleLessThanBeforeCheckingThenSaves(): void
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
            'testmodule' . $lng->separator . 'greeting' => "<b>x</b>\r\n",
            'testmodule' . $lng->separator . 'greeting' . $lng->separator . 'comment' => '',
        ]);
        $this->inject($gui, 'object', $this->createFakeLanguageObject('zz_saveobject_test'));
        $gui->expects($this->once())->method('viewObject')->with(1, []);

        $gui->saveObject();

        $this->assertSame(['greeting' => '<b>x</b><br />'], $captured_lang_array);
    }

    public function testSaveObjectIgnoresAValueAndACommentSentAsArray(): void
    {
        $this->seedEmptyGlobalLanguageFile('de');
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['ilDB'] = fn() => $this->databaseThatMustNeverBeWrittenTo();
        $GLOBALS['DIC']['lng'] = fn() => $this->createStub(ilLanguage::class);
        $gui = $this->createGuiForSaveObjectWithParsedBody([
            'common#:#yes' => ['x'],
            'common#:#yes#:#comment' => ['y'],
        ]);
        $gui->expects($this->once())->method('viewObject')->with(1, []);
        $warnings = [];
        set_error_handler(static function (int $number, string $message) use (&$warnings): bool {
            $warnings[] = $message;
            return true;
        });
        try {
            $gui->saveObject();
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $warnings);
    }
}
