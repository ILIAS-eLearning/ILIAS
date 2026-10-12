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

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Activities\LanguageSettings;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * Only what runs without the legacy container: the installed languages, the settings behind an injected Setting,
 * the lookup of the module content and the plural check against shipped `.po` files. The writes through
 * \ilObjLanguage are covered by the Activity tests as calls on a mocked provider.
 */
class RepositoryProviderTest extends \ilLanguageBaseTestCase
{
    public function testGetInstalledLanguagesDelegatesToTheRepository(): void
    {
        $installed = $this->createMock(InstalledLanguageRepository::class);
        $installed->expects($this->once())->method('getInstalledLanguages')->willReturn(['de', 'en']);

        $provider = new RepositoryProvider($installed, $this->createStub(\ilDBInterface::class), $this->createStub(LanguageSettings::class));

        $this->assertSame(['de', 'en'], $provider->getInstalledLanguages());
    }

    /** @return array<string, array{0: ?array<string, mixed>}> */
    public static function unusableLngModulesRows(): array
    {
        return [
            'no row' => [null],
            // unserialize(null) would throw a \TypeError under strict_types
            'lang_array is null' => [['lang_array' => null]],
            'lang_array is not a string' => [['lang_array' => 42]],
            'lang_array decodes to a non-array' => [['lang_array' => serialize('not-an-array')]],
            'lang_array is an object (allowed_classes false)' => [['lang_array' => serialize(new \ArrayObject(['x' => 'y']))]],
        ];
    }

    /**
     * A module that is not maintained in PO files without a usable lng_modules row yields null: the module name is
     * free input, so lng_data must not be used to create a row for an arbitrary one.
     *
     * @param ?array<string, mixed> $lng_modules_row
     */
    #[DataProvider('unusableLngModulesRows')]
    public function testCurrentModuleEntriesIsNullWithoutAUsableLngModulesRowForAModuleNotMaintainedInPoFiles(?array $lng_modules_row): void
    {
        $db = $this->dbFetching($lng_modules_row, [['identifier' => 'new_topic', 'value' => 'Hallo']]);

        $this->assertNull($this->currentModuleEntries(new RepositoryProvider($this->createStub(InstalledLanguageRepository::class), $db, $this->createStub(LanguageSettings::class))));
    }

    public function testCurrentModuleEntriesIsTheUsableLngModulesRow(): void
    {
        $db = $this->dbFetching(['lang_array' => serialize(['existing' => 'Bestehend'])], [['identifier' => 'ignored', 'value' => 'x']]);

        $this->assertSame(
            ['existing' => 'Bestehend'],
            $this->currentModuleEntries(new RepositoryProvider($this->createStub(InstalledLanguageRepository::class), $db, $this->createStub(LanguageSettings::class)))
        );
    }

    public function testCurrentModuleEntriesQuotesLanguageKeyAndModuleIntoTheQuery(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->exactly(2))->method('quote')->willReturnCallback(
            static fn(mixed $value, string $type): string => "<$type:$value>"
        );
        $db->expects($this->once())->method('query')->with(
            'SELECT lang_array FROM lng_modules WHERE lang_key = <text:de> AND module = <text:co\'mmon>'
        );
        $db->method('fetchAssoc')->willReturn(null);

        (new \ReflectionMethod(RepositoryProvider::class, 'currentModuleEntries'))
            ->invoke(new RepositoryProvider($this->createStub(InstalledLanguageRepository::class), $db, $this->createStub(LanguageSettings::class)), 'de', "co'mmon");
    }

    /**
     * For a module that is maintained in PO files (a shipped `.po` exists) with neither a readable overlay nor a
     * usable lng_modules row the module content is rebuilt from lng_data, which already holds the new entry.
     */
    #[RunInSeparateProcess]
    public function testCurrentModuleEntriesRebuildsFromLngDataForAModuleMaintainedInPoFilesWithoutAReadableOverlayOrRow(): void
    {
        $this->defineRuntimePaths();
        $fixture_dir = __DIR__ . '/tmp-provider-fixtures-' . bin2hex(random_bytes(4));
        mkdir($fixture_dir, 0775, true);
        \MigratedPoFixture::writePo(
            $fixture_dir . '/common_de.po',
            \MigratedPoFixture::catalog('common', ['existing' => 'Veraltet'])
        );
        // an unreadable overlay leads to the lng_modules/lng_data fallback; without overlay the shipped state is the base
        $overlay_dir = CLIENT_DATA_DIR . '/lang/common/de';
        mkdir($overlay_dir, 0775, true);
        file_put_contents($overlay_dir . '/common_de.po', "msgid \"kaputt\n");

        try {
            $db = $this->dbFetching(null, [
                ['identifier' => 'existing', 'value' => 'Bestehend'],
                ['identifier' => 'new_topic', 'value' => 'Hallo'],
            ]);
            $provider = new RepositoryProvider(
                $this->createStub(InstalledLanguageRepository::class),
                $db,
                $this->createStub(LanguageSettings::class),
                $this->managerFor($fixture_dir, 'common')
            );

            $this->assertSame(['existing' => 'Bestehend', 'new_topic' => 'Hallo'], $this->currentModuleEntries($provider));
        } finally {
            \MigratedPoFixture::removeDirectory($fixture_dir);
            \MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }
    }

    /**
     * A form missing from a shipped plural message (here "item [2]" of a message with two forms) is reported with
     * its language; an existing form, another module's identifier or a language without shipped `.po` is not.
     */
    #[RunInSeparateProcess]
    public function testLanguagesWithoutPluralFormListsOnlyLanguagesWhoseShippedMessageLacksTheForm(): void
    {
        $this->defineRuntimePaths();
        $fixture_dir = __DIR__ . '/tmp-provider-plural-' . bin2hex(random_bytes(4));
        mkdir($fixture_dir, 0775, true);
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $entry = new TranslationEntry('poll', 'item');
        $entry->setPlural('items', ['Eintrag', 'Einträge']);
        $catalog->add($entry);
        \MigratedPoFixture::writePo($fixture_dir . '/poll_de.po', $catalog);

        try {
            $provider = new RepositoryProvider(
                $this->createStub(InstalledLanguageRepository::class),
                $this->createStub(\ilDBInterface::class),
                $this->createStub(LanguageSettings::class),
                $this->managerFor($fixture_dir, 'poll')
            );

            $this->assertSame(['de'], $provider->languagesWithoutPluralForm('poll', 'item [2]', ['de', 'fr']));
            $this->assertSame([], $provider->languagesWithoutPluralForm('poll', 'item [1]', ['de']));
            $this->assertSame([], $provider->languagesWithoutPluralForm('poll', 'item', ['de']));
            $this->assertSame([], $provider->languagesWithoutPluralForm('other', 'item [2]', ['de']));
        } finally {
            \MigratedPoFixture::removeDirectory($fixture_dir);
            \MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }
    }

    public function testLanguagesWithoutPluralFormIsEmptyWithoutAFileDirectoryManager(): void
    {
        $provider = new RepositoryProvider(
            $this->createStub(InstalledLanguageRepository::class),
            $this->createStub(\ilDBInterface::class),
            $this->createStub(LanguageSettings::class)
        );

        $this->assertSame([], $provider->languagesWithoutPluralForm('poll', 'item [2]', ['de']));
    }

    /** @return array<string, array{0: \Closure}> */
    public static function dicWithoutComponentRepository(): array
    {
        return [
            'DIC is an array' => [static fn(): array => []],
            'container without component.repository' => [static fn(): \ILIAS\DI\Container => new \ILIAS\DI\Container()],
        ];
    }

    /** Without a component repository (Setup) re-applying the plugin language files does nothing. */
    #[DataProvider('dicWithoutComponentRepository')]
    public function testRefreshPluginsDoesNothingWithoutAComponentRepository(\Closure $dic): void
    {
        $GLOBALS['DIC'] = $dic();

        (new RepositoryProvider($this->createStub(InstalledLanguageRepository::class), $this->createStub(\ilDBInterface::class), $this->createStub(LanguageSettings::class)))
            ->refreshPlugins(['de']);

        $this->addToAssertionCount(1);
    }

    public function testSettingsAreDelegatedToTheLanguageSettings(): void
    {
        $settings = $this->createMock(LanguageSettings::class);
        $settings->expects($this->once())->method('setLanguageDetectionEnabled')->with(true);
        $settings->expects($this->once())->method('isPageTranslationEnabled')->with('fr')->willReturn(true);
        $settings->expects($this->once())->method('setPageTranslationEnabled')->with('de', false);

        $provider = new RepositoryProvider(
            $this->createMock(InstalledLanguageRepository::class),
            $this->createMock(\ilDBInterface::class),
            $settings
        );
        $provider->setLanguageDetectionEnabled(true);
        $this->assertTrue($provider->isPageTranslationEnabled('fr'));
        $provider->setPageTranslationEnabled('de', false);
    }

    /** The private lookup of the module content that updateModuleCache() adds the new entry to. */
    private function currentModuleEntries(RepositoryProvider $provider): ?array
    {
        return (new \ReflectionMethod(RepositoryProvider::class, 'currentModuleEntries'))->invoke($provider, 'de', 'common');
    }

    /** A manager whose only shipped directory is $fixture_dir, holding the `.po` files of $module. */
    private function managerFor(string $fixture_dir, string $module): LanguageFileDirectoryManager
    {
        return new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            \MigratedPoFixture::directory($module, 'components/ILIAS/Language/tests/Activities/' . basename($fixture_dir) . '/')
        );
    }

    /** Needs a separate process: the constants cannot be undefined again. */
    private function defineRuntimePaths(): void
    {
        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../../'));
        }
        define('CLIENT_DATA_DIR', sys_get_temp_dir() . '/ilias_provider_test_' . bin2hex(random_bytes(4)));
    }

    /**
     * @param ?array<string, mixed> $lng_modules_row
     * @param list<array{identifier: string, value: string}> $lng_data_rows
     */
    private function dbFetching(?array $lng_modules_row, array $lng_data_rows): \ilDBInterface
    {
        $modules_statement = $this->createStub(\ilDBStatement::class);
        $data_statement = $this->createStub(\ilDBStatement::class);

        $db = $this->createStub(\ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn(mixed $value, string $type): string => "'" . (string) $value . "'");
        $db->method('query')->willReturnCallback(
            static fn(string $query): \ilDBStatement => str_contains($query, 'FROM lng_modules')
                ? $modules_statement
                : $data_statement
        );
        $db->method('fetchAssoc')->willReturnCallback(
            static function (\ilDBStatement $statement) use ($modules_statement, $lng_modules_row, &$lng_data_rows): ?array {
                return $statement === $modules_statement ? $lng_modules_row : array_shift($lng_data_rows);
            }
        );

        return $db;
    }
}
