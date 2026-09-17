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
        // These tests deliberately manipulate $GLOBALS['ilDB'] - one replaces
        // it, one removes it - so it has to be restored afterwards. Tests run
        // in random order and other tests in this component do rely on the
        // global.
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
        $db = $this->createMock(ilDBInterface::class);
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
            return $this->createMock(ilDBStatement::class);
        });

        // A single installed language, then exhausted. $rows must be captured
        // by reference - a by-value capture would hand out the same row for
        // ever and the "while ($row = fetchObject())" loops would not end.
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

    /**
     * @param list<string> $log collects the language key every write method
     *        was actually called with, in call order
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
     * installLanguages() is protected, so it is invoked here via reflection -
     * the same approach already used by ilSetupLanguageTest::callCheckLanguage()
     * for a protected method on a sibling class in this component.
     *
     * @param list<string> $language_keys
     */
    private function invokeInstallLanguages(ilLanguagesInstalledAndUpdatedObjective $objective, array $language_keys): void
    {
        (new ReflectionMethod($objective, 'installLanguages'))->invoke($objective, $language_keys);
    }

    /**
     * The central integration guarantee of installLanguages(): a single call
     * with a mixed list of already-installed and not-yet-installed language
     * keys must actually flush+reinstall each of them - the not-yet-installed
     * one via InstallLanguage, the already-installed one via UpdateLanguage -
     * not merely report them as belonging to the correct bucket. Both
     * Activities are built via their forSetup() factory here (the
     * constructor's default when no Activity is injected), sharing the same
     * mocked ilSetupLanguage.
     */
    public function testInstallLanguagesActuallyFlushesBothTheNewlyInstalledAndTheAlreadyInstalledLanguage(): void
    {
        $log = [];
        // 'de' is already installed, 'fr' is not.
        $setup_language = $this->createSetupLanguageMock(['de'], $log);

        $objective = new ilLanguagesInstalledAndUpdatedObjective($setup_language);
        $this->invokeInstallLanguages($objective, ['de', 'fr']);

        // 'fr' is flushed once by InstallLanguage (fresh install); 'de' is
        // flushed once by UpdateLanguage (refresh of an already installed
        // language) - in that order, since installLanguages() runs
        // InstallLanguage before UpdateLanguage.
        $this->assertSame(['fr', 'de'], $log);
    }

    /**
     * Regression guard for the documented "harmless double cycle": a
     * language that InstallLanguage has just installed is, within the very
     * same installLanguages() call, immediately flushed a second time by
     * UpdateLanguage - because by the time UpdateLanguage::perform() queries
     * getInstalledLanguages() again, the just-installed language is now
     * reported as installed too, exactly like a real database would behave
     * after InstallLanguage's write. This is intentional (see
     * ilLanguagesInstalledAndUpdatedObjective::installLanguages() docblock),
     * not wasted work accidentally introduced by the two-Activity split.
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
        // The DB write that makes 'fr' visible as installed to every
        // subsequent getInstalledLanguages() call, exactly like a real
        // INSERT into object_data would.
        $setup_language->method('registerInstalledLanguage')->willReturnCallback(
            static function (string $lang_key) use (&$newly_installed): void {
                $newly_installed[] = $lang_key;
            }
        );

        $objective = new ilLanguagesInstalledAndUpdatedObjective($setup_language);
        $this->invokeInstallLanguages($objective, ['de', 'fr']);

        // 'fr': flushed once by InstallLanguage (fresh install). Then
        // UpdateLanguage runs against the now-current installed list
        // (['de', 'fr']) and flushes both again - 'de' because it always
        // was installed, 'fr' because InstallLanguage just registered it.
        $this->assertSame(['fr', 'de', 'fr'], $log);
    }
}
