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

use ILIAS\ILIASObject\Properties\CoreProperties\TitleAndDescription;
use ILIAS\ILIASObject\Properties\Properties;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\Setup\InstalledLanguageDatabaseRepository;
use ILIAS\Language\Setup\FixedInstallationRoot;
use ILIAS\Language\Setup\LanguageInstallationManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * "Remove local changes" (ilObjLanguage::removeLocalChanges()) for PO/MO modules: it flushes the database and
 * insertLanguageForRemovingLocalChanges() rebuilds each migrated overlay from its shipped `.po`. The ilObjLanguage
 * constructor is bypassed (it needs a full ilObject bootstrap); only update() and the property storage are replaced.
 */
class ResetMigratedLocalChangesTest extends ilLanguageBaseTestCase
{
    private string $root;
    private string $client_data_dir;
    /** @var list<string> */
    private array $queries = [];
    /** @var list<string> */
    private array $invalidated = [];
    private bool $expect_update = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir() . '/ilias_reset_test_' . bin2hex(random_bytes(6));
        mkdir($this->root . '/lang/customizing', 0775, true);
        file_put_contents($this->root . '/lang/ilias_de.lang', "<!-- language file start -->\ncommon#:#yes#:#Ja\n");
        $this->client_data_dir = $this->root . '/client-data';
        mkdir($this->client_data_dir);

        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(
            static fn(mixed $value): string => $value === null ? 'NULL' : "'" . (string) $value . "'"
        );
        $db->method('in')->willReturnCallback(
            static fn(string $field, array $values): string => $field . " IN ('" . implode("','", $values) . "')"
        );
        $db->method('manipulate')->willReturnCallback(function (string $query): int {
            $this->queries[] = $query;
            return 1;
        });
        $db->method('query')->willReturn($this->createStub(ilDBStatement::class));
        $this->setGlobalVariable('ilDB', $db);

        MigratedPoFixture::resetRuntime();
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->root);

        parent::tearDown();
    }

    private function directory(string $module): LanguageFileDirectory
    {
        return MigratedPoFixture::directory($module, 'components/' . $module . '/lang/');
    }

    /** @param array<string, string|array<string, mixed>> $entries */
    private function shipPo(string $module, array $entries): void
    {
        MigratedPoFixture::writePo(
            $this->root . '/components/' . $module . '/lang/' . $module . '_de.po',
            MigratedPoFixture::catalog($module, $entries)
        );
    }

    /** @param array<string, string|array<string, mixed>> $entries */
    private function seedOverlay(string $module, array $entries): void
    {
        MigratedPoFixture::writePair($this->overlayBase($module), MigratedPoFixture::catalog($module, $entries));
    }

    private function overlayBase(string $module): string
    {
        return $this->client_data_dir . '/lang/' . $module . '/de/' . $module . '_de';
    }

    private function languageObject(
        string $status,
        ?Closure $client_data_dir_resolver,
        LanguageFileDirectory ...$directories
    ): ilObjLanguage&MockObject {
        $manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            new MainLanguageFileDirectory(),
            ...$directories
        );
        $db = $GLOBALS['ilDB'];
        $repository = new InstalledLanguageDatabaseRepository($db, $manager, new FixedInstallationRoot($this->root));
        $installation_manager = new LanguageInstallationManager(
            $db,
            $manager,
            $this->root,
            $repository,
            null,
            new CallbackTranslationCache(function (string $lang_key): void {
                $this->invalidated[] = $lang_key;
            }),
            $client_data_dir_resolver
        );

        $properties = $this->createStub(Properties::class);
        $properties->method('getPropertyTitleAndDescription')->willReturn(new TitleAndDescription());
        $properties->method('withPropertyTitleAndDescription')->willReturnSelf();

        $object = $this->createPartialMock(ilObjLanguage::class, ['update', 'getObjectProperties']);
        $object->method('getObjectProperties')->willReturn($properties);
        $object->expects(str_starts_with($status, 'installed') && $this->expect_update ? $this->once() : $this->never())
            ->method('update');
        $object->key = 'de';
        $object->status = $status;
        foreach (['language_file_directory_manager' => $manager, 'repository' => $repository, 'manager' => $installation_manager] as $name => $value) {
            (new ReflectionProperty(ilObjLanguage::class, $name))->setValue($object, $value);
        }

        return $object;
    }

    private function resolver(): Closure
    {
        $client_data_dir = $this->client_data_dir;
        return static fn(): string => $client_data_dir;
    }

    public function testResetsLocalValuesAndRemovesLocallyAddedVariablesFromTheOverlay(): void
    {
        $this->shipPo('rtest', [
            'greeting' => 'Hallo',
            'hint' => ['value' => 'Hint', 'fuzzy' => true],
        ]);
        $this->seedOverlay('rtest', [
            'greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z'],
            'hint' => ['value' => 'Hinweis', 'original' => 'Hint', 'local_change' => '2020-01-01T00:00:00Z'],
            'added_locally' => ['value' => 'Eigene Variable', 'local_change' => '2020-01-01T00:00:00Z'],
        ]);
        $object = $this->languageObject('installed', $this->resolver(), $this->directory('rtest'));

        $this->assertTrue($object->removeLocalChanges());

        $this->assertFileDoesNotExist($this->overlayBase('rtest') . '.po');
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase('rtest')));
        $this->assertFileExists(MigratedPoFixture::overlayLock($this->overlayBase('rtest')), 'the lock file is only removed on uninstall');
        $this->assertSame('installed', $object->getDescription());
    }

    public function testFlushesTheWholeLanguageIncludingLocalChangesAndInvalidatesTheCache(): void
    {
        $this->shipPo('rtest', ['greeting' => 'Hallo']);
        $object = $this->languageObject('installed', $this->resolver(), $this->directory('rtest'));

        $object->removeLocalChanges();

        $this->assertContains("DELETE FROM lng_data WHERE lang_key = 'de'", $this->queries, 'local changes are deleted too');
        $this->assertContains("DELETE FROM lng_modules WHERE lang_key = 'de'", $this->queries);
        $this->assertSame(['de'], $this->invalidated);
    }

    public function testResetsEveryMigratedModuleIndependently(): void
    {
        $this->shipPo('rone', ['greeting' => 'Eins']);
        $this->shipPo('rtwo', ['greeting' => 'Zwei']);
        $this->seedOverlay('rone', ['greeting' => ['value' => 'Lokal 1', 'original' => 'Eins', 'local_change' => '2020-01-01T00:00:00Z']]);
        $this->seedOverlay('rtwo', ['greeting' => ['value' => 'Lokal 2', 'original' => 'Zwei', 'local_change' => '2020-01-01T00:00:00Z']]);
        $object = $this->languageObject('installed', $this->resolver(), $this->directory('rone'), $this->directory('rtwo'));

        $object->removeLocalChanges();

        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase('rone')));
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase('rtwo')));
    }

    /** A directory without a shipped `.po` for the language is not migrated for it: no overlay is created, the other module is still reset. */
    public function testSkipsAModuleWithoutShippedPoForTheLanguage(): void
    {
        $this->shipPo('rtest', ['greeting' => 'Hallo']);
        $this->seedOverlay('rtest', ['greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z']]);
        $object = $this->languageObject('installed', $this->resolver(), $this->directory('rtest'), $this->directory('nopo'));

        $this->assertTrue($object->removeLocalChanges());

        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase('rtest')));
        $this->assertFileDoesNotExist($this->overlayBase('nopo') . '.po');
    }

    public function testWithoutClientDataDirTheDatabaseIsResetButNoOverlayIsWritten(): void
    {
        $this->shipPo('rtest', ['greeting' => 'Hallo']);
        $object = $this->languageObject('installed', static fn(): ?string => null, $this->directory('rtest'));

        $this->assertTrue($object->removeLocalChanges());

        $this->assertDirectoryDoesNotExist($this->client_data_dir . '/lang');
        $this->assertNotEmpty(array_filter($this->queries, static fn(string $q): bool => str_starts_with($q, /** @lang text */ 'INSERT INTO lng_modules')));
    }

    public function testDoesNothingForALanguageThatIsNotInstalled(): void
    {
        $this->shipPo('rtest', ['greeting' => 'Hallo']);
        $this->seedOverlay('rtest', ['greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z']]);
        $object = $this->languageObject('not_installed', $this->resolver(), $this->directory('rtest'));

        $this->assertFalse($object->removeLocalChanges());

        $this->assertSame([], $this->queries);
        $this->assertSame(['greeting' => 'Servus'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase('rtest'))));
    }

    public function testRefusesALanguageWithAnUnparsableShippedPoBeforeFlushingAnything(): void
    {
        mkdir($this->root . '/components/rtest/lang', 0775, true);
        file_put_contents($this->root . '/components/rtest/lang/rtest_de.po', "msgid \"kaputt\n");
        $this->seedOverlay('rtest', ['greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z']]);
        $this->expect_update = false;
        $object = $this->languageObject('installed', $this->resolver(), $this->directory('rtest'));

        $this->assertFalse($object->removeLocalChanges());

        $this->assertSame([], $this->queries);
        $this->assertSame(['greeting' => 'Servus'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase('rtest'))));
    }
}
