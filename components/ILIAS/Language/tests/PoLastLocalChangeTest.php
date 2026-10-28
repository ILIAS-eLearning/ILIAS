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
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * ilObjLanguage::_getLastLocalChange() is PO/MO-aware: a migrated module's overlay .po (under CLIENT_DATA_DIR, never the
 * shipped one) can carry a local_change that lng_data never sees (direct .po edit). _getLastMigratedLocalChange() is
 * tested via reflection, _getLastLocalChange() end-to-end with a stubbed $ilDB.
 *
 * Separate processes, because CLIENT_DATA_DIR cannot be redefined and an earlier test class of a full-suite run
 * may already have defined it as /var/iliasdata, which would make every test skip.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class PoLastLocalChangeTest extends ilLanguageBaseTestCase
{
    private ?string $fixture_directory = null;
    /** True if this test defined CLIENT_DATA_DIR itself; tearDown() removes only such a root. */
    private bool $created_client_data_dir_root = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        // CLIENT_DATA_DIR is deliberately not defined here: tests that seed no fixture must not skip because of a foreign one
    }

    /** Refuses to write fixtures into a foreign CLIENT_DATA_DIR (e.g. the real /var/iliasdata of a full-suite run). */
    private function guardClientDataDirIsTestOwned(): void
    {
        if (defined('CLIENT_DATA_DIR') && !str_starts_with(CLIENT_DATA_DIR, sys_get_temp_dir() . '/')) {
            $this->markTestSkipped(
                'CLIENT_DATA_DIR ("' . CLIENT_DATA_DIR . '") is not a test-owned temp directory - '
                . 'refusing to write fixture files there.'
            );
        }
        if (!defined('CLIENT_DATA_DIR')) {
            define('CLIENT_DATA_DIR', sys_get_temp_dir() . '/ilias_lang_test_client_data_dir_' . getmypid());
            $this->created_client_data_dir_root = true;
        }
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
        // never remove a foreign CLIENT_DATA_DIR
        if ($this->created_client_data_dir_root && defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }

        parent::tearDown();
    }

    /**
     * Writes a .po/.mo pair for a throwaway module at the overlay location under CLIENT_DATA_DIR, the only one
     * _getLastMigratedLocalChange() reads.
     *
     * @param array<string, array{value: string, original?: string}> $entries keyed by identifier;
     *   'original' seeds LocalChangeComments::setOriginal() as baseline for refresh().
     * @param array<string, string> $local_changes identifier => ISO-8601 UTC local_change timestamp
     *   ('Y-m-d\TH:i:s\Z') stamped via LocalChangeComments::refresh(), simulating a direct .po edit.
     */
    private function seedFixtureModule(
        string $module,
        string $lang_key,
        array $entries,
        array $local_changes = []
    ): LanguageFileDirectory {
        $this->guardClientDataDirIsTestOwned();
        $this->fixture_directory ??= rtrim(CLIENT_DATA_DIR, '/') . '/lang/components/ILIAS/Language/tests/'
            . 'tmp-lastchange-fixtures-' . bin2hex(random_bytes(4));
        if (!is_dir($this->fixture_directory)) {
            mkdir($this->fixture_directory, 0775, true);
        }

        $translations = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog();
        foreach ($entries as $identifier => $entry) {
            $translation = MigratedPoFixture::entry($module, $identifier, $entry['value']);
            if (isset($entry['original'])) {
                LocalChangeComments::setOriginal($translation, $entry['original']);
            }
            if (isset($local_changes[$identifier])) {
                $now = DateTimeImmutable::createFromFormat(
                    'Y-m-d\TH:i:s\Z',
                    $local_changes[$identifier],
                    new DateTimeZone('UTC')
                );
                // refresh() stamps local_change only if the value differs from the previous one; an empty previous value always does
                $previous = $entry['original'] ?? '';
                LocalChangeComments::refresh(
                    $translation,
                    $previous === $entry['value'] ? $previous . ' (old)' : $previous,
                    $entry['value'],
                    $now
                );
            }
            $translations->add($translation);
        }

        $base_path = rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module . '/' . $lang_key . '/' . $module . '_' . $lang_key;
        MigratedPoFixture::writePo($base_path . '.po', $translations);
        MigratedPoFixture::writeOverlayRevision($base_path, $translations);

        $relative_path = 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/';
        MigratedPoFixture::writeShippedPo($relative_path, $module, $lang_key, $translations);

        return new class ($module, $relative_path) implements LanguageFileDirectory {
            public function __construct(private string $prefix, private string $path)
            {
            }

            public function getPrefix(): string
            {
                return $this->prefix;
            }

            public function getPath(): string
            {
                return $this->path;
            }

            public function getSuffix(): string
            {
                return '';
            }

            public function isLocal(): bool
            {
                return false;
            }
        };
    }

    private function registerDirectoryManager(LanguageFileDirectory ...$contributed): void
    {
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), ...$contributed)
        );
    }

    private function invokeGetLastMigratedLocalChange(string $lang_key): ?string
    {
        $method = (new ReflectionClass(ilObjLanguage::class))->getMethod('_getLastMigratedLocalChange');
        return $method->invoke(null, $lang_key);
    }

    /** _getLastLocalChange() issues exactly one query() call, so a single canned fetchRow() result suffices. */
    private function stubDatabase(?string $db_last_change): void
    {
        $statement = $this->createStub(ilDBStatement::class);
        $statement->method('fetchRow')->willReturn(
            $db_last_change === null ? [] : ['last_change' => $db_last_change]
        );

        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(
            static fn(mixed $value, string $type): string => "'" . (string) $value . "'"
        );
        $db->method('query')->willReturn($statement);

        $this->setGlobalVariable('ilDB', $db);
    }

    public function testReturnsTheNormalizedTimestampForAModuleWithOneLocallyChangedEntry(): void
    {
        $directory = $this->seedFixtureModule(
            'lctest',
            'de',
            ['greeting' => ['value' => 'Hallo, geändert', 'original' => 'Hallo']],
            ['greeting' => '2026-09-21T10:00:00Z']
        );
        $this->registerDirectoryManager($directory);

        $this->assertSame('2026-09-21 10:00:00', $this->invokeGetLastMigratedLocalChange('de'));
    }

    public function testReturnsTheMaximumAcrossTwoEntriesNotTheFirstOrLastIterated(): void
    {
        // the later change is added first: iteration order must not decide, only the timestamp
        $directory = $this->seedFixtureModule(
            'lctest',
            'de',
            [
                'farewell' => ['value' => 'Tschüss, geändert', 'original' => 'Tschüss'],
                'greeting' => ['value' => 'Hallo, geändert', 'original' => 'Hallo'],
            ],
            [
                'farewell' => '2026-09-21T18:00:00Z',
                'greeting' => '2026-09-21T10:00:00Z',
            ]
        );
        $this->registerDirectoryManager($directory);

        $this->assertSame('2026-09-21 18:00:00', $this->invokeGetLastMigratedLocalChange('de'));
    }

    public function testReturnsTheMaximumAcrossTwoContributedModules(): void
    {
        $module_a = $this->seedFixtureModule(
            'lca',
            'de',
            ['greeting' => ['value' => 'Hallo, geändert', 'original' => 'Hallo']],
            ['greeting' => '2026-09-21T10:00:00Z']
        );
        $module_b = $this->seedFixtureModule(
            'lcb',
            'de',
            ['farewell' => ['value' => 'Tschüss, geändert', 'original' => 'Tschüss']],
            ['farewell' => '2026-09-22T09:30:00Z']
        );
        $this->registerDirectoryManager($module_a, $module_b);

        $this->assertSame('2026-09-22 09:30:00', $this->invokeGetLastMigratedLocalChange('de'));
    }

    public function testReturnsNullWhenNoEntryOfTheMigratedModuleHasALocalChange(): void
    {
        $directory = $this->seedFixtureModule('lctest', 'de', [
            'greeting' => ['value' => 'Hallo', 'original' => 'Hallo'],
        ]);
        $this->registerDirectoryManager($directory);

        $this->assertNull($this->invokeGetLastMigratedLocalChange('de'));
    }

    public function testSkipsAContributedDirectoryMissingTheMoOrPoFileForTheRequestedLanguage(): void
    {
        $directory = $this->seedFixtureModule(
            'lctest',
            'de',
            ['greeting' => ['value' => 'Hallo, geändert', 'original' => 'Hallo']],
            ['greeting' => '2026-09-21T10:00:00Z']
        );
        $this->registerDirectoryManager($directory);

        $this->assertNull($this->invokeGetLastMigratedLocalChange('fr'));
    }

    public function testReturnsNullWhenNoDirectoryManagerIsRegisteredAtAll(): void
    {
        $this->assertNull($this->invokeGetLastMigratedLocalChange('de'));
    }

    public function testSkipsATranslationWhoseContextDoesNotMatchTheModule(): void
    {
        $this->guardClientDataDirIsTestOwned();
        $this->fixture_directory ??= rtrim(CLIENT_DATA_DIR, '/') . '/lang/components/ILIAS/Language/tests/'
            . 'tmp-lastchange-fixtures-' . bin2hex(random_bytes(4));
        if (!is_dir($this->fixture_directory)) {
            mkdir($this->fixture_directory, 0775, true);
        }

        $translations = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog();
        // an entry of a different module in the same .po must not leak a local_change into "lctest"
        $foreign = MigratedPoFixture::entry('other_module', 'greeting', 'Hallo, geändert');
        LocalChangeComments::setOriginal($foreign, 'Hallo');
        LocalChangeComments::refresh($foreign, 'Hallo', 'Hallo, geändert', new DateTimeImmutable('2026-09-21T10:00:00Z'));
        $translations->add($foreign);

        $base_path = rtrim(CLIENT_DATA_DIR, '/') . '/lang/lctest/de/lctest_de';
        MigratedPoFixture::writePo($base_path . '.po', $translations);
        MigratedPoFixture::writeOverlayRevision($base_path, $translations);

        $relative_path = 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/';
        MigratedPoFixture::writeShippedPo($relative_path, 'lctest', 'de', $translations);
        $directory = new class ('lctest', $relative_path) implements LanguageFileDirectory {
            public function __construct(private string $prefix, private string $path)
            {
            }

            public function getPrefix(): string
            {
                return $this->prefix;
            }

            public function getPath(): string
            {
                return $this->path;
            }

            public function getSuffix(): string
            {
                return '';
            }

            public function isLocal(): bool
            {
                return false;
            }
        };
        $this->registerDirectoryManager($directory);

        $this->assertNull($this->invokeGetLastMigratedLocalChange('de'));
    }

    public function testPoValueWinsWhenItIsLaterThanTheDbValue(): void
    {
        $this->stubDatabase('2026-09-20 08:00:00');
        $directory = $this->seedFixtureModule(
            'lctest',
            'de',
            ['greeting' => ['value' => 'Hallo, geändert', 'original' => 'Hallo']],
            ['greeting' => '2026-09-21T10:00:00Z']
        );
        $this->registerDirectoryManager($directory);

        $this->assertSame('2026-09-21 10:00:00', ilObjLanguage::_getLastLocalChange('de'));
    }

    public function testDbValueWinsWhenItIsLaterThanThePoValue(): void
    {
        $this->stubDatabase('2026-09-22 12:00:00');
        $directory = $this->seedFixtureModule(
            'lctest',
            'de',
            ['greeting' => ['value' => 'Hallo, geändert', 'original' => 'Hallo']],
            ['greeting' => '2026-09-21T10:00:00Z']
        );
        $this->registerDirectoryManager($directory);

        $this->assertSame('2026-09-22 12:00:00', ilObjLanguage::_getLastLocalChange('de'));
    }

    public function testReturnsTheDbValueUnchangedWhenNoMigratedModuleContributesAnything(): void
    {
        $this->stubDatabase('2026-09-20 08:00:00');

        $this->assertSame('2026-09-20 08:00:00', ilObjLanguage::_getLastLocalChange('de'));
    }

    /** The "" sentinel makes ilLanguageFolderTable disable the "reset local changes" row action. */
    public function testReturnsEmptyStringWhenNeitherSourceHasAnyLocalChange(): void
    {
        $this->stubDatabase(null);
        $directory = $this->seedFixtureModule('lctest', 'de', [
            'greeting' => ['value' => 'Hallo', 'original' => 'Hallo'],
        ]);
        $this->registerDirectoryManager($directory);

        $this->assertSame('', ilObjLanguage::_getLastLocalChange('de'));
    }
}
