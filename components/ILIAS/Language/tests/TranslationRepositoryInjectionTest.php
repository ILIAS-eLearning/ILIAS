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

use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\TranslationInMemoryRepository;
use ILIAS\Language\LanguageIdentifier;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

enum TranslationRepositoryInjectionIdentifier: string implements LanguageIdentifier
{
    case GREETING = 'greeting';
    case MISSING = 'missing';

    public function module(): string
    {
        return 'inmem';
    }
}

/**
 * ilLanguage reads the values of the ILIAS modules from the injected TranslationRepository: no
 * build, no .po/.mo files, no gettext. Without the injection they would fall back to TranslationGettextRepository,
 * which serves nothing here (no build), so every assertion on a value fails if the injection is ignored.
 * Own process per test: the installed languages and the repository state are static.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class TranslationRepositoryInjectionTest extends ilLanguageBaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        if (!defined('ILIAS_LOG_ENABLED')) {
            define('ILIAS_LOG_ENABLED', false);
        }

        // the modules count as shipped (by their prefix) although nothing is built
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                MigratedPoFixture::directory('inmem', 'components/ILIAS/Language/tests/no-such-directory/'),
                MigratedPoFixture::directory('unserved', 'components/ILIAS/Language/tests/no-such-directory/')
            )
        );
        $this->setGlobalVariable('ilClientIniFile', $this->createStub(ilIniFile::class));
        $this->setGlobalVariable('ilDB', $this->databaseWithInstalledLanguages(['de']));
        $factory = $this->createStub(ilLoggerFactory::class);
        $factory->method('getComponentLogger')->willReturn($this->createStub(ilLogger::class));
        $this->setGlobalVariable('ilLoggerFactory', $factory);
    }

    /** @param list<string> $installed */
    private function databaseWithInstalledLanguages(array $installed): ilDBInterface&\PHPUnit\Framework\MockObject\Stub
    {
        $rows = array_map(
            static fn(string $key): array => ['obj_id' => $key, 'title' => $key, 'description' => 'installed', 'desc' => 'installed'],
            $installed
        );
        $db = $this->createStub(ilDBInterface::class);
        $db->method('numRows')->willReturn(count($rows));
        $db->method('fetchAssoc')->willReturnCallback(static function () use (&$rows) {
            return array_shift($rows);
        });
        // modules not served by the repository (e.g. "common" before it is migrated) read an empty lng_modules row
        $statement = $this->createStub(ilDBStatement::class);
        $statement->method('fetchRow')->willReturn(['lang_array' => serialize([])]);
        $db->method('query')->willReturn($statement);

        return $db;
    }

    private function repository(): TranslationInMemoryRepository
    {
        return new TranslationInMemoryRepository(
            ['inmem' => ['de' => ['greeting' => 'Hallo', 'same' => 'same'], 'en' => ['greeting' => 'Hello']]],
            ['inmem' => ['de' => ['item' => ['Eintrag', 'Einträge']]]]
        );
    }

    public function testTxtServesTheValuesOfTheInjectedRepositoryForALoadedModule(): void
    {
        $language = new ilLanguage('de', $this->repository());
        $language->loadLanguageModule('inmem');

        $this->assertSame('Hallo', $language->txt('greeting'));
        $this->assertTrue($language->exists('greeting'));
        $this->assertSame('same', $language->txt('same'), 'a value equal to its identifier is a value');
    }

    public function testTxtWithNSelectsThePluralFormOfTheInjectedRepositoryAndFallsBackToTheSingular(): void
    {
        $language = new ilLanguage('de', $this->repository());
        $language->loadLanguageModule('inmem');

        $this->assertSame('Eintrag', $language->txt('item', 1));
        $this->assertSame('Einträge', $language->txt('item', 5));
        $this->assertSame('Hallo', $language->txt('greeting', 5), 'no plural message: the singular value');
    }

    public function testAnIdentifierIsReadFromItsModuleWithoutLoadingIt(): void
    {
        $language = new ilLanguage('de', $this->repository());

        $this->assertSame('Hallo', $language->txt(TranslationRepositoryInjectionIdentifier::GREETING));
        $this->assertSame('-missing-', $language->txt(TranslationRepositoryInjectionIdentifier::MISSING));
    }

    public function testAKeyTheRepositoryDoesNotHaveGivesTheMissingMarkerNotNullOrAnException(): void
    {
        $language = new ilLanguage('de', $this->repository());
        $language->loadLanguageModule('inmem');

        $this->assertSame('-unknown_key-', $language->txt('unknown_key'));
        $this->assertSame('-unknown_key-', $language->txt('unknown_key', 3));
        $this->assertFalse($language->exists('unknown_key'));
    }

    public function testAModuleTheRepositoryDoesNotServeGivesTheMissingMarkerNotNullOrAnException(): void
    {
        $language = new ilLanguage('de', $this->repository());
        $language->loadLanguageModule('inmem');
        $language->loadLanguageModule('unserved');

        $this->assertSame('Hallo', $language->txt('greeting'), 'what is served stays');
        $this->assertSame('-other-', $language->txt('other'));
    }

    public function testAnEmptyRepositoryServesNothing(): void
    {
        $language = new ilLanguage('de', new TranslationInMemoryRepository());
        $language->loadLanguageModule('inmem');

        $this->assertSame('-greeting-', $language->txt('greeting'));
        $this->assertSame('-greeting-', $language->txt('greeting', 2));
        $this->assertSame('-greeting-', $language->txt(TranslationRepositoryInjectionIdentifier::GREETING));
        $this->assertFalse($language->exists('greeting'));
    }

    public function testAModuleWithoutValuesInTheLanguageOfTheRepositoryIsNotServedInAnotherOne(): void
    {
        $repository = new TranslationInMemoryRepository(['inmem' => ['en' => ['greeting' => 'Hello']]]);
        $language = new ilLanguage('de', $repository);
        $language->loadLanguageModule('inmem');

        $this->assertSame('-greeting-', $language->txt('greeting'));
    }
}
