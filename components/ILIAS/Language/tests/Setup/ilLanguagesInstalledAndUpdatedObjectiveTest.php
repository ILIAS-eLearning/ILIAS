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

use ILIAS\Setup;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub;

/**
 * Guards the database handling of ilLanguagesInstalledAndUpdatedObjective: ilSetupLanguage resolves its database
 * lazily, so setDbHandler() must stay authoritative; a regression would be silent because the global fallback
 * takes over again.
 */
class ilLanguagesInstalledAndUpdatedObjectiveTest extends TestCase
{
    use RestoresProcessState;

    private bool $had_global_db;
    private mixed $previous_global_db = null;

    protected function setUp(): void
    {
        // never the build of the installation, see MigratedPoFixture::resetRuntime()
        MigratedPoFixture::resetRuntime();
        // these tests replace/remove $GLOBALS['ilDB'], which other tests in random order rely on
        $this->had_global_db = isset($GLOBALS['ilDB']);
        $this->previous_global_db = $GLOBALS['ilDB'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->had_global_db) {
            $GLOBALS['ilDB'] = $this->previous_global_db;
        } else {
            unset($GLOBALS['ilDB']);
        }
    }

    /**
     * @param list<string> $log collects the tag of every database actually queried
     */
    private function createDatabaseMock(string $tag, array &$log): ilDBInterface
    {
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn(mixed $value): string => "'" . (string) $value . "'");
        $db->method('like')->willReturn('1=1');
        $db->method('now')->willReturn('NOW()');
        $db->method('nextId')->willReturn(1);
        $db->method('manipulate')->willReturnCallback(static function () use ($tag, &$log): int {
            $log[] = $tag;
            return 1;
        });
        $db->method('query')->willReturnCallback(function () use ($tag, &$log) {
            $log[] = $tag;
            return $this->createStub(ilDBStatement::class);
        });

        // a single installed language, then exhausted; $rows must be captured by reference, or the
        // "while ($row = fetchObject())" loops never end
        $rows = [(object) ['title' => 'de', 'obj_id' => 7, 'description' => 'installed']];
        $db->method('fetchObject')->willReturnCallback(static function () use (&$rows) {
            return array_shift($rows);
        });

        return $db;
    }

    public function testInjectedDatabaseTakesPrecedenceOverTheGlobal(): void
    {
        $log = [];
        $GLOBALS['ilDB'] = $this->createDatabaseMock('GLOBAL', $log);

        $setup_language = new ilSetupLanguage('de');
        $setup_language->setDbHandler($this->createDatabaseMock('INJECTED', $log));

        $setup_language->getInstalledLanguages();
        $setup_language->registerInstalledLanguage('de', [], []);

        $this->assertNotEmpty($log, 'no database was queried at all');
        $this->assertSame(
            ['INJECTED'],
            array_values(array_unique($log)),
            'the global was used even though a database had been injected'
        );
    }

    public function testAchieveNeedsNoGlobalDatabaseAtAll(): void
    {
        $log = [];
        unset($GLOBALS['ilDB']);

        $objective = new ilLanguagesInstalledAndUpdatedObjective(new ilSetupLanguage('en'));
        $environment = new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_DATABASE => $this->createDatabaseMock('INJECTED', $log),
            // the real ilSetupLanguage resolves the client data directory of the running installation; its owner check may
            // warn (e.g. as root), which is unrelated here
            Setup\Environment::RESOURCE_ADMIN_INTERACTION => $this->createStub(Setup\AdminInteraction::class),
        ]);

        $objective->achieve($environment);

        $this->assertFalse(isset($GLOBALS['ilDB']), 'the global database must not be (re)created');
        $this->assertSame(['INJECTED'], array_values(array_unique($log)));
    }

    public function testGetLabelNeedsNoDatabase(): void
    {
        unset($GLOBALS['ilDB']);
        $setup_language = $this->createMock(ilSetupLanguage::class);
        $setup_language->expects($this->never())->method('getInstalledLanguages');

        $objective = new ilLanguagesInstalledAndUpdatedObjective($setup_language);

        $this->assertSame('Install/Update languages', $objective->getLabel());
    }

    public function testIsApplicableReadsTheLanguagesForTheLabelFromTheSetupDatabase(): void
    {
        $log = [];
        unset($GLOBALS['ilDB']);
        $objective = new ilLanguagesInstalledAndUpdatedObjective(new ilSetupLanguage('en'));
        $environment = new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_DATABASE => $this->createDatabaseMock('INJECTED', $log),
        ]);

        $this->assertTrue($objective->isApplicable($environment));
        $this->assertSame('Install/Update languages de', $objective->getLabel());
        $this->assertSame(['INJECTED'], array_values(array_unique($log)));
    }

    public function testAchieveReusesTheLanguagesDeterminedInIsApplicable(): void
    {
        $setup_language = $this->createMock(ilSetupLanguage::class);
        $setup_language->expects($this->once())->method('getInstalledLanguages')->willReturn(['de', 'fr']);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);
        $install_language = $this->createMock(\ILIAS\Language\Activities\InstallLanguage::class);
        $install_language->expects($this->once())->method('perform')
            ->with(['language_keys' => ['de', 'fr'], 'mode' => \ILIAS\Language\Activities\InstallLanguage::MODE_INSTALL]);
        $update_language = $this->createMock(\ILIAS\Language\Activities\UpdateLanguage::class);
        $update_language->expects($this->once())->method('perform')->with(['language_keys' => ['de', 'fr']]);
        $objective = new ilLanguagesInstalledAndUpdatedObjective($setup_language, $install_language, $update_language);
        $environment = new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_DATABASE => $this->createStub(ilDBInterface::class),
        ]);

        $objective->isApplicable($environment);
        $objective->achieve($environment);

        $this->assertSame('Install/Update languages de, fr', $objective->getLabel());
    }

    /**
     * @param list<string> $log collects the language key every write method was called with, in call order
     */
    private function createSetupLanguageMock(array $installed_language_keys, array &$log): Stub&ilSetupLanguage
    {
        $setup_language = $this->createStub(ilSetupLanguage::class);
        $setup_language->method('getInstalledLanguages')->willReturn($installed_language_keys);
        $setup_language->method('getAvailableLanguagesForInstallation')->willReturn([]);
        $setup_language->method('getLocalLanguages')->willReturn([]);
        $setup_language->method('getInvalidLocalLanguageFiles')->willReturn([]);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);
        $setup_language->method('flushLanguageForInstallation')->willReturnCallback(
            static function (string $lang_key) use (&$log): void {
                $log[] = $lang_key;
            }
        );

        return $setup_language;
    }

    /**
     * installLanguages() is protected, so it is invoked via reflection, like ilSetupLanguageTest::callCheckLanguage().
     *
     * @param list<string> $language_keys
     */
    private function invokeInstallLanguages(ilLanguagesInstalledAndUpdatedObjective $objective, array $language_keys): void
    {
        (new ReflectionMethod($objective, 'installLanguages'))->invoke($objective, $language_keys);
    }

    /**
     * A mixed list of installed and not installed keys must flush and reinstall each: the new one via InstallLanguage,
     * the installed one via UpdateLanguage (both built via forSetup() around the same mocked ilSetupLanguage).
     */
    public function testInstallLanguagesActuallyFlushesBothTheNewlyInstalledAndTheAlreadyInstalledLanguage(): void
    {
        $log = [];
        $setup_language = $this->createSetupLanguageMock(['de'], $log);

        $objective = new ilLanguagesInstalledAndUpdatedObjective($setup_language);
        $this->invokeInstallLanguages($objective, ['de', 'fr']);

        // 'fr' is flushed by InstallLanguage, then 'de' by UpdateLanguage: installLanguages() runs InstallLanguage first
        $this->assertSame(['fr', 'de'], $log);
    }

    /**
     * The documented "harmless double cycle": a language just installed by InstallLanguage is flushed again by
     * UpdateLanguage, because getInstalledLanguages() now reports it as installed. Intentional, see
     * ilLanguagesInstalledAndUpdatedObjective::installLanguages().
     */
    public function testFreshlyInstalledLanguageIsImmediatelyRefreshedAgainByUpdateLanguage(): void
    {
        $log = [];
        $newly_installed = [];

        $setup_language = $this->createStub(ilSetupLanguage::class);
        $setup_language->method('getInstalledLanguages')->willReturnCallback(
            static function () use (&$newly_installed): array {
                return array_merge(['de'], $newly_installed);
            }
        );
        $setup_language->method('getAvailableLanguagesForInstallation')->willReturn([]);
        $setup_language->method('getLocalLanguages')->willReturn([]);
        $setup_language->method('getInvalidLocalLanguageFiles')->willReturn([]);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);
        $setup_language->method('flushLanguageForInstallation')->willReturnCallback(
            static function (string $lang_key) use (&$log): void {
                $log[] = $lang_key;
            }
        );
        // the write that makes 'fr' visible as installed to later getInstalledLanguages() calls, like a real INSERT
        $setup_language->method('registerInstalledLanguage')->willReturnCallback(
            static function (string $lang_key) use (&$newly_installed): void {
                $newly_installed[] = $lang_key;
            }
        );

        $objective = new ilLanguagesInstalledAndUpdatedObjective($setup_language);
        $this->invokeInstallLanguages($objective, ['de', 'fr']);

        // 'fr' is flushed by InstallLanguage; UpdateLanguage then sees ['de', 'fr'] and flushes both again
        $this->assertSame(['fr', 'de', 'fr'], $log);
    }

    /**
     * @param list<string> $flushed collects every flushed language key
     */
    private function createAchievableSetupLanguage(array $installed, array $invalid, array &$flushed, bool $default_directories = false): Stub&ilSetupLanguage
    {
        $setup_language = $this->createStub(ilSetupLanguage::class);
        $setup_language->method('getInstalledLanguages')->willReturn($installed);
        $setup_language->method('getAvailableLanguagesForInstallation')->willReturn([]);
        $setup_language->method('getLocalLanguages')->willReturn([]);
        $setup_language->method('getInvalidLocalLanguageFiles')->willReturn([]);
        $setup_language->method('checkLanguageForInstallation')->willReturnCallback(
            static fn(string $lang_key): bool => !in_array($lang_key, $invalid, true)
        );
        $setup_language->method('usesDefaultLanguageFileDirectories')->willReturn($default_directories);
        $setup_language->method('flushLanguageForInstallation')->willReturnCallback(
            static function (string $lang_key) use (&$flushed): void {
                $flushed[] = $lang_key;
            }
        );

        return $setup_language;
    }

    private function databaseStub(): ilDBInterface
    {
        return $this->createStub(ilDBInterface::class);
    }

    /**
     * An invalid language file only skips that language (reported); valid ones are still refreshed.
     */
    public function testAchieveSkipsInvalidLanguagesAndInformsTheAdministrator(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de', 'xx', 'fr'], ['xx'], $flushed);
        $messages = [];
        $io = $this->createStub(Setup\AdminInteraction::class);
        $io->method('inform')->willReturnCallback(static function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve(new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_DATABASE => $this->databaseStub(),
            Setup\Environment::RESOURCE_ADMIN_INTERACTION => $io,
        ]));

        $this->assertSame(['de', 'fr'], $flushed, 'valid languages are refreshed, the invalid one is untouched');
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('xx', $messages[0]);
        $this->assertStringNotContainsString('de', str_replace('Skipped languages', '', $messages[0]));
    }

    public function testAchieveReportsSkippedLanguagesViaErrorLogWithoutAdminInteraction(): void
    {
        $this->expectErrorLog();
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de', 'xx'], ['xx'], $flushed);

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve(new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_DATABASE => $this->databaseStub(),
        ]));

        $this->assertSame(['de'], $flushed);
        $this->assertStringContainsString('xx', (string) file_get_contents((string) ini_get('error_log')));
    }

    public function testAchieveWithOnlyInvalidLanguagesDoesNotThrowAndWritesNothing(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['xx'], ['xx'], $flushed);
        $io = $this->createMock(Setup\AdminInteraction::class);
        $io->expects($this->once())->method('inform');

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve(new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_DATABASE => $this->databaseStub(),
            Setup\Environment::RESOURCE_ADMIN_INTERACTION => $io,
        ]));

        $this->assertSame([], $flushed);
    }

    public function testAchieveDoesNotInformWhenEveryLanguageIsValid(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);
        $io = $this->createMock(Setup\AdminInteraction::class);
        $io->expects($this->never())->method('inform');

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve(new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_DATABASE => $this->databaseStub(),
            Setup\Environment::RESOURCE_ADMIN_INTERACTION => $io,
        ]));

        $this->assertSame(['de'], $flushed);
    }

    /**
     * @param list<string> $messages collects every message passed to AdminInteraction::inform()
     */
    private function informCollectingEnvironment(array &$messages): Setup\Environment
    {
        $io = $this->createStub(Setup\AdminInteraction::class);
        $io->method('inform')->willReturnCallback(static function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        return new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_DATABASE => $this->databaseStub(),
            Setup\Environment::RESOURCE_ADMIN_INTERACTION => $io,
        ]);
    }

    /**
     * Without a resolvable client data directory no PO/MO overlay can be written: worth a warning instead of silently
     * serving the modules from the database, but only if such a module exists.
     */
    public function testWarnsAboutAMissingClientDataDirectoryWhenMigratedModulesExist(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);
        $setup_language->method('getClientDataDir')->willReturn(null);
        $setup_language->method('hasMigratedModules')->willReturn(true);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $this->assertCount(1, $messages);
        $this->assertStringStartsWith('WARNING: The client data directory is not known', $messages[0]);
    }

    /**
     * Without any migrated module a missing client data directory is no warning.
     */
    public function testNoWarningAboutAMissingClientDataDirectoryWithoutAnyMigratedModule(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);
        $setup_language->method('getClientDataDir')->willReturn(null);
        $setup_language->method('hasMigratedModules')->willReturn(false);
        $io = $this->createMock(Setup\AdminInteraction::class);
        $io->expects($this->never())->method('inform');

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve(new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_DATABASE => $this->databaseStub(),
            Setup\Environment::RESOURCE_ADMIN_INTERACTION => $io,
        ]));
    }

    /**
     * With every requested language invalid, $language_keys is empty before informAboutMissingClientDataDirectory()
     * is reached: no warning, whatever hasMigratedModules() says.
     */
    public function testNoWarningAboutAMissingClientDataDirectoryWithNoValidLanguageKeys(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['xx'], ['xx'], $flushed);
        $setup_language->method('getClientDataDir')->willReturn(null);
        $setup_language->method('hasMigratedModules')->willReturn(true);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        // the "invalid language" notice is expected, the missing-client-data-dir warning is not
        $this->assertCount(1, $messages);
        $this->assertStringStartsWith('Skipped languages', $messages[0]);
    }

    /**
     * Checked before anything is written: unwritable overlay directories of the languages about to be installed or
     * updated are reported by name; the update still runs.
     */
    public function testAchieveWarnsAboutUnwritableOverlayDirectoriesBeforeWritingAndStillUpdates(): void
    {
        $flushed = [];
        $events = [];
        $setup_language = $this->createAchievableSetupLanguage(['de', 'fr'], [], $flushed);
        $setup_language->method('findUnwritableOverlayDirectories')->willReturnCallback(
            static function (array $lang_keys) use (&$events, &$flushed): array {
                $events[] = ['check', $lang_keys, count($flushed)];
                return ['/data/client/lang/components/pilot/lang', '/data/client/lang/components/other/lang'];
            }
        );
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $this->assertSame([['check', ['de', 'fr'], 0]], $events, 'checked once, for all languages, before any write');
        $this->assertSame(['de', 'fr'], $flushed, 'the update is not aborted');
        $this->assertCount(1, $messages);
        $this->assertStringStartsWith('WARNING:', $messages[0]);
        $this->assertStringContainsString('/data/client/lang/components/pilot/lang, /data/client/lang/components/other/lang', $messages[0]);
        $this->assertStringContainsString('web server user', $messages[0]);
    }

    /**
     * Invalid languages are skipped before the check.
     */
    public function testTheWritabilityCheckOnlyCoversTheLanguagesThatAreActuallyWritten(): void
    {
        $flushed = [];
        $checked = [];
        $setup_language = $this->createAchievableSetupLanguage(['de', 'xx'], ['xx'], $flushed);
        $setup_language->method('findUnwritableOverlayDirectories')->willReturnCallback(
            static function (array $lang_keys) use (&$checked): array {
                $checked[] = $lang_keys;
                return [];
            }
        );
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $this->assertSame([['de']], $checked);
    }

    /**
     * Overlays that could not be written although the check passed (race, single unwritable file) are reported from
     * the Activity results, every language once, from InstallLanguage and UpdateLanguage together.
     */
    public function testAchieveInformsAboutTheLanguagesWhoseOverlayCouldNotBeWritten(): void
    {
        $install_language = $this->createMock(\ILIAS\Language\Activities\InstallLanguage::class);
        $install_language->expects($this->once())->method('perform')->willReturn([
            'overlay_write_failed_language_keys' => ['fr'],
        ]);
        $update_language = $this->createMock(\ILIAS\Language\Activities\UpdateLanguage::class);
        $update_language->expects($this->once())->method('perform')->willReturn([
            'overlay_write_failed_language_keys' => ['de', 'fr'],
        ]);
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de', 'fr'], [], $flushed);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language, $install_language, $update_language))
            ->achieve($this->informCollectingEnvironment($messages));

        $this->assertCount(1, $messages);
        $this->assertStringStartsWith('WARNING:', $messages[0]);
        $this->assertStringContainsString('the languages fr, de ', $messages[0]);
    }

    /**
     * Customizing entries left out for disallowed markup (LanguageInstallationManager::getSkippedInvalidMarkupEntries())
     * are reported as a WARNING per language; the install/update is not aborted.
     */
    public function testAchieveWarnsAboutSkippedInvalidMarkupEntriesPerLanguage(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de', 'fr'], [], $flushed);
        $setup_language->method('getSkippedInvalidMarkupEntries')->willReturn([
            'de' => ['common#:#greeting' => ['tag <script>']],
            'fr' => ['common#:#farewell' => ['comment " i"']],
        ]);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $warnings = array_values(array_filter($messages, static fn(string $m): bool => str_starts_with($m, 'WARNING:')));
        $this->assertCount(2, $warnings);
        $this->assertTrue(
            (bool) array_filter($warnings, static fn(string $m): bool => str_contains($m, 'de') && str_contains($m, 'common#:#greeting'))
        );
        $this->assertTrue(
            (bool) array_filter($warnings, static fn(string $m): bool => str_contains($m, 'fr') && str_contains($m, 'common#:#farewell'))
        );
    }

    public function testAchieveWarnsAboutSkippedDuplicateEntriesPerLanguage(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de', 'fr'], [], $flushed);
        $setup_language->method('getSkippedDuplicateEntries')->willReturn([
            'de' => ['common#:#greeting' => ['mme: greeting'], 'tos#:#x' => ['mme: x']],
        ]);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $warnings = array_values(array_filter($messages, static fn(string $m): bool => str_contains($m, 'were not applied')));
        $this->assertCount(1, $warnings);
        $this->assertStringStartsWith('WARNING: Customizing entries of language de ', $warnings[0]);
        $this->assertStringContainsString('common#:#greeting, tos#:#x', $warnings[0]);
        $this->assertStringContainsString('another module', $warnings[0]);
    }

    /** Modules left out because their overlay cannot be read are named per language; without any there is no such warning. */
    public function testAchieveWarnsAboutModulesWithAnUnreadableOverlayPerLanguage(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de', 'fr'], [], $flushed);
        $setup_language->method('getSkippedUnreadableOverlays')->willReturn(['de' => ['common', 'tos']]);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $warnings = array_values(array_filter($messages, static fn(string $m): bool => str_contains($m, 'cannot be read for the modules')));
        $this->assertCount(1, $warnings);
        $this->assertStringStartsWith('WARNING:', $warnings[0]);
        $this->assertStringContainsString('language de', $warnings[0]);
        $this->assertStringContainsString('common, tos', $warnings[0]);
        $this->assertStringContainsString('rollback copy in lng_data/lng_modules is incomplete', $warnings[0]);
    }

    public function testAchieveWarnsAboutOverlaysOfUnshippedModulesPerLanguage(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de', 'fr'], [], $flushed);
        $setup_language->method('getOverlaysOfUnshippedModules')->willReturn(['de' => ['common', 'tos']]);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $this->assertCount(1, $messages);
        $this->assertStringStartsWith('WARNING:', $messages[0]);
        $this->assertStringContainsString('common, tos', $messages[0]);
        $this->assertStringContainsString('language de', $messages[0]);
        $this->assertStringContainsString('taken-over.de.json', $messages[0]);
    }

    public function testAchieveWarnsAboutUnrecordedTakenOverModulesPerLanguage(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de', 'fr'], [], $flushed);
        $setup_language->method('getUnrecordedTakenOverModules')->willReturn(['fr' => ['common']]);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $this->assertCount(1, $messages);
        $this->assertStringStartsWith('WARNING:', $messages[0]);
        $this->assertStringContainsString('language fr', $messages[0]);
        $this->assertStringContainsString('modules common', $messages[0]);
        $this->assertStringContainsString('taken-over.fr.json', $messages[0]);
    }

    /**
     * Legacy local changes written before the markup check are never modified or removed, only reported once per run
     * as a WARNING naming the entry.
     */
    public function testAchieveWarnsAboutInvalidMarkupInPreExistingLocalChanges(): void
    {
        $this->expectErrorLog();
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);
        $setup_language->method('getSkippedInvalidMarkupEntries')->willReturn([]);
        $setup_language->method('getClientDataDir')->willReturn(null);
        $setup_language->method('getLocalChanges')->willReturn([
            'common' => ['greeting' => '<script>alert(1)</script>'],
        ]);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $warnings = array_values(array_filter(
            $messages,
            static fn(string $m): bool => str_contains($m, 'Local changes') && str_contains($m, 'contain HTML')
        ));
        $this->assertCount(1, $warnings);
        $this->assertStringContainsString('common#:#greeting', $warnings[0]);
        $this->assertStringContainsString('de', $warnings[0]);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: bool}> module of the lng_data change, source
     *         ("overlay": readable overlay, "record": taken over, none), value of the overlay entry, whether it is reported
     */
    public static function markupCheckSources(): array
    {
        return [
            'module with a readable overlay: lng_data is not read' => ['pilot', 'overlay', 'Hallo', false],
            'module taken over: lng_data is not read' => ['pilot', 'record', 'Hallo', false],
            'the overlay itself holds disallowed markup' => ['pilot', 'overlay', '<script>x</script>', true],
            'module before the takeover: lng_data counts' => ['pilot', 'none', 'Hallo', true],
            'module of no component: lng_data counts' => ['rep_robj_xtst', 'none', 'Hallo', true],
        ];
    }

    /** Disallowed markup in lng_data only counts for modules before the takeover and for plugins; otherwise the overlay is checked. */
    #[DataProvider('markupCheckSources')]
    public function testTheMarkupCheckOfLocalChangesReadsLngDataOnlyWhereItIsStillTheSource(
        string $lng_data_module,
        string $source,
        string $overlay_value,
        bool $expect_warning
    ): void {
        if ($expect_warning) {
            $this->expectErrorLog();
        }
        $root = (string) realpath(__DIR__ . '/../../../../../');
        $relative = 'components/ILIAS/Language/tests/Setup/tmp-markup-' . bin2hex(random_bytes(4)) . '/';
        $client_data_dir = sys_get_temp_dir() . '/ilias_markup_check_' . bin2hex(random_bytes(4));
        mkdir($client_data_dir, 0775, true);
        $directory = MigratedPoFixture::directory('pilot', $relative);
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $directory);
        try {
            MigratedPoFixture::writePo($root . '/' . $relative . 'pilot_de.po', MigratedPoFixture::catalog('pilot', ['greeting' => 'Hallo']));
            if ($source === 'overlay') {
                MigratedPoFixture::writePair(
                    MigratedPoFixture::overlayBase($client_data_dir, $directory, 'de'),
                    MigratedPoFixture::catalog('pilot', ['greeting' => ['value' => $overlay_value, 'original' => 'Hallo', 'local_change' => '2025-05-05T05:05:05Z']])
                );
            } elseif ($source === 'record') {
                MigratedLanguageFileSync::markLocalChangesTakenOver($client_data_dir, 'de', ['pilot']);
            }
            $flushed = [];
            $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);
            $setup_language->method('getSkippedInvalidMarkupEntries')->willReturn([]);
            $setup_language->method('getClientDataDir')->willReturn($client_data_dir);
            $setup_language->method('getLanguageFileDirectoryManager')->willReturn($manager);
            $setup_language->method('getLocalChanges')->willReturn([$lng_data_module => ['farewell' => '<script>alert(1)</script>']]);
            $messages = [];

            (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

            $warnings = array_values(array_filter($messages, static fn(string $m): bool => str_contains($m, 'Local changes')));
            $this->assertCount($expect_warning ? 1 : 0, $warnings);
            if ($expect_warning) {
                // the overlay's own entry, or the lng_data row of a module without overlay and record
                $this->assertStringContainsString($source === 'overlay' ? 'pilot#:#greeting' : $lng_data_module . '#:#farewell', $warnings[0]);
                $this->assertSame($source !== 'overlay', str_contains($warnings[0], '#:#farewell'));
            }
        } finally {
            MigratedPoFixture::removeDirectory($root . '/' . $relative);
            MigratedPoFixture::removeDirectory($client_data_dir);
        }
    }

    /**
     * A legacy local change that is still allowed is not reported.
     */
    public function testAchieveDoesNotWarnAboutAnAllowedPreExistingLocalChange(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);
        $setup_language->method('getSkippedInvalidMarkupEntries')->willReturn([]);
        $setup_language->method('getClientDataDir')->willReturn(null);
        $setup_language->method('getLocalChanges')->willReturn(['common' => ['greeting' => 'Hallo, geändert']]);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $this->assertSame(
            [],
            array_values(array_filter($messages, static fn(string $m): bool => str_contains($m, 'Local changes')))
        );
    }

    /**
     * No skipped entries, no such warning.
     */
    public function testAchieveDoesNotWarnAboutInvalidMarkupWithoutAnySkippedEntry(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);
        $setup_language->method('getSkippedInvalidMarkupEntries')->willReturn([]);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $this->assertSame(
            [],
            array_values(array_filter($messages, static fn(string $m): bool => str_contains($m, 'markup that is not allowed')))
        );
    }

    /**
     * installLanguages() returns the languages with an unwritten overlay; a missing result key counts as none,
     * duplicates are removed.
     */
    public function testInstallLanguagesReturnsTheDeduplicatedLanguagesWithAnUnwrittenOverlay(): void
    {
        $install_language = $this->createStub(\ILIAS\Language\Activities\InstallLanguage::class);
        $install_language->method('perform')->willReturn(['overlay_write_failed_language_keys' => ['fr', 'de']]);
        $update_language = $this->createStub(\ILIAS\Language\Activities\UpdateLanguage::class);
        $update_language->method('perform')->willReturn(['overlay_write_failed_language_keys' => ['de', 'it']]);
        $flushed = [];
        $objective = new ilLanguagesInstalledAndUpdatedObjective(
            $this->createAchievableSetupLanguage([], [], $flushed),
            $install_language,
            $update_language
        );

        $this->assertSame(
            ['fr', 'de', 'it'],
            (new ReflectionMethod($objective, 'installLanguages'))->invoke($objective, ['de', 'fr', 'it'])
        );

        $install_without_field = $this->createStub(\ILIAS\Language\Activities\InstallLanguage::class);
        $install_without_field->method('perform')->willReturn([]);
        $update_without_field = $this->createStub(\ILIAS\Language\Activities\UpdateLanguage::class);
        $update_without_field->method('perform')->willReturn([]);
        $objective = new ilLanguagesInstalledAndUpdatedObjective(
            $this->createAchievableSetupLanguage([], [], $flushed),
            $install_without_field,
            $update_without_field
        );
        $this->assertSame([], (new ReflectionMethod($objective, 'installLanguages'))->invoke($objective, ['de']));
    }

    /**
     * With only the default directories no UpdateLanguage runs, so only InstallLanguage's result contributes.
     */
    public function testInstallLanguagesWithDefaultDirectoriesOnlyReportsTheInstallResult(): void
    {
        $install_language = $this->createStub(\ILIAS\Language\Activities\InstallLanguage::class);
        $install_language->method('perform')->willReturn(['overlay_write_failed_language_keys' => ['fr']]);
        $update_language = $this->createMock(\ILIAS\Language\Activities\UpdateLanguage::class);
        $update_language->expects($this->never())->method('perform');
        $flushed = [];
        $objective = new ilLanguagesInstalledAndUpdatedObjective(
            $this->createAchievableSetupLanguage([], [], $flushed, true),
            $install_language,
            $update_language
        );

        $this->assertSame(['fr'], (new ReflectionMethod($objective, 'installLanguages'))->invoke($objective, ['fr']));
    }

    /**
     * Writable is not enough: a Setup run as another user than the client data directory's owner is warned about.
     * As root the directory is handed to a non-root user, otherwise "/" (root-owned) serves as another user's directory,
     * which fires the root-specific branch ("$owner === 0", "belongs to root") instead; the assertions therefore
     * branch on the observed owner.
     */
    public function testAchieveWarnsWhenSetupDoesNotRunAsTheOwnerOfTheClientDataDirectory(): void
    {
        if (!function_exists('posix_geteuid')) {
            $this->markTestSkipped('The owner check needs the POSIX extension.');
        }
        $datadir = sys_get_temp_dir() . '/ilias_obj_owner_' . bin2hex(random_bytes(4));
        mkdir($datadir);
        try {
            $foreign_dir = $this->directoryOfAnotherUser($datadir);
            $flushed = [];
            $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);
            $setup_language->method('getClientDataDir')->willReturn($foreign_dir);
            // the owner-mismatch warning is only given when there is a migrated module's overlay to protect
            $setup_language->method('hasMigratedModules')->willReturn(true);
            $foreign_owner = fileowner($foreign_dir);
            $messages = [];

            (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));
        } finally {
            MigratedPoFixture::removeDirectory($datadir);
        }

        $this->assertNotSame(posix_geteuid(), $foreign_owner);
        $this->assertCount(1, $messages);
        $this->assertStringStartsWith('WARNING: Setup runs as user id ' . posix_geteuid() . ',', $messages[0]);
        if ($foreign_owner === 0) {
            $this->assertStringContainsString('belongs to root', $messages[0]);
        } else {
            $this->assertStringContainsString($foreign_dir . ' belongs to user id ' . $foreign_owner . '.', $messages[0]);
        }
        $this->assertSame(['de'], $flushed);
    }

    private function directoryOfAnotherUser(string $own_directory): string
    {
        if (posix_geteuid() === 0) {
            if (!@chown($own_directory, 65534)) {
                $this->markTestSkipped('Cannot hand a directory to another user.');
            }
            clearstatcache();
            return $own_directory;
        }
        if (fileowner('/') === posix_geteuid()) {
            $this->markTestSkipped('No directory of another user available.');
        }

        return '/';
    }

    public function testAchieveDoesNotWarnWhenSetupRunsAsTheOwnerOfTheClientDataDirectory(): void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('A Setup run as root is always warned about (running Setup as root is not supported).');
        }
        $datadir = sys_get_temp_dir() . '/ilias_obj_owner_' . bin2hex(random_bytes(4));
        mkdir($datadir);
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);
        $setup_language->method('getClientDataDir')->willReturn($datadir);
        $io = $this->createMock(Setup\AdminInteraction::class);
        $io->expects($this->never())->method('inform');

        try {
            (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve(new Setup\ArrayEnvironment([
                Setup\Environment::RESOURCE_DATABASE => $this->databaseStub(),
                Setup\Environment::RESOURCE_ADMIN_INTERACTION => $io,
            ]));
        } finally {
            MigratedPoFixture::removeDirectory($datadir);
        }
    }

    /**
     * The owner warning comes in addition to the unwritable-directories warning: that one concerns this run, the
     * owner mismatch every later write by the web server.
     */
    public function testTheOwnerWarningIsGivenInAdditionToTheUnwritableDirectoriesWarning(): void
    {
        if (!function_exists('posix_geteuid')) {
            $this->markTestSkipped('The owner check needs the POSIX extension.');
        }
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);
        $setup_language->method('findUnwritableOverlayDirectories')->willReturn(['/x/lang/components/pilot/lang']);
        $setup_language->method('getClientDataDir')->willReturn('/');
        // the owner-mismatch warning is only given when there is a migrated module's overlay to protect
        $setup_language->method('hasMigratedModules')->willReturn(true);
        $messages = [];

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve($this->informCollectingEnvironment($messages));

        $this->assertCount(2, $messages);
        $this->assertStringContainsString('/x/lang/components/pilot/lang', $messages[0]);
        $this->assertStringStartsWith('WARNING: Setup runs as user id ' . posix_geteuid() . ',', $messages[1]);
        $this->assertStringContainsString('chown -R ', $messages[1]);
    }

    /**
     * The owner-mismatch warning only protects a migrated module's overlay: without a migrated module it is not given.
     */
    public function testNoOwnerWarningWithoutAnyMigratedModule(): void
    {
        if (!function_exists('posix_geteuid')) {
            $this->markTestSkipped('The owner check needs the POSIX extension.');
        }
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);
        $setup_language->method('getClientDataDir')->willReturn('/');
        $setup_language->method('hasMigratedModules')->willReturn(false);
        $io = $this->createMock(Setup\AdminInteraction::class);
        $io->expects($this->never())->method('inform');

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve(new Setup\ArrayEnvironment([
            Setup\Environment::RESOURCE_DATABASE => $this->databaseStub(),
            Setup\Environment::RESOURCE_ADMIN_INTERACTION => $io,
        ]));
    }

    /**
     * An instance with only the default directories (plugin Setup Objectives) must never refresh an installed
     * language: that would drop every component-contributed module from lng_data.
     */
    public function testAnInstanceWithDefaultDirectoriesInstallsMissingLanguagesButNeverRefreshes(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed, true);

        $this->invokeInstallLanguages(new ilLanguagesInstalledAndUpdatedObjective($setup_language), ['de', 'fr']);

        $this->assertSame(['fr'], $flushed);
    }

    public function testInstallLanguagesWithoutKeysDoesNothing(): void
    {
        $flushed = [];
        $setup_language = $this->createAchievableSetupLanguage(['de'], [], $flushed);

        $this->invokeInstallLanguages(new ilLanguagesInstalledAndUpdatedObjective($setup_language), []);

        $this->assertSame([], $flushed);
    }

    private function iniStub(string $datadir): ilIniFile
    {
        $ini = $this->createStub(ilIniFile::class);
        $ini->method('readVariable')->willReturnCallback(
            static fn(string $group, string $name): string => $group === 'clients' && $name === 'datadir' ? $datadir : ''
        );
        return $ini;
    }

    /**
     * @param array<string, mixed> $resources
     * @return list<?string> every value setClientDataDir() was called with
     */
    private function achieveCapturingClientDataDir(array $resources): array
    {
        $flushed = [];
        $calls = [];
        $setup_language = $this->createAchievableSetupLanguage([], [], $flushed);
        $setup_language->method('setClientDataDir')->willReturnCallback(
            static function (?string $dir) use (&$calls): void {
                $calls[] = $dir;
            }
        );

        (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->achieve(new Setup\ArrayEnvironment(
            [Setup\Environment::RESOURCE_DATABASE => $this->databaseStub()] + $resources
        ));

        return $calls;
    }

    public function testAchieveTakesTheClientDataDirFromTheSetupEnvironment(): void
    {
        $datadir = sys_get_temp_dir() . '/ilias_obj_cdd_' . bin2hex(random_bytes(4));
        mkdir($datadir . '/myclient', 0775, true);

        try {
            $calls = $this->achieveCapturingClientDataDir([
                Setup\Environment::RESOURCE_ILIAS_INI => $this->iniStub($datadir . '/'),
                Setup\Environment::RESOURCE_CLIENT_ID => 'myclient',
            ]);
        } finally {
            MigratedPoFixture::removeDirectory($datadir);
        }

        $this->assertSame([$datadir . '/myclient'], $calls);
    }

    /**
     * @param array<string, mixed> $resources
     */
    #[DataProvider('incompleteClientEnvironments')]
    public function testAchieveKeepsTheDefaultResolutionWithoutAUsableClientDataDir(\Closure $resources): void
    {
        $datadir = sys_get_temp_dir() . '/ilias_obj_cdd_' . bin2hex(random_bytes(4));
        mkdir($datadir . '/myclient', 0775, true);

        try {
            $calls = $this->achieveCapturingClientDataDir($resources($this->iniStub($datadir)));
        } finally {
            MigratedPoFixture::removeDirectory($datadir);
        }

        $this->assertSame([], $calls, 'setClientDataDir() must not be called (not even with null)');
    }

    public static function incompleteClientEnvironments(): array
    {
        return [
            'no ini, no client id' => [static fn(ilIniFile $ini): array => []],
            'no client id' => [static fn(ilIniFile $ini): array => [Setup\Environment::RESOURCE_ILIAS_INI => $ini]],
            'no ini' => [static fn(ilIniFile $ini): array => [Setup\Environment::RESOURCE_CLIENT_ID => 'myclient']],
            'client directory does not exist' => [static fn(ilIniFile $ini): array => [
                Setup\Environment::RESOURCE_ILIAS_INI => $ini,
                Setup\Environment::RESOURCE_CLIENT_ID => 'other',
            ]],
            'unsafe client id' => [static fn(ilIniFile $ini): array => [
                Setup\Environment::RESOURCE_ILIAS_INI => $ini,
                Setup\Environment::RESOURCE_CLIENT_ID => '../myclient',
            ]],
        ];
    }

    private static function directory(string $prefix, string $path, bool $local = false): LanguageFileDirectory
    {
        return MigratedPoFixture::directory($prefix, $path, $local);
    }

    private static function manager(LanguageFileDirectory ...$directories): LanguageFileDirectoryManager
    {
        return new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            new MainLanguageFileDirectory(),
            ...$directories
        );
    }

    private function hashFor(?LanguageFileDirectoryManager $manager): string
    {
        $setup_language = new ilSetupLanguage('en', $manager);
        return (new ilLanguagesInstalledAndUpdatedObjective(
            $setup_language,
            $this->createStub(\ILIAS\Language\Activities\InstallLanguage::class),
            $this->createStub(\ILIAS\Language\Activities\UpdateLanguage::class)
        ))->getHash();
    }

    public function testTheHashIsStableForTheSameDirectoryConfiguration(): void
    {
        $build = static fn(): LanguageFileDirectoryManager => new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            new MainLanguageFileDirectory(),
            self::directory('tos', 'components/ILIAS/TermsOfService/lang/')
        );

        $this->assertSame($this->hashFor($build()), $this->hashFor($build()));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $this->hashFor($build()));
    }

    /**
     * The default-directories instance never refreshes, so it must not collide with one built with an equal explicit
     * configuration.
     */
    public function testTheDefaultDirectoriesHashDiffersFromAnExplicitlyEqualConfiguration(): void
    {
        $this->assertNotSame(
            $this->hashFor(null),
            $this->hashFor(new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()))
        );
    }

    /**
     * Each part of a directory's configuration (class, prefix, path, suffix) and a contributed directory must change
     * the hash, otherwise the Setup keeps only one of two differently configured objectives.
     */
    #[DataProvider('differingConfigurations')]
    public function testTheHashDiffersForDifferentDirectoryConfigurations(\Closure $a, \Closure $b): void
    {
        $this->assertNotSame($this->hashFor($a()), $this->hashFor($b()));
    }

    public static function differingConfigurations(): array
    {
        return [
            'default vs. with contributed directory' => [
                static fn() => null,
                static fn() => self::manager(self::directory('tos', 'components/ILIAS/TermsOfService/lang/')),
            ],
            'prefix differs' => [
                static fn() => self::manager(self::directory('tos', 'components/x/lang/')),
                static fn() => self::manager(self::directory('file', 'components/x/lang/')),
            ],
            'path differs' => [
                static fn() => self::manager(self::directory('tos', 'components/x/lang/')),
                static fn() => self::manager(self::directory('tos', 'components/y/lang/')),
            ],
            'suffix differs' => [
                static fn() => self::manager(self::directory('tos', 'components/x/lang/')),
                static fn() => self::manager(self::directory('tos', 'components/x/lang/', true)),
            ],
            'only the class differs' => [
                static fn() => self::manager(new \ILIAS\Language\ComponentTranslation\ComponentLanguageFileDirectory(new \ILIAS\Language(), 'tos')),
                static function (): LanguageFileDirectoryManager {
                    $component = new \ILIAS\Language\ComponentTranslation\ComponentLanguageFileDirectory(new \ILIAS\Language(), 'tos');
                    return self::manager(self::directory('tos', $component->getPath()));
                },
            ],
        ];
    }

    public function testTheBuildIsAPreconditionOnlyWithDirectoriesOfTheComponentGraph(): void
    {
        $environment = $this->createStub(Setup\Environment::class);

        $default = new ilLanguagesInstalledAndUpdatedObjective(new ilSetupLanguage('en'));
        $this->assertCount(1, $default->getPreconditions($environment));
        $this->assertInstanceOf(ilDatabaseInitializedObjective::class, $default->getPreconditions($environment)[0]);

        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory());
        $setup_language = new ilSetupLanguage('en', $manager);
        $preconditions = (new ilLanguagesInstalledAndUpdatedObjective($setup_language))->getPreconditions($environment);

        $this->assertCount(2, $preconditions);
        $this->assertInstanceOf(ilDatabaseInitializedObjective::class, $preconditions[0]);
        $this->assertSame(
            (new ilLanguageSetupAgent(
                $this->createStub(\ILIAS\Refinery\Factory::class),
                $setup_language,
                $this->createStub(\ILIAS\Language\Activities\InstallLanguage::class),
                $this->createStub(\ILIAS\Language\Activities\UpdateLanguage::class)
            ))->getBuildObjective()->getHash(),
            $preconditions[1]->getHash(),
            'the same objective as the build of the agent, so Setup achieves it once'
        );
    }
}
