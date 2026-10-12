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
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * ilLanguage::ntxt(), the plural-aware sibling of txt(). Separate processes: CLIENT_DATA_DIR/ILIAS_ABSOLUTE_PATH cannot
 * be redefined and the migrated-file cache is a static property.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class NtxtTest extends ilLanguageBaseTestCase
{
    private ?string $fixture_directory = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        if (!defined('ILIAS_LOG_ENABLED')) {
            define('ILIAS_LOG_ENABLED', false);
        }
        MigratedPoFixture::resetRuntime();
    }

    protected function tearDown(): void
    {
        if (isset($this->fixture_directory) && is_dir($this->fixture_directory)) {
            array_map('unlink', glob($this->fixture_directory . '/*') ?: []);
            rmdir($this->fixture_directory);
        }
        if (isset($this->fixture_directory)) {
            MigratedPoFixture::removeShippedDirectory('components/ILIAS/Language/tests/' . basename($this->fixture_directory));
        }
        if (defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }

        parent::tearDown();
    }

    /** Contributes a migrated module whose shipped `.po` (and, if it differs, overlay `.mo`) is $catalog, so plural entries can be built directly. */
    private function contributeModule(string $module, TranslationCatalog $catalog, ?TranslationCatalog $overlay = null): LanguageFileDirectory
    {
        MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);
        $this->fixture_directory ??= __DIR__ . '/tmp-ntxt-fixtures-' . bin2hex(random_bytes(4));

        $relative_path = 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/';
        if ($overlay !== null) {
            MigratedPoFixture::writePair(rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module . '/de/' . $module . '_de', $overlay);
        }
        MigratedPoFixture::writeShippedPo($relative_path, $module, 'de', $catalog);

        return MigratedPoFixture::directory($module, $relative_path);
    }

    private function registerDirectoryManager(LanguageFileDirectory ...$contributed): void
    {
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), ...$contributed)
        );
    }

    private function stubUsageLogDependencies(): void
    {
        $this->setGlobalVariable('ilClientIniFile', $this->createStub(ilIniFile::class));
        $this->setGlobalVariable('ilDB', $this->createStub(ilDBInterface::class));
    }

    /** Logged problems (e.g. invalid "Plural-Forms") need a logger in $DIC, else they go to error_log() and mark the test risky. */
    private function stubComponentLogger(): void
    {
        $factory = $this->createStub(ilLoggerFactory::class);
        $factory->method('getComponentLogger')->willReturn($this->createStub(ilLogger::class));
        $this->setGlobalVariable('ilLoggerFactory', $factory);
    }

    private function buildLanguage(string $lang_key = 'de'): ilLanguage
    {
        global $DIC;
        if ($DIC->offsetExists(LanguageFileDirectoryManager::class)) {
            MigratedPoFixture::build($DIC[LanguageFileDirectoryManager::class], (string) ILIAS_ABSOLUTE_PATH);
        }
        $language = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        $reflected = new ReflectionObject($language);
        $reflected->getProperty('lang_key')->setValue($language, $lang_key);
        $reflected->getProperty('lang_user')->setValue($language, $lang_key);
        $reflected->getProperty('lang_default')->setValue($language, $lang_key);
        $reflected->getProperty('log')->setValue($language, $this->createStub(ilLogger::class));

        return $language;
    }

    /** @param list<string> $forms */
    private static function pluralCatalog(string $module, string $header, string $id, array $forms): TranslationCatalog
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        if ($header !== '') {
            $catalog->setHeader('Plural-Forms', $header);
        }
        $entry = new TranslationEntry($module, $id);
        $entry->setPlural($id . 's', $forms);
        $catalog->add($entry);

        return $catalog;
    }

    public function testSelectsTheFormThePluralRuleOfTheLanguagePicksForN(): void
    {
        $catalog = self::pluralCatalog('ntxt', 'nplurals=2; plural=(n != 1);', 'item', ['Eintrag', 'Einträge']);
        $this->stubUsageLogDependencies();
        $this->registerDirectoryManager($this->contributeModule('ntxt', $catalog));

        $language = $this->buildLanguage();
        $language->loadLanguageModule('ntxt');

        $this->assertSame('Eintrag', $language->ntxt('item', 1));
        $this->assertSame('Einträge', $language->ntxt('item', 0));
        $this->assertSame('Einträge', $language->ntxt('item', 5));
        $this->assertSame('Einträge', $language->ntxt('item', -5), 'a negative n counts like its absolute value');
    }

    public function testFallsBackToTxtForAnOrdinarySingularIdentifier(): void
    {
        $catalog = MigratedPoFixture::catalog('ntxt', ['plain' => 'Einfach']);
        $this->stubUsageLogDependencies();
        $this->registerDirectoryManager($this->contributeModule('ntxt', $catalog));

        $language = $this->buildLanguage();
        $language->loadLanguageModule('ntxt');

        $this->assertSame('Einfach', $language->ntxt('plain', 1));
        $this->assertSame('Einfach', $language->ntxt('plain', 5));
    }

    public function testFallsBackToTxtForATopicThatWasNeverLoadedAtAll(): void
    {
        $language = $this->buildLanguage();

        $this->assertSame($language->txt('unknown_topic'), $language->ntxt('unknown_topic', 5));
    }

    public function testServesThePluralFormFromTheFallbackModuleWithoutLoadingIt(): void
    {
        $catalog = self::pluralCatalog('ntxt', 'nplurals=2; plural=(n != 1);', 'item', ['Eintrag', 'Einträge']);
        $this->stubUsageLogDependencies();
        $this->registerDirectoryManager($this->contributeModule('ntxt', $catalog));

        $language = $this->buildLanguage();

        $this->assertSame('Eintrag', $language->ntxt('item', 1, 'ntxt'));
        $this->assertSame('Einträge', $language->ntxt('item', 5, 'ntxt'));
    }

    /** Without a "Plural-Forms" header the Germanic rule (n==1 -> form 0, else 1) applies instead of failing. */
    public function testUsesTheGermanicRuleWhenThePluralFormsHeaderIsMissing(): void
    {
        $catalog = self::pluralCatalog('ntxt', '', 'item', ['Eintrag', 'Einträge']);
        $this->stubUsageLogDependencies();
        $this->stubComponentLogger();
        $this->registerDirectoryManager($this->contributeModule('ntxt', $catalog));

        $language = $this->buildLanguage();
        $language->loadLanguageModule('ntxt');

        $this->assertSame('Eintrag', $language->ntxt('item', 1));
        $this->assertSame('Einträge', $language->ntxt('item', 5));
    }

    /** An empty selected form falls back to txt() rather than returning an empty string. */
    public function testFallsBackToTxtWhenTheSelectedFormIsEmpty(): void
    {
        $catalog = self::pluralCatalog('ntxt', 'nplurals=2; plural=(n != 1);', 'item', ['Eintrag', '']);
        $this->stubUsageLogDependencies();
        $this->registerDirectoryManager($this->contributeModule('ntxt', $catalog));

        $language = $this->buildLanguage();
        $language->loadLanguageModule('ntxt');

        // an empty default form falls back to form 0, which txt() and the n=5 fallback of ntxt() both serve
        $this->assertSame('Eintrag', $language->txt('item'));
        $this->assertSame('Eintrag', $language->ntxt('item', 5));
    }

    /** A later module overwriting the identifier with a plain value must win for ntxt() too. */
    public function testALaterModuleOverwritingTheIdentifierWinsOverTheEarlierPluralForms(): void
    {
        $plural = self::pluralCatalog('ntxt_a', 'nplurals=2; plural=(n != 1);', 'shared', ['Eintrag', 'Einträge']);
        $overwritten = MigratedPoFixture::catalog('ntxt_b', ['shared' => 'Überschrieben']);
        $this->stubUsageLogDependencies();
        $this->registerDirectoryManager(
            $this->contributeModule('ntxt_a', $plural),
            $this->contributeModule('ntxt_b', $overwritten)
        );

        $language = $this->buildLanguage();
        $language->loadLanguageModule('ntxt_a');
        $language->loadLanguageModule('ntxt_b');

        $this->assertSame('Überschrieben', $language->ntxt('shared', 5));
    }

    public function testAnOverlayReplacingThePluralFormsIsUsedForFormSelection(): void
    {
        $shipped = self::pluralCatalog('ntxt', 'nplurals=2; plural=(n != 1);', 'item', ['Eintrag', 'Einträge']);
        $overlay = self::pluralCatalog('ntxt', 'nplurals=2; plural=(n != 1);', 'item', ['Datensatz', 'Datensätze']);
        $this->stubUsageLogDependencies();
        $this->registerDirectoryManager($this->contributeModule('ntxt', $shipped, $overlay));

        $language = $this->buildLanguage();
        $language->loadLanguageModule('ntxt');

        $this->assertSame('Datensatz', $language->ntxt('item', 1));
        $this->assertSame('Datensätze', $language->ntxt('item', 5));
    }
}
