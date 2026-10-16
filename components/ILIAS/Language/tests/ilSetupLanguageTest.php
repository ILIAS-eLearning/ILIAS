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

use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;

/**
 * Class ilSetupLanguageTest
 */
class ilSetupLanguageTest extends ilLanguageBaseTestCase
{
    private ilSetupLanguage $newLangSetupDe;
    private ilSetupLanguage $newLangSetupEs;
    private array $langInstalled;
    private string $tempComponentDir = 'artifacts/test_lang_component';

    protected function setUp(): void
    {
        unset($GLOBALS['ilDB']);
        $this->newLangSetupDe = new ilSetupLanguage('de');
        $this->newLangSetupEs = new ilSetupLanguage('es');

        $this->langInstalled[] = $this->newLangSetupDe;
        $this->langInstalled[] = $this->newLangSetupEs;
    }

    protected function tearDown(): void
    {
        $this->cleanupTempDir();
        parent::tearDown();
    }

    private function cleanupTempDir(): void
    {
        $fullPath = __DIR__ . '/../../../../' . $this->tempComponentDir;
        if (is_dir($fullPath)) {
            $files = glob($fullPath . '/*');
            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            rmdir($fullPath);
        }
    }

    private function createMockComponentDirectory(
        string $path = 'artifacts/test_lang_component',
        string $prefix = 'test'
    ): LanguageFileDirectory {
        return new class ($path, $prefix) implements LanguageFileDirectory {
            public function __construct(private string $path, private string $prefix)
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

    private function createMockCustomizingDirectory(): LanguageFileDirectory
    {
        return new class ($this->tempComponentDir) implements LanguageFileDirectory {
            public function __construct(private string $path)
            {
            }

            public function getPrefix(): string
            {
                return '';
            }

            public function getPath(): string
            {
                return $this->path;
            }

            public function getSuffix(): string
            {
                return '.local';
            }

            public function isLocal(): bool
            {
                return true;
            }
        };
    }

    public function testRetrieveLanguageKey(): void
    {
        $this->assertEquals('de', $this->newLangSetupDe->getLangKey());
        $this->assertEquals('es', $this->newLangSetupEs->getLangKey());
    }

    public function testRetrieveInstalledLanguage(): void
    {
        $languagesAsKeys = [];
        foreach ($this->langInstalled as $languageAsKey) {
            $languagesAsKeys[] = $languageAsKey->getLangKey();
        }

        $this->assertContains('de', $languagesAsKeys);
        $this->assertContains('es', $languagesAsKeys);
    }

    private function emptyDatabase(bool $expect_query): ilDBInterface&\PHPUnit\Framework\MockObject\MockObject
    {
        $db = $this->createMock(ilDBInterface::class);
        $db->method('quote')->willReturn("'x'");
        $db->method('like')->willReturn('1=1');
        $db->method('fetchObject')->willReturn(null);
        $db->expects($expect_query ? self::once() : self::never())->method('query')
            ->willReturn($this->createStub(ilDBStatement::class));

        return $db;
    }

    /**
     * @return array<string, array{0: bool, 1: bool, 2: bool, 3: string}> handler, constructor, global, winner
     */
    public static function databaseSources(): array
    {
        return [
            'handler beats constructor and global' => [true, true, true, 'handler'],
            'handler beats constructor' => [true, true, false, 'handler'],
            'handler beats global' => [true, false, true, 'handler'],
            'constructor beats global' => [false, true, true, 'constructor'],
            'only constructor' => [false, true, false, 'constructor'],
            'only global' => [false, false, true, 'global'],
        ];
    }

    #[DataProvider('databaseSources')]
    public function testDatabaseSourcePrecedence(bool $handler, bool $constructor, bool $global, string $winner): void
    {
        $dbs = [];
        foreach (['handler' => $handler, 'constructor' => $constructor, 'global' => $global] as $name => $present) {
            if ($present) {
                $dbs[$name] = $this->emptyDatabase($name === $winner);
            }
        }
        if (isset($dbs['global'])) {
            $GLOBALS['ilDB'] = $dbs['global'];
        }

        $setup_language = new ilSetupLanguage('en', null, $dbs['constructor'] ?? null);
        if (isset($dbs['handler'])) {
            $setup_language->setDbHandler($dbs['handler']);
        }

        self::assertSame([], $setup_language->getInstalledLanguages());
    }

    public function testFileBasedMethodsWorkWithoutAnyDatabase(): void
    {
        $setup_language = new ilSetupLanguage('en');

        self::assertContainsOnlyString($setup_language->getLocalLanguages());
        self::assertTrue($setup_language->checkLanguageForInstallation('en'));
    }

    /**
     * @return array<string, array{0: Closure(ilSetupLanguage): mixed}>
     */
    public static function databaseMethods(): array
    {
        return [
            'repository' => [static fn(ilSetupLanguage $l): array => $l->getInstalledLanguages()],
            'manager' => [static fn(ilSetupLanguage $l) => $l->insertLanguageForInstallation('en')],
        ];
    }

    #[DataProvider('databaseMethods')]
    public function testDatabaseMethodsWithoutDatabaseThrowLogicException(Closure $call): void
    {
        $this->expectException(LogicException::class);

        $call(new ilSetupLanguage('en'));
    }

    private function callCheckLanguage(ilSetupLanguage $setup_language, string $lang_key): bool
    {
        $reflection = new ReflectionMethod($setup_language, 'checkLanguage');
        return $reflection->invoke($setup_language, $lang_key);
    }

    public function testCheckLanguageWithOnlyMainFile(): void
    {
        $mockDir = $this->createMockComponentDirectory();
        $manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            new MainLanguageFileDirectory(),
            $mockDir
        );
        $setup_language = new ilSetupLanguage('en', $manager);

        $this->assertTrue($this->callCheckLanguage($setup_language, 'en'));
        $this->assertTrue($this->callCheckLanguage($setup_language, 'de'));
    }

    public function testCheckLanguageWithBothMainAndComponentFiles(): void
    {
        $fullPath = __DIR__ . '/../../../../' . $this->tempComponentDir;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        try {
            file_put_contents(
                $fullPath . '/ilias_en.lang',
                "<!-- language file start -->\ntest_key#:#test_value\n"
            );

            $mockDir = $this->createMockComponentDirectory($this->tempComponentDir, 'test');
            $manager = new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                new MainLanguageFileDirectory(),
                $mockDir
            );
            $setup_language = new ilSetupLanguage('en', $manager);

            $this->assertTrue($this->callCheckLanguage($setup_language, 'en'));
        } finally {
            $this->cleanupTempDir();
        }
    }

    public function testCheckLanguageWithMissingMainFile(): void
    {
        $mockDir = $this->createMockComponentDirectory();
        $manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            new MainLanguageFileDirectory(),
            $mockDir
        );
        $setup_language = new ilSetupLanguage('xx', $manager);

        $this->assertFalse($this->callCheckLanguage($setup_language, 'xx'));
    }

    public function testCheckLanguageFailsOnInvalidComponentLanguageFile(): void
    {
        $fullPath = __DIR__ . '/../../../../' . $this->tempComponentDir;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        try {
            file_put_contents($fullPath . '/ilias_en.lang', "invalid content without header\n");

            $mockDir = $this->createMockComponentDirectory($this->tempComponentDir, 'test');
            $manager = new LanguageFileDirectoryManager(
                new MainLanguageFileDirectory(),
                $mockDir
            );
            $setup_language = new ilSetupLanguage('en', $manager);

            $this->assertFalse($this->callCheckLanguage($setup_language, 'en'));

            file_put_contents(
                $fullPath . '/ilias_en.lang',
                "<!-- language file start -->\nonly_one_part\n"
            );
            $this->assertFalse($this->callCheckLanguage($setup_language, 'en'));
        } finally {
            $this->cleanupTempDir();
        }
    }

    public function testCheckLanguageFailsOnNonUtf8Value(): void
    {
        $fullPath = __DIR__ . '/../../../../' . $this->tempComponentDir;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        try {
            file_put_contents(
                $fullPath . '/ilias_en.lang',
                "<!-- language file start -->\n" .
                "test_key#:#" . chr(0xFF) . chr(0xFE) . "\n"
            );

            $mockDir = $this->createMockComponentDirectory($this->tempComponentDir, 'test');
            $manager = new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                new MainLanguageFileDirectory(),
                $mockDir
            );
            $setup_language = new ilSetupLanguage('en', $manager);

            $this->assertFalse($this->callCheckLanguage($setup_language, 'en'));
        } finally {
            $this->cleanupTempDir();
        }
    }

    public function testGetInvalidLocalLanguageFilesReturnsEmptyForValidFile(): void
    {
        $fullPath = __DIR__ . '/../../../../' . $this->tempComponentDir;
        mkdir($fullPath, 0777, true);
        file_put_contents($fullPath . '/ilias_en.lang.local', 'custom');

        $manager = new LanguageFileDirectoryManager(
            $this->createMockCustomizingDirectory(),
            new MainLanguageFileDirectory()
        );
        $setup_language = new ilSetupLanguage('en', $manager);

        $this->assertSame([], $setup_language->getInvalidLocalLanguageFiles(['en']));
    }

    public function testGetInvalidLocalLanguageFilesIgnoresMissingFile(): void
    {
        $fullPath = __DIR__ . '/../../../../' . $this->tempComponentDir;
        mkdir($fullPath, 0777, true);

        $manager = new LanguageFileDirectoryManager(
            $this->createMockCustomizingDirectory(),
            new MainLanguageFileDirectory()
        );
        $setup_language = new ilSetupLanguage('en', $manager);

        $this->assertSame([], $setup_language->getInvalidLocalLanguageFiles(['en']));
    }

    public function testGetInvalidLocalLanguageFilesReturnsMalformedFileName(): void
    {
        $fullPath = __DIR__ . '/../../../../' . $this->tempComponentDir;
        mkdir($fullPath, 0777, true);
        file_put_contents($fullPath . '/ilias_en.lang.locl', 'custom');

        $manager = new LanguageFileDirectoryManager(
            $this->createMockCustomizingDirectory(),
            new MainLanguageFileDirectory()
        );
        $setup_language = new ilSetupLanguage('en', $manager);

        $this->assertSame(
            ['ilias_en.lang.locl'],
            $setup_language->getInvalidLocalLanguageFiles(['en'])
        );
    }

    private function createDatabaseMock(): ilDBInterface
    {
        $db = $this->createMock(ilDBInterface::class);
        $db->method('nextId')->willReturn(42);
        $db->method('quote')->willReturnCallback(
            static fn(mixed $value): string => "'" . (string) $value . "'"
        );
        $db->method('now')->willReturn('NOW()');
        $db->method('manipulate')->willReturn(1);

        return $db;
    }

    public function testRegisterInstalledLanguageInsertsUnknownLanguage(): void
    {
        $db = $this->createDatabaseMock();
        $db->expects($this->once())
            ->method('manipulate')
            ->with($this->logicalAnd(
                $this->stringContains('INSERT INTO object_data'),
                $this->stringContains("'de'"),
                $this->stringContains("'installed'")
            ))
            ->willReturn(1);

        $this->newLangSetupDe->setDbHandler($db);
        $this->newLangSetupDe->registerInstalledLanguage('de', [], []);
    }

    public function testRegisterInstalledLanguageInsertsAsInstalledLocalWhenLocalFileIsPresent(): void
    {
        $db = $this->createDatabaseMock();
        $db->expects($this->once())
            ->method('manipulate')
            ->with($this->stringContains("'installed_local'"));

        $this->newLangSetupDe->setDbHandler($db);
        $this->newLangSetupDe->registerInstalledLanguage('de', [], ['de']);
    }

    public function testRegisterInstalledLanguageUpdatesKnownLanguage(): void
    {
        $db = $this->createDatabaseMock();
        $db->expects($this->once())
            ->method('manipulate')
            ->with($this->logicalAnd(
                $this->stringContains('UPDATE object_data'),
                $this->stringContains("'installed'"),
                $this->stringContains('obj_id = ' . "'7'")
            ))
            ->willReturn(1);

        $this->newLangSetupDe->setDbHandler($db);
        $this->newLangSetupDe->registerInstalledLanguage(
            'de',
            ['de' => ['obj_id' => 7, 'status' => 'not_installed']],
            []
        );
    }
}
