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

namespace ILIAS\Language\ComponentTranslation;

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use MigratedPoFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * NativeGettext changes process-wide state (LC_MESSAGES, LANGUAGE, LOCPATH, LC_ALL): what activate() leaves behind and
 * restore() gives back, losing and regaining the settings to other code, a build native gettext cannot use, lookups of
 * messages that cannot be looked up.
 *
 * The thread-safe (ZTS) branch is reached through NativeGettext::useThreadSafeForTests().
 */
class NativeGettextTest extends TestCase
{
    use \RestoresProcessState;

    private const string DOMAIN = 'ntg.de';
    private const string REAL_LOCALE = 'de_DE.UTF-8';

    private string $root;
    private string $build_directory;
    private string $locale_directory;

    protected function setUp(): void
    {
        MigratedPoFixture::resetRuntime();
        $this->root = sys_get_temp_dir() . '/ilias_native_gettext_' . bin2hex(random_bytes(6));
        mkdir($this->root, 0775, true);

        $catalog = MigratedPoFixture::catalog('ntg', ['greeting' => 'Hallo']);
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $item = new TranslationEntry('ntg', 'item');
        $item->setPlural('items', ['Eintrag', 'Einträge']);
        $catalog->add($item);
        MigratedPoFixture::writePo($this->root . '/ntg/ntg_de.po', $catalog);
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), MigratedPoFixture::directory('ntg', 'ntg/'));
        $build = MigratedPoFixture::build($manager, $this->root)['build'];
        // the build checked itself through native gettext in this process, see ShippedLanguageFilesCompiledObjective
        NativeGettext::restore();
        $this->assertNotNull($build);
        $this->build_directory = MigratedLanguageFilePaths::buildDirectory(MigratedPoFixture::artifactDirectory(), $build);
        $this->locale_directory = MigratedLanguageFilePaths::buildLocaleDirectory($this->build_directory);
    }

    protected function tearDown(): void
    {
        NativeGettext::resetForTests();
        MigratedPoFixture::removeDirectory($this->root);
        MigratedPoFixture::resetRuntime();
    }

    private function activateBuild(): void
    {
        $this->assertTrue(NativeGettext::activate($this->locale_directory), (string) NativeGettext::getProblem());
        $this->assertTrue(NativeGettext::bind(self::DOMAIN, $this->build_directory));
    }

    /**
     * @return iterable<string, array{string|false, string|false}>
     */
    public static function previousProcessSettings(): iterable
    {
        yield 'nothing set before' => [false, false];
        yield 'LANGUAGE set before' => ['de:fr', false];
        yield 'LOCPATH set before' => [false, '/nonexistent/locpath'];
        yield 'both set before' => ['en', '/nonexistent/locpath'];
    }

    #[DataProvider('previousProcessSettings')]
    public function testActivateServesTheBuildAndRestoreGivesTheProcessStateBack(string|false $language, string|false $locpath): void
    {
        MigratedPoFixture::requireGlibcLocale($this);
        putenv($language === false ? 'LANGUAGE' : 'LANGUAGE=' . $language);
        putenv($locpath === false ? 'LOCPATH' : 'LOCPATH=' . $locpath);
        $messages_before = setlocale(LC_MESSAGES, '0');

        $this->activateBuild();

        $this->assertTrue(NativeGettext::isActive());
        $this->assertSame(NativeGettext::LOCALE_NAME, setlocale(LC_MESSAGES, '0'));
        $this->assertSame(NativeGettext::CATALOG_LANGUAGE, getenv('LANGUAGE'));
        $this->assertSame($locpath, getenv('LOCPATH'), 'LOCPATH only lives for the setlocale() call (no system locale is found while it is set)');
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));

        NativeGettext::restore();

        $this->assertFalse(NativeGettext::isActive());
        $this->assertSame($messages_before, setlocale(LC_MESSAGES, '0'));
        $this->assertSame($language, getenv('LANGUAGE'));
        $this->assertSame($locpath, getenv('LOCPATH'));
        $this->assertSame('greeting', NativeGettext::translate(self::DOMAIN, 'greeting'), 'without the locale native gettext answers with the message id');
    }

    public function testRestoreLeavesASettingOtherCodeChangedSinceAsItIs(): void
    {
        $this->activateBuild();
        setlocale(LC_MESSAGES, 'C');
        putenv('LANGUAGE=fr');

        NativeGettext::restore();

        $this->assertSame('C', setlocale(LC_MESSAGES, '0'));
        $this->assertSame('fr', getenv('LANGUAGE'));
    }

    public function testRestoreWithoutActivationChangesNothing(): void
    {
        putenv('LANGUAGE=de');
        $messages = setlocale(LC_MESSAGES, '0');

        NativeGettext::restore();

        $this->assertSame('de', getenv('LANGUAGE'));
        $this->assertSame($messages, setlocale(LC_MESSAGES, '0'));
    }

    private function requireSystemLocale(): void
    {
        $before = setlocale(LC_MESSAGES, '0');
        $found = setlocale(LC_MESSAGES, NativeGettext::SYSTEM_LOCALES);
        setlocale(LC_MESSAGES, $before);
        if ($found === false) {
            $this->markTestSkipped('none of ' . implode(', ', NativeGettext::SYSTEM_LOCALES) . ' is installed');
        }
    }

    public function testSystemLocaleBranchTranslatesWithoutLocaleDirectoryAndLeavesLocpathAlone(): void
    {
        $this->requireSystemLocale();
        NativeGettext::useSystemLocaleForTests(true);
        putenv('LOCPATH');
        $this->assertDirectoryDoesNotExist($this->root . '/no-locale');

        $this->assertTrue(NativeGettext::activate($this->root . '/no-locale'), (string) NativeGettext::getProblem());
        $this->assertTrue(NativeGettext::bind(self::DOMAIN, $this->build_directory));

        $this->assertTrue(NativeGettext::isActive());
        $this->assertNotSame(NativeGettext::LOCALE_NAME, setlocale(LC_MESSAGES, '0'));
        $this->assertSame(NativeGettext::CATALOG_LANGUAGE, getenv('LANGUAGE'));
        $this->assertFalse(getenv('LOCPATH'), 'the system branch never sets LOCPATH');
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));
    }

    /**
     * @return iterable<string, array{string|false, string|false}>
     */
    public static function processSettingsBeforeSystemLocaleActivation(): iterable
    {
        yield 'C locale' => ['C', 'de'];
        yield 'the system locale itself, LANGUAGE unset' => ['system', false];
        yield 'a real locale, which stays' => [self::REAL_LOCALE, 'de'];
    }

    #[DataProvider('processSettingsBeforeSystemLocaleActivation')]
    public function testSystemLocaleIsOnlyActiveAfterActivateAndRestoreGivesTheProcessStateBack(string $locale, string|false $language): void
    {
        $this->requireSystemLocale();
        NativeGettext::useSystemLocaleForTests(true);
        $kept = $locale === self::REAL_LOCALE;
        if ($kept) {
            $this->requireRealLocale();
        }
        $locale = $locale === 'system' ? (string) setlocale(LC_MESSAGES, NativeGettext::SYSTEM_LOCALES) : (string) setlocale(LC_MESSAGES, $locale);
        putenv($language === false ? 'LANGUAGE' : 'LANGUAGE=' . $language);
        $this->assertFalse(NativeGettext::isActive(), 'a locale set by someone else is not our activation');

        $this->activateSystemLocale();
        $this->assertTrue(NativeGettext::isActive());
        if ($kept) {
            $this->assertSame($locale, setlocale(LC_MESSAGES, '0'), 'a real locale is not replaced');
        }

        NativeGettext::restore();

        $this->assertFalse(NativeGettext::isActive());
        $this->assertSame($locale, setlocale(LC_MESSAGES, '0'));
        $this->assertSame($language, getenv('LANGUAGE'));
    }

    /**
     * @return iterable<string, array{\Closure(): void}>
     */
    public static function foreignChangesInTheSystemLocaleBranch(): iterable
    {
        yield from self::foreignChanges();
        yield 'LC_ALL environment variable set to C' => [static function (): void {
            putenv('LC_ALL=C');
        }];
        yield 'LC_ALL environment variable removed' => [static function (): void {
            putenv('LC_ALL');
        }];
    }

    #[DataProvider('foreignChangesInTheSystemLocaleBranch')]
    public function testReactivateIfLostRepairsWhatOtherCodeChangedInTheSystemLocaleBranch(\Closure $change): void
    {
        $this->requireSystemLocale();
        NativeGettext::useSystemLocaleForTests(true);
        $this->activateSystemLocale();
        $change();
        $this->assertFalse(NativeGettext::isActive(), 'precondition');

        $this->assertTrue(NativeGettext::reactivateIfLost());

        $this->assertTrue(NativeGettext::isActive());
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));
    }

    /**
     * @return iterable<string, array{string|false}>
     */
    public static function lcAllBeforeSystemLocaleActivation(): iterable
    {
        yield 'unset' => [false];
        yield 'C' => ['C'];
        yield 'another locale' => ['de_DE.UTF-8'];
    }

    #[DataProvider('lcAllBeforeSystemLocaleActivation')]
    public function testSystemLocaleBranchSetsLcAllToTheActiveLocaleAndRestoreGivesItBackExactly(string|false $lc_all): void
    {
        $this->requireSystemLocale();
        NativeGettext::useSystemLocaleForTests(true);
        putenv($lc_all === false ? 'LC_ALL' : 'LC_ALL=' . $lc_all);

        $this->activateSystemLocale();

        $this->assertSame(setlocale(LC_MESSAGES, '0'), getenv('LC_ALL'));
        $this->assertTrue(NativeGettext::isActive());
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));

        NativeGettext::restore();

        $this->assertSame($lc_all, getenv('LC_ALL'));
        $this->assertFalse(NativeGettext::isActive());
    }

    #[DataProvider('lcAllBeforeSystemLocaleActivation')]
    public function testRestoreAfterARepeatedActivationGivesBackLcAllFromBeforeTheFirstOne(string|false $lc_all): void
    {
        $this->requireSystemLocale();
        NativeGettext::useSystemLocaleForTests(true);
        putenv($lc_all === false ? 'LC_ALL' : 'LC_ALL=' . $lc_all);
        $this->activateSystemLocale();

        $this->assertTrue(NativeGettext::activate($this->root . '/other-no-locale'), (string) NativeGettext::getProblem());
        putenv('LC_ALL=fr_FR.UTF-8');
        $this->assertTrue(NativeGettext::reactivateIfLost());
        $this->assertSame(setlocale(LC_MESSAGES, '0'), getenv('LC_ALL'));

        NativeGettext::restore();

        $this->assertSame($lc_all, getenv('LC_ALL'));
    }

    public function testRestoreChangesNothingInAThreadSafePhpInTheSystemLocaleBranch(): void
    {
        $this->requireSystemLocale();
        NativeGettext::useSystemLocaleForTests(true);
        putenv('LC_ALL=C');
        $this->activateSystemLocale();
        $active = setlocale(LC_MESSAGES, '0');
        NativeGettext::useThreadSafeForTests(true);

        NativeGettext::restore();

        $this->assertSame($active, getenv('LC_ALL'));
        $this->assertTrue(NativeGettext::isActive());
    }

    public function testRestoreLeavesALcAllOtherCodeChangedSinceAsItIsInTheSystemLocaleBranch(): void
    {
        $this->requireSystemLocale();
        NativeGettext::useSystemLocaleForTests(true);
        putenv('LC_ALL');
        $this->activateSystemLocale();
        putenv('LC_ALL=fr_FR.UTF-8');

        NativeGettext::restore();

        $this->assertSame('fr_FR.UTF-8', getenv('LC_ALL'));
    }

    public function testGlibcBranchNeverTouchesLcAll(): void
    {
        MigratedPoFixture::requireGlibcLocale($this);
        putenv('LC_ALL=de_DE.UTF-8');

        $this->activateBuild();
        $this->assertTrue(NativeGettext::isActive());
        $this->assertSame('de_DE.UTF-8', getenv('LC_ALL'));

        NativeGettext::restore();
        $this->assertSame('de_DE.UTF-8', getenv('LC_ALL'));

        putenv('LC_ALL');
        $this->activateBuild();
        $this->assertFalse(getenv('LC_ALL'));
        NativeGettext::restore();
        $this->assertFalse(getenv('LC_ALL'));
    }

    private function activateSystemLocale(): void
    {
        $this->assertTrue(NativeGettext::activate($this->root . '/no-locale'), (string) NativeGettext::getProblem());
        $this->assertTrue(NativeGettext::bind(self::DOMAIN, $this->build_directory));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function directoriesWithoutAUsableLocale(): iterable
    {
        yield 'no locale directory' => ['none'];
        yield 'an empty locale directory' => ['empty'];
    }

    #[DataProvider('directoriesWithoutAUsableLocale')]
    public function testActivateFailsWithAProblemAndLeavesTheProcessAsItWas(string $kind): void
    {
        MigratedPoFixture::requireGlibcLocale($this);
        $directory = $this->root . '/locale-' . $kind;
        mkdir($directory . ($kind === 'empty' ? '/' . NativeGettext::LOCALE_NAME : ''), 0775, true);
        putenv('LANGUAGE=de');
        putenv('LOCPATH=/nonexistent/locpath');
        $messages = setlocale(LC_MESSAGES, '0');

        $this->assertFalse(NativeGettext::activate($directory));

        $this->assertStringContainsString(NativeGettext::LOCALE_NAME, (string) NativeGettext::getProblem());
        $this->assertFalse(NativeGettext::isActive());
        $this->assertSame($messages, setlocale(LC_MESSAGES, '0'));
        $this->assertSame('de', getenv('LANGUAGE'));
        $this->assertSame('/nonexistent/locpath', getenv('LOCPATH'));
    }

    /**
     * @return iterable<string, array{\Closure(): void}>
     */
    public static function foreignChanges(): iterable
    {
        yield 'LC_MESSAGES set to C' => [static function (): void {
            setlocale(LC_MESSAGES, 'C');
        }];
        yield 'LC_ALL set to C (what initLocale() does)' => [static function (): void {
            setlocale(LC_ALL, 'C');
        }];
        yield 'LANGUAGE changed' => [static function (): void {
            putenv('LANGUAGE=de');
        }];
        yield 'LANGUAGE removed' => [static function (): void {
            putenv('LANGUAGE');
        }];
    }

    #[DataProvider('foreignChanges')]
    public function testReactivateIfLostRepairsWhatOtherCodeChanged(\Closure $change): void
    {
        MigratedPoFixture::requireGlibcLocale($this);
        $this->activateBuild();
        $change();
        $this->assertFalse(NativeGettext::isActive(), 'precondition');
        $this->assertSame('greeting', NativeGettext::translate(self::DOMAIN, 'greeting'), 'precondition: nothing is translated now');

        $this->assertTrue(NativeGettext::reactivateIfLost());

        $this->assertTrue(NativeGettext::isActive());
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));
    }

    private function requireRealLocale(): void
    {
        $before = setlocale(LC_MESSAGES, '0');
        $found = setlocale(LC_MESSAGES, self::REAL_LOCALE);
        setlocale(LC_MESSAGES, $before);
        if ($found === false) {
            $this->markTestSkipped('the locale "' . self::REAL_LOCALE . '" is not installed');
        }
    }

    #[DataProvider('previousProcessSettings')]
    public function testActivateKeepsARealLocaleAndRestoreLeavesItWhileTakingLanguageBack(string|false $language, string|false $locpath): void
    {
        MigratedPoFixture::requireGlibcLocale($this);
        $this->requireRealLocale();
        setlocale(LC_MESSAGES, self::REAL_LOCALE);
        putenv($language === false ? 'LANGUAGE' : 'LANGUAGE=' . $language);
        putenv($locpath === false ? 'LOCPATH' : 'LOCPATH=' . $locpath);

        $this->activateBuild();

        $this->assertTrue(NativeGettext::isActive());
        $this->assertSame(self::REAL_LOCALE, setlocale(LC_MESSAGES, '0'));
        $this->assertSame(NativeGettext::CATALOG_LANGUAGE, getenv('LANGUAGE'));
        $this->assertSame($locpath, getenv('LOCPATH'), 'LOCPATH is not touched when the locale stays');
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));

        NativeGettext::restore();

        $this->assertFalse(NativeGettext::isActive());
        $this->assertSame(self::REAL_LOCALE, setlocale(LC_MESSAGES, '0'), 'restore() leaves a locale it did not set');
        $this->assertSame($language, getenv('LANGUAGE'));
        $this->assertSame($locpath, getenv('LOCPATH'));
    }

    #[DataProvider('lcAllBeforeSystemLocaleActivation')]
    public function testSystemLocaleBranchKeepsARealLocaleSetsLcAllToItAndRestoreGivesLcAllBack(string|false $lc_all): void
    {
        $this->requireRealLocale();
        NativeGettext::useSystemLocaleForTests(true);
        setlocale(LC_MESSAGES, self::REAL_LOCALE);
        putenv($lc_all === false ? 'LC_ALL' : 'LC_ALL=' . $lc_all);

        $this->activateSystemLocale();

        $this->assertSame(self::REAL_LOCALE, setlocale(LC_MESSAGES, '0'));
        $this->assertSame(self::REAL_LOCALE, getenv('LC_ALL'));
        $this->assertTrue(NativeGettext::isActive());
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));

        NativeGettext::restore();

        $this->assertSame($lc_all, getenv('LC_ALL'));
        $this->assertSame(self::REAL_LOCALE, setlocale(LC_MESSAGES, '0'));
        $this->assertFalse(NativeGettext::isActive());
    }

    /**
     * @return iterable<string, array{bool}>
     */
    public static function branches(): iterable
    {
        yield 'glibc branch (own locale)' => [false];
        yield 'system locale branch' => [true];
    }

    #[DataProvider('branches')]
    public function testReactivateIfLostKeepsALocaleOtherCodeSetForEverything(bool $system_locale): void
    {
        $system_locale ? $this->requireSystemLocale() : MigratedPoFixture::requireGlibcLocale($this);
        $this->requireRealLocale();
        NativeGettext::useSystemLocaleForTests($system_locale);
        $this->activateBuildInBranch($system_locale);
        $locpath = getenv('LOCPATH');
        setlocale(LC_ALL, self::REAL_LOCALE);
        $this->assertFalse(NativeGettext::isActive(), 'precondition');

        $this->assertTrue(NativeGettext::reactivateIfLost());

        $this->assertTrue(NativeGettext::isActive());
        $this->assertSame(self::REAL_LOCALE, setlocale(LC_MESSAGES, '0'));
        $this->assertSame($locpath, getenv('LOCPATH'));
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));
        $this->assertFalse(NativeGettext::reactivateIfLost(), 'nothing left to repair');

        NativeGettext::restore();
        $this->assertSame(self::REAL_LOCALE, setlocale(LC_MESSAGES, '0'), 'the locale of the other code is not ours to take back');
    }

    private function activateBuildInBranch(bool $system_locale): void
    {
        $system_locale ? $this->activateSystemLocale() : $this->activateBuild();
    }

    public function testActivateStillFailsWithoutLocaleDirectoryWhenARealLocaleStays(): void
    {
        MigratedPoFixture::requireGlibcLocale($this);
        $this->requireRealLocale();
        setlocale(LC_MESSAGES, self::REAL_LOCALE);
        putenv('LANGUAGE=de');
        $this->assertDirectoryDoesNotExist($this->root . '/locale-none');

        $this->assertFalse(NativeGettext::activate($this->root . '/locale-none'));

        $this->assertStringContainsString(NativeGettext::LOCALE_NAME, (string) NativeGettext::getProblem());
        $this->assertFalse(NativeGettext::isActive());
        $this->assertSame(self::REAL_LOCALE, setlocale(LC_MESSAGES, '0'));
        $this->assertSame('de', getenv('LANGUAGE'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function localesThatAreNotReal(): iterable
    {
        yield 'C.UTF-8' => ['C.UTF-8'];
        yield 'C.utf8' => ['C.utf8'];
        yield 'POSIX' => ['POSIX'];
        yield 'C' => ['C'];
    }

    #[DataProvider('localesThatAreNotReal')]
    public function testALocaleOfTheCFamilyIsNotRealSoTheOwnLocaleIsActivatedAndTakenBackAgain(string $locale): void
    {
        MigratedPoFixture::requireGlibcLocale($this);
        if (setlocale(LC_MESSAGES, $locale) === false) {
            $this->markTestSkipped('the locale "' . $locale . '" is not installed');
        }
        $before = setlocale(LC_MESSAGES, '0');

        $this->activateBuild();

        $this->assertSame(NativeGettext::LOCALE_NAME, setlocale(LC_MESSAGES, '0'));
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));

        NativeGettext::restore();

        $this->assertSame($before, setlocale(LC_MESSAGES, '0'));
    }

    public function testTheOwnLocaleIsNeverTakenForARealOneAfterLanguageWasLost(): void
    {
        MigratedPoFixture::requireGlibcLocale($this);
        $before = setlocale(LC_MESSAGES, '0');
        $this->activateBuild();
        putenv('LANGUAGE');
        $this->assertFalse(NativeGettext::isActive(), 'precondition');

        $this->assertTrue(NativeGettext::reactivateIfLost());

        $this->assertSame(NativeGettext::LOCALE_NAME, setlocale(LC_MESSAGES, '0'));
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));

        NativeGettext::restore();

        $this->assertSame($before, setlocale(LC_MESSAGES, '0'), 'the own locale is ours, so it is taken back');
        $this->assertNotSame(NativeGettext::CATALOG_LANGUAGE, getenv('LANGUAGE'));
    }

    #[RunInSeparateProcess]
    public function testTheIcuDefaultLocaleStaysTheOneOfTheAdministrator(): void
    {
        MigratedPoFixture::requireGlibcLocale($this);
        $this->requireRealLocale();
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('needs ext-intl');
        }
        setlocale(LC_ALL, [self::REAL_LOCALE]);
        setlocale(LC_NUMERIC, 'C');

        $this->activateBuild();

        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));
        $this->assertSame('de_DE', \locale_get_default());
    }

    public function testReactivateIfLostDoesNothingWithoutAnActivationOrWhileActive(): void
    {
        // setUp()'s build activated native gettext - restore() keeps that locale for a reactivation
        NativeGettext::resetForTests();
        $this->assertFalse(NativeGettext::reactivateIfLost(), 'never activated');
        $this->assertNotSame(NativeGettext::LOCALE_NAME, setlocale(LC_MESSAGES, '0'));

        $this->activateBuild();
        $this->assertFalse(NativeGettext::reactivateIfLost(), 'still active');
    }

    public function testAfterRestoreReactivateIfLostActivatesTheLocaleAgain(): void
    {
        $this->activateBuild();
        NativeGettext::restore();
        $this->assertFalse(NativeGettext::isActive(), 'precondition');

        $this->assertTrue(NativeGettext::reactivateIfLost());
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));
    }

    public function testRestoreChangesNothingInAThreadSafePhp(): void
    {
        $this->activateBuild();
        NativeGettext::useThreadSafeForTests(true);

        NativeGettext::restore();

        $this->assertTrue(NativeGettext::isThreadSafe());
        $this->assertTrue(NativeGettext::isActive(), 'other threads may be using the settings');
    }

    /**
     * In a thread-safe PHP restore() does nothing, but another thread may reset what activate() set; the lookup must
     * repair that.
     */
    #[DataProvider('foreignChanges')]
    public function testReactivateIfLostRepairsAForeignResetInAThreadSafePhpToo(\Closure $change): void
    {
        $this->activateBuild();
        NativeGettext::useThreadSafeForTests(true);
        NativeGettext::restore();
        $this->assertTrue(NativeGettext::isActive(), 'precondition: nothing was taken back');
        $change();
        $this->assertFalse(NativeGettext::isActive(), 'precondition');

        $this->assertTrue(NativeGettext::reactivateIfLost());

        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));
    }

    public function testReactivateIfLostFailsWithAProblemWhenTheLocaleIsGone(): void
    {
        MigratedPoFixture::requireGlibcLocale($this);
        $this->activateBuild();
        setlocale(LC_MESSAGES, 'C');
        MigratedPoFixture::removeDirectory($this->locale_directory . '/' . NativeGettext::LOCALE_NAME);

        $this->assertFalse(NativeGettext::reactivateIfLost());

        $this->assertStringContainsString('is missing', (string) NativeGettext::getProblem());
        $this->assertFalse(NativeGettext::isActive());
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function catalogs(): iterable
    {
        yield 'the catalog of the build' => ['build', true];
        yield 'garbage instead of a catalog' => ['garbage', false];
        yield 'a catalog without header (not one of ours)' => ['no-header', false];
        yield 'a domain without file' => ['missing', false];
    }

    #[DataProvider('catalogs')]
    public function testIsLoadedTellsAReadableCatalogOfOurOwnByItsHeader(string $kind, bool $expected): void
    {
        $this->activateBuild();
        $directory = $this->build_directory;
        $domain = self::DOMAIN;
        if ($kind !== 'build') {
            $domain = 'other.' . $kind;
            $directory = $this->root . '/catalogs-' . $kind;
            $file = $directory . '/' . NativeGettext::CATALOG_DIRECTORY . '/' . $domain . '.mo';
            mkdir(dirname($file), 0775, true);
            if ($kind === 'garbage') {
                file_put_contents($file, "this is no .mo file\n");
            } elseif ($kind === 'no-header') {
                $catalog = new TranslationCatalog();
                $entry = new TranslationEntry(null, 'greeting');
                $entry->translate('Hallo');
                $catalog->add($entry);
                file_put_contents($file, $catalog->toMoString());
            }
        }
        $this->assertTrue(NativeGettext::bind($domain, $directory));

        $this->assertSame($expected, NativeGettext::isLoaded($domain));
    }

    public function testBindRefusesADirectoryThatDoesNotExist(): void
    {
        $this->assertFalse(NativeGettext::bind('ntg.xx', $this->root . '/does-not-exist'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function messageIdsThatCannotBeLookedUp(): iterable
    {
        yield 'empty (would answer with the header)' => [''];
        yield 'longer than PHP accepts' => [str_repeat('x', 4097)];
        yield 'unknown' => ['no_such_key'];
    }

    #[DataProvider('messageIdsThatCannotBeLookedUp')]
    public function testTranslateAnswersTheMessageIdItself(string $message_id): void
    {
        $this->activateBuild();

        $this->assertSame($message_id, NativeGettext::translate(self::DOMAIN, $message_id));
        $this->assertSame($message_id, NativeGettext::translatePlural(self::DOMAIN, $message_id, 2));
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function quantities(): iterable
    {
        yield 'one' => [1, 'Eintrag'];
        yield 'zero' => [0, 'Einträge'];
        yield 'minus one counts like one' => [-1, 'Eintrag'];
        yield 'minus five counts like five' => [-5, 'Einträge'];
        yield 'the largest integer' => [PHP_INT_MAX, 'Einträge'];
        yield 'the smallest integer (abs() would overflow)' => [PHP_INT_MIN, 'Einträge'];
    }

    #[DataProvider('quantities')]
    public function testTranslatePluralSelectsTheFormOfTheAbsoluteQuantity(int $n, string $expected): void
    {
        $this->activateBuild();

        $this->assertSame(
            $expected,
            NativeGettext::translatePlural(self::DOMAIN, ShippedTranslations::PLURAL_CONTEXT . "\x04item", $n)
        );
    }
}
