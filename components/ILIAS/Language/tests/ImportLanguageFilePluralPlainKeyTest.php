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

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\Stub;

/**
 * importLanguageFile() maps a plain key of a plural message of a PO module to its default form key right after
 * reading the file, i.e. before the $to_keep/$kept_by_mode comparison and the markup pre-check.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ImportLanguageFilePluralPlainKeyTest extends ilLanguageBaseTestCase
{
    private const string MODULE = 'ptest3';
    private const string LANG_KEY = 'de';

    private ?string $fixture_directory = null;
    private bool $created_client_data_dir_root = false;
    private ?string $upload_file = null;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'ILIAS_ABSOLUTE_PATH' => realpath(__DIR__ . '/../../../../'),
            'ILIAS_HTTP_PATH' => 'http://localhost',
            'ILIAS_VERSION' => 'test',
        ] as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
        $this->created_client_data_dir_root = MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);

        MigratedPoFixture::resetRuntime();
        (new ReflectionClass(ilCachedLanguage::class))->getProperty('instances')->setValue(null, []);
        (new ReflectionClass(ilLanguageFile::class))->getProperty('global_file_objects')->setValue(null, []);
    }

    protected function tearDown(): void
    {
        if ($this->fixture_directory !== null) {
            MigratedPoFixture::removeShippedDirectory('components/ILIAS/Language/tests/' . $this->fixture_directory);
            MigratedPoFixture::removeDirectory(rtrim(CLIENT_DATA_DIR, '/') . '/lang');
        }
        if ($this->upload_file !== null && is_file($this->upload_file)) {
            unlink($this->upload_file);
        }
        if ($this->created_client_data_dir_root && defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }
        (new ReflectionClass(ilLanguageFile::class))->getProperty('global_file_objects')->setValue(null, []);

        parent::tearDown();
    }

    private function seedShippedPluralModule(): LanguageFileDirectory
    {
        $this->fixture_directory = 'tmp-import-plural-fixtures-' . bin2hex(random_bytes(4));
        $relative_path = 'components/ILIAS/Language/tests/' . $this->fixture_directory . '/';

        $catalog = new TranslationCatalog();
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $entry = new TranslationEntry(self::MODULE, 'item');
        $entry->setPlural('items', ['Eintrag', 'Einträge']);
        $catalog->add($entry);
        MigratedPoFixture::writeShippedPo($relative_path, self::MODULE, self::LANG_KEY, $catalog);

        $directory = MigratedPoFixture::directory(self::MODULE, $relative_path);
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $directory)
        );

        return $directory;
    }

    private function stubLng(): ilLanguage
    {
        $lng = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        $this->setGlobalVariable('lng', $lng);

        return $lng;
    }

    private function seedEmptyGlobalLanguageFile(string $lang_key): void
    {
        $fake_global_file = (new ReflectionClass(ilLanguageFile::class))->newInstanceWithoutConstructor();
        $fake_global_file->setAllValues([]);
        $fake_global_file->setAllComments([]);

        $cache = (new ReflectionClass(ilLanguageFile::class))->getProperty('global_file_objects');
        $existing = $cache->isInitialized() ? $cache->getValue() : [];
        $cache->setValue(null, $existing + [$lang_key => $fake_global_file]);
    }

    /**
     * @param list<array{0: string, 1: array<string, mixed>}> $replace_calls filled with every
     *        replace("lng_data", ...) call: [identifier, ['value' => ..., 'local_change' => ...]]
     */
    private function mockDatabaseForWrites(array &$replace_calls): ilDBInterface&Stub
    {
        $lng_modules_stmt = $this->createStub(ilDBStatement::class);
        $remarks_stmt = $this->createStub(ilDBStatement::class);
        $lng_data_stmt = $this->createStub(ilDBStatement::class);
        $query_f_stmt = $this->createStub(ilDBStatement::class);

        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value, string $type = ''): string => "'" . (string) $value . "'");
        $db->method('in')->willReturn("module IN ('placeholder')");
        $db->method('query')->willReturnCallback(
            static function (string $sql) use ($lng_modules_stmt, $remarks_stmt, $lng_data_stmt): ilDBStatement {
                if (str_contains($sql, 'lng_modules')) {
                    return $lng_modules_stmt;
                }
                return str_contains($sql, 'remarks') ? $remarks_stmt : $lng_data_stmt;
            }
        );
        $db->method('queryF')->willReturn($query_f_stmt);
        $db->method('fetchAssoc')->willReturnCallback(
            static function (ilDBStatement $statement) use ($query_f_stmt): ?array {
                return $statement === $query_f_stmt ? ['lang_array' => serialize([])] : null;
            }
        );
        $db->method('manipulate')->willReturn(0);
        $db->method('insert')->willReturn(1);
        $db->method('replace')->willReturnCallback(
            static function (string $table, array $keys, array $values) use (&$replace_calls): int {
                if ($table === 'lng_data') {
                    $replace_calls[] = [$keys['identifier'][1] ?? null, [
                        'value' => $values['value'][1] ?? null,
                        'local_change' => $values['local_change'][1] ?? null,
                    ]];
                }
                return 1;
            }
        );

        $this->setGlobalVariable('ilDB', $db);
        $this->setGlobalVariable('ilErr', $this->createStub(ilErrorHandling::class));

        return $db;
    }

    private function languageObject(): ilObjLanguageExt
    {
        $object = (new ReflectionClass(ilObjLanguageExt::class))->newInstanceWithoutConstructor();
        $object->key = self::LANG_KEY;
        $object->separator = '#:#';

        return $object;
    }

    private function overlayPoPath(): string
    {
        return rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . self::MODULE . '/' . self::LANG_KEY . '/' . self::MODULE . '_' . self::LANG_KEY . '.po';
    }

    private function writeUploadFile(string $content): string
    {
        $this->upload_file = sys_get_temp_dir() . '/ilias_import_plural_' . bin2hex(random_bytes(4)) . '_' . self::LANG_KEY . '.lang';
        file_put_contents($this->upload_file, "/* header */\n<!-- language file start -->\n" . $content);

        return $this->upload_file;
    }

    /** Changes the default form (form 1) locally, so an overlay exists that differs from the shipped value. */
    private function locallyChangeDefaultForm(ilLanguage $lng, string $value): void
    {
        $replace_calls = [];
        $this->mockDatabaseForWrites($replace_calls);

        ilObjLanguageExt::_saveValues(self::LANG_KEY, [self::MODULE . $lng->separator . 'item [1]' => $value], []);

        $entry = TranslationCatalog::fromPoFile($this->overlayPoPath())->find(null, 'item');
        $this->assertNotNull($entry, 'precondition: the overlay holds the local change');
        $this->assertSame($value, $entry->getPluralTranslations()[1] ?? null, 'precondition failed');
    }

    /** "keepnew"/"keepall" keep the locally changed value for a plain-key line, like for an explicit form-key line. */
    #[DataProvider('modesThatKeepTheLocalChange')]
    public function testPlainKeyLineIsKeptWhenTheStandardFormIsAlreadyLocallyChanged(string $mode): void
    {
        $this->seedShippedPluralModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $this->locallyChangeDefaultForm($lng, 'Alte lokale Form');

        $replace_calls = [];
        $this->mockDatabaseForWrites($replace_calls);
        $file = $this->writeUploadFile(self::MODULE . "#:#item#:#X\n");

        $this->languageObject()->importLanguageFile($file, $mode);

        $entry = TranslationCatalog::fromPoFile($this->overlayPoPath())->find(null, 'item');
        $this->assertNotNull($entry);
        $this->assertSame('Alte lokale Form', $entry->getPluralTranslations()[1] ?? null, 'the local change is kept, not overwritten by X');
        $item_calls = array_values(array_filter($replace_calls, static fn(array $call): bool => $call[0] === 'item'));
        $this->assertSame([], $item_calls, 'a kept key needs no rewrite: nothing to save for it');
    }

    /** @return array<string, array{0: string}> */
    public static function modesThatKeepTheLocalChange(): array
    {
        return ['keepnew' => ['keepnew'], 'keepall' => ['keepall']];
    }

    public function testPlainKeyLineOverwritesTheStandardFormInReplaceMode(): void
    {
        $this->seedShippedPluralModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $this->locallyChangeDefaultForm($lng, 'Alte lokale Form');

        $replace_calls = [];
        $this->mockDatabaseForWrites($replace_calls);
        $file = $this->writeUploadFile(self::MODULE . "#:#item#:#X\n");

        $this->languageObject()->importLanguageFile($file, 'replace');

        $entry = TranslationCatalog::fromPoFile($this->overlayPoPath())->find(null, 'item');
        $this->assertNotNull($entry);
        $this->assertSame('X', $entry->getPluralTranslations()[1] ?? null, 'replace overwrites the standard form with the uploaded value');
        $item_calls = array_values(array_filter($replace_calls, static fn(array $call): bool => $call[0] === 'item'));
        $this->assertCount(1, $item_calls);
        $this->assertSame('X', $item_calls[0][1]['value']);
    }

    /** With $skipInvalidMarkup the invalid plain-key line is left out and reported under the resolved form key ("<module>#:#item [1]"). */
    public function testInvalidMarkupOfAPlainPluralKeyIsSkippedUnderItsResolvedFormKey(): void
    {
        $this->seedShippedPluralModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $replace_calls = [];
        $this->mockDatabaseForWrites($replace_calls);
        $file = $this->writeUploadFile(self::MODULE . "#:#item#:#<script>alert(1)</script>\n");

        $object = $this->languageObject();
        $object->importLanguageFile($file, 'replace', true);

        $skipped = $object->getSkippedInvalidMarkupValues();
        $this->assertArrayHasKey(self::MODULE . '#:#item [1]', $skipped, 'reported under the resolved form key');
        $this->assertArrayNotHasKey(self::MODULE . '#:#item', $skipped, 'never under the plain key the file line used');

        if (is_file($this->overlayPoPath())) {
            $entry = TranslationCatalog::fromPoFile($this->overlayPoPath())->find(null, 'item');
            $this->assertNotNull($entry);
            $this->assertSame('Einträge', $entry->getPluralTranslations()[1] ?? null);
        }
    }

    /** Without $skipInvalidMarkup the invalid-value map of the exception is keyed by the resolved form key too. */
    public function testInvalidMarkupOfAPlainPluralKeyRejectsTheImportNamingTheResolvedFormKey(): void
    {
        $this->seedShippedPluralModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $replace_calls = [];
        $this->mockDatabaseForWrites($replace_calls);
        $file = $this->writeUploadFile(self::MODULE . "#:#item#:#<script>alert(1)</script>\n");

        try {
            $this->languageObject()->importLanguageFile($file, 'replace');
            $this->fail('Expected an ilLanguageInvalidMarkupException');
        } catch (ilLanguageInvalidMarkupException $e) {
            $this->assertSame([self::MODULE . '#:#item [1]'], array_keys($e->getInvalidValues()));
        }
    }

    /** Plain and explicit form line of the same form: the later one wins; the plain line's "###" comment becomes the remark. */
    public function testLaterFormLineWinsOverAnEarlierPlainLineAndItsCommentBecomesTheFormsRemark(): void
    {
        $this->seedShippedPluralModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $replace_calls = [];
        $this->mockDatabaseForWrites($replace_calls);
        $file = $this->writeUploadFile(
            self::MODULE . "#:#item#:#Von Plain-Key###Bitte pruefen\n"
            . self::MODULE . "#:#item [1]#:#Von Formzeile\n"
        );

        $this->languageObject()->importLanguageFile($file, 'replace');

        $entry = TranslationCatalog::fromPoFile($this->overlayPoPath())->find(null, 'item');
        $this->assertNotNull($entry);
        $this->assertSame('Von Formzeile', $entry->getPluralTranslations()[1] ?? null, 'the later, explicit form line wins');
    }

    public function testLaterPlainLineWinsOverAnEarlierFormLine(): void
    {
        $this->seedShippedPluralModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $replace_calls = [];
        $this->mockDatabaseForWrites($replace_calls);
        $file = $this->writeUploadFile(
            self::MODULE . "#:#item [1]#:#Von Formzeile\n"
            . self::MODULE . "#:#item#:#Von Plain-Key###Bitte pruefen\n"
        );

        $this->languageObject()->importLanguageFile($file, 'replace');

        $entry = TranslationCatalog::fromPoFile($this->overlayPoPath())->find(null, 'item');
        $this->assertNotNull($entry);
        $this->assertSame('Von Plain-Key', $entry->getPluralTranslations()[1] ?? null, 'the later, plain line wins');
        $this->assertSame(
            'Bitte pruefen',
            \ILIAS\Language\ComponentTranslation\LocalChangeComments::getRemark($entry),
            'the plain line\'s "###" comment becomes the remark of the resolved form'
        );
    }
}
