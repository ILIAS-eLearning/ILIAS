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
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Guards the database handling of ilLanguagesInstalledAndUpdatedObjective.
 *
 * The class used to overwrite $GLOBALS['ilDB'] with the Setup-provided
 * database for the duration of the install and restore it afterwards, with a
 * "@todo remove this once ilSetupLanguage supports proper DI" attached. That
 * is gone: ilSetupLanguage resolves its database lazily, so setDbHandler()
 * is authoritative for everything the install path touches. These tests pin
 * both halves of that down, because a regression would be silent - the
 * global fallback would simply take over again.
 */
class ilLanguagesInstalledAndUpdatedObjectiveTest extends TestCase
{
    private bool $had_global_db;
    private mixed $previous_global_db = null;

    protected function setUp(): void
    {
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
    private function createSetupLanguageMock(array $installed_language_keys, array &$log): MockObject&ilSetupLanguage
    {
        $setup_language = $this->createMock(ilSetupLanguage::class);
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

        $setup_language = $this->createMock(ilSetupLanguage::class);
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
}
