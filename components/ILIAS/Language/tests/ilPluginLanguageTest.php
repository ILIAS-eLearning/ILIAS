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

/**
 * ilPluginLanguage: `.po` takes precedence over `.lang`, and updateLanguages() reads a plugin's `ilias_<lang>.po` through
 * the database-only path (a plugin cannot contribute a LanguageFileDirectory, so no overlay file is written).
 */
class ilPluginLanguageTest extends ilLanguageBaseTestCase
{
    private ?string $plugin_root_directory = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->plugin_root_directory) && is_dir($this->plugin_root_directory)) {
            MigratedPoFixture::removeDirectory($this->plugin_root_directory);
        }

        parent::tearDown();
    }

    private function createPluginInfo(string $module_prefix): ilPluginInfo
    {
        $this->plugin_root_directory = sys_get_temp_dir() . '/ilias_plugin_lang_test_' . bin2hex(random_bytes(4));
        mkdir($this->plugin_root_directory . '/lang', 0775, true);

        $component = $this->createStub(ilComponentInfo::class);
        $component->method('getId')->willReturn('comp');
        $slot = $this->createStub(ilPluginSlotInfo::class);
        $slot->method('getId')->willReturn('slot');

        $plugin_info = $this->createStub(ilPluginInfo::class);
        $plugin_info->method('getComponent')->willReturn($component);
        $plugin_info->method('getPluginSlot')->willReturn($slot);
        $plugin_info->method('getId')->willReturn($module_prefix);
        $plugin_info->method('getPath')->willReturn($this->plugin_root_directory);

        return $plugin_info;
    }

    /** @param array<string, string> $entries identifier => value */
    private function writeShippedPluginPo(string $lang_key, array $entries): void
    {
        $catalog = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog();
        foreach ($entries as $identifier => $value) {
            $catalog->add(\MigratedPoFixture::entry(null, $identifier, $value));
        }
        \MigratedPoFixture::writePo($this->plugin_root_directory . '/lang/ilias_' . $lang_key . '.po', $catalog);
    }

    public function testGetAvailableLangFilesListsBothLangAndPoFiles(): void
    {
        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest'));
        touch($this->plugin_root_directory . '/lang/ilias_de.lang');
        touch($this->plugin_root_directory . '/lang/ilias_en.po');

        $files = $plugin_language->getAvailableLangFiles();
        $by_key = array_combine(array_column($files, 'key'), array_column($files, 'file'));

        $this->assertSame('ilias_de.lang', $by_key['de']);
        $this->assertSame('ilias_en.po', $by_key['en']);
    }

    public function testPoWinsOverLangForTheSameLanguage(): void
    {
        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest'));
        touch($this->plugin_root_directory . '/lang/ilias_de.lang');
        touch($this->plugin_root_directory . '/lang/ilias_de.po');

        $files = $plugin_language->getAvailableLangFiles();

        $this->assertCount(1, $files);
        $this->assertSame('ilias_de.po', $files[0]['file']);
    }

    /**
     * ilDB double for updateLanguages(): no local changes, replaceLangEntry()'s replace() captured, replaceLangModule()
     * answered generically.
     *
     * @param array<string, string> $captured_replace_entries filled with "module#:#identifier" => value
     */
    private function stubDatabase(array &$captured_replace_entries): ilDBInterface
    {
        // replaceLangModule()'s self-check SELECT must unserialize() to an array (mantis #20046/#19140), while
        // _getLocalChangesByModule()'s SELECT must return no rows: told apart by SQL text
        $self_check_statement = $this->createStub(ilDBStatement::class);
        $no_rows_statement = $this->createStub(ilDBStatement::class);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value, string $type = ''): string => "'" . (string) $value . "'");
        $db->method('queryF')->willReturnCallback(
            static fn(string $sql) => str_contains($sql, 'lng_modules') ? $self_check_statement : $no_rows_statement
        );
        $db->method('query')->willReturn($self_check_statement);
        $db->method('fetchAssoc')->willReturnCallback(
            static fn(ilDBStatement $statement): ?array => $statement === $self_check_statement
                ? ['lang_array' => serialize([])]
                : null
        );
        $db->method('manipulate')->willReturn(0);
        $db->method('insert')->willReturn(1);
        $db->method('replace')->willReturnCallback(
            function (string $table, array $keys, array $values) use (&$captured_replace_entries): int {
                $captured_replace_entries[$keys['module'][1] . '#:#' . $keys['identifier'][1]] = $values['value'][1];
                return 1;
            }
        );
        $this->setGlobalVariable('ilDB', $db);

        return $db;
    }

    /** @param list<string> $warnings */
    private function stubLoggerCapturingWarnings(array &$warnings): void
    {
        $logger = $this->createStub(ilLogger::class);
        $logger->method('warning')->willReturnCallback(static function (string $message) use (&$warnings): void {
            $warnings[] = $message;
        });
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);
    }

    public function testUpdateLanguagesWritesPoValuesWithThePrefixThroughTheDatabaseOnly(): void
    {
        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest'));
        $this->writeShippedPluginPo('de', ['greeting' => 'Hallo']);
        $captured = [];
        $this->stubDatabase($captured);

        $plugin_language->updateLanguages(['de']);

        // no LanguageFileDirectoryManager is registered (a plugin cannot contribute one), so the overlay sync is a no-op
        $this->assertSame('Hallo', $captured['comp_slot_ptest#:#comp_slot_ptest_greeting']);
    }

    /** An entry with a msgctxt (even empty) is ignored with a warning: a plugin `.po` has exactly one implicit module. */
    public function testEntriesWithAMsgctxtAreIgnoredAndWarnedAbout(): void
    {
        $context = 'some_module';
        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest'));
        $catalog = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog();
        $catalog->add(\MigratedPoFixture::entry($context, 'foreign', 'Fremd'));
        $catalog->add(\MigratedPoFixture::entry(null, 'greeting', 'Hallo'));
        \MigratedPoFixture::writePo($this->plugin_root_directory . '/lang/ilias_de.po', $catalog);
        $captured = [];
        $this->stubDatabase($captured);
        $warnings = [];
        $this->stubLoggerCapturingWarnings($warnings);

        $plugin_language->updateLanguages(['de']);

        $this->assertArrayNotHasKey('comp_slot_ptest#:#comp_slot_ptest_foreign', $captured);
        $this->assertSame('Hallo', $captured['comp_slot_ptest#:#comp_slot_ptest_greeting']);
        $this->assertNotEmpty(array_filter($warnings, static fn(string $w): bool => str_contains($w, 'msgctxt') && str_contains($w, 'foreign')));
    }

    /** Disallowed markup in a plugin `.po` value is cleaned and warned about. */
    public function testDisallowedMarkupIsSanitisedAndWarnedAboutNotRejected(): void
    {
        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest'));
        $this->writeShippedPluginPo('de', ['greeting' => '<script>alert(1)</script>Hallo']);
        $captured = [];
        $this->stubDatabase($captured);
        $warnings = [];
        $this->stubLoggerCapturingWarnings($warnings);

        $plugin_language->updateLanguages(['de']);

        $value = $captured['comp_slot_ptest#:#comp_slot_ptest_greeting'];
        $this->assertStringNotContainsString('<script', $value);
        $this->assertStringContainsString('Hallo', $value);
        $this->assertNotEmpty(array_filter($warnings, static fn(string $w): bool => str_contains($w, 'Markup not allowed')));
    }

    /** Unlike a plugin `.po`, a `.lang` value is taken over unchanged: disallowed markup is only logged. */
    public function testDisallowedMarkupInALangFileIsTakenOverUnchangedAndWarnedAbout(): void
    {
        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest'));
        file_put_contents(
            $this->plugin_root_directory . '/lang/ilias_de.lang',
            "greeting#:#<script>alert(1)</script>Hallo\n"
        );
        $captured = [];
        $this->stubDatabase($captured);
        $warnings = [];
        $this->stubLoggerCapturingWarnings($warnings);

        $plugin_language->updateLanguages(['de']);

        $value = $captured['comp_slot_ptest#:#comp_slot_ptest_greeting'];
        $this->assertSame('<script>alert(1)</script>Hallo', $value);
        $this->assertNotEmpty(array_filter($warnings, static fn(string $w): bool => str_contains($w, 'Markup not allowed')));
    }

    public function testEmptyTranslationsAreSkipped(): void
    {
        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest'));
        $this->writeShippedPluginPo('de', ['greeting' => 'Hallo', 'empty_one' => '']);
        $captured = [];
        $this->stubDatabase($captured);

        $plugin_language->updateLanguages(['de']);

        $this->assertArrayHasKey('comp_slot_ptest#:#comp_slot_ptest_greeting', $captured);
        $this->assertArrayNotHasKey('comp_slot_ptest#:#comp_slot_ptest_empty_one', $captured);
    }

    /** A broken `.po` skips only that language. */
    public function testABrokenPoSkipsOnlyThatLanguageAndOthersAreStillUpdated(): void
    {
        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest'));
        file_put_contents($this->plugin_root_directory . '/lang/ilias_de.po', "msgid \"kaputt\n");
        $this->writeShippedPluginPo('en', ['greeting' => 'Hello']);
        $captured = [];
        $this->stubDatabase($captured);
        $warnings = [];
        $this->stubLoggerCapturingWarnings($warnings);

        $plugin_language->updateLanguages(['de', 'en']);

        $this->assertSame('Hello', $captured['comp_slot_ptest#:#comp_slot_ptest_greeting'], 'the valid "en" .po is still applied');
        $this->assertNotEmpty(array_filter($warnings, static fn(string $w): bool => str_contains($w, 'cannot be read')));
    }

    public function testTxtFallsBackToTheDefaultLanguageForAKeyMissingInTheUserLanguage(): void
    {
        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest'));
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnCallback(static fn(string $key): string => $key === 'comp_slot_ptest_known' ? 'Bekannt' : '-' . $key . '-');
        $lng->method('getDefaultLanguage')->willReturn('en');
        $this->setGlobalVariable('lng', $lng);
        $this->setGlobalVariable('ilClientIniFile', $this->createStub(ilIniFile::class));
        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value): string => "'" . (string) $value . "'");
        $queries = [];
        $db->method('query')->willReturnCallback(static function (string $sql) use (&$queries, $statement) {
            $queries[] = $sql;

            return $statement;
        });
        // answers only the lookup of the default language for exactly this module and key
        $db->method('fetchAssoc')->willReturnCallback(static function () use (&$queries): ?array {
            return str_contains(end($queries), "module = 'comp_slot_ptest' AND lang_key = 'en' AND identifier = 'comp_slot_ptest_missing'")
                ? ['value' => 'Missing en']
                : null;
        });
        $this->setGlobalVariable('ilDB', $db);

        $this->assertSame('Bekannt', $plugin_language->txt('known'));
        $this->assertSame([], $queries, 'a key present in the user language is not looked up again');
        $this->assertSame('Missing en', $plugin_language->txt('missing'));
        $this->assertCount(1, $queries);
        $this->assertSame('-comp_slot_ptest_none-', $plugin_language->txt('none'));
    }
}
