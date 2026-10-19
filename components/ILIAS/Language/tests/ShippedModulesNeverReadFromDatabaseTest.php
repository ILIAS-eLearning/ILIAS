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
use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\MigratedTranslations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * A module of the ILIAS components (built or with a `lang/<module>.pot`) is never read from lng_data/lng_modules, also
 * without CLIENT_DATA_DIR and without the component graph (Setup, CLI); only other modules (plugins) are.
 *
 * ILIAS_ABSOLUTE_PATH is a temporary tree with its own ilias.ini.php, so separate processes (neither constant can be redefined).
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ShippedModulesNeverReadFromDatabaseTest extends ilLanguageBaseTestCase
{
    private const string LANG_DIRECTORY = 'components/Vendor/Comp/lang/';

    private string $root;
    private string $client_data_dir;

    protected function setUp(): void
    {
        parent::setUp();
        if (defined('CLIENT_DATA_DIR') || defined('ILIAS_ABSOLUTE_PATH')) {
            $this->markTestSkipped('CLIENT_DATA_DIR/ILIAS_ABSOLUTE_PATH are already defined, the fixture needs its own tree.');
        }

        $this->root = sys_get_temp_dir() . '/ilias_shipped_no_db_' . bin2hex(random_bytes(6));
        $this->client_data_dir = $this->root . '/data/client';
        mkdir($this->client_data_dir, 0775, true);
        file_put_contents(
            $this->root . '/ilias.ini.php',
            "; <?php exit; ?>\n[clients]\ndatadir = \"{$this->root}/data\"\ndefault = \"client\"\n"
        );
        define('ILIAS_ABSOLUTE_PATH', $this->root);
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->root);
        parent::tearDown();
    }

    /**
     * Module "zzs": shipped template, de built (greeting, other); no lng_data access at all.
     */
    private function buildShippedModule(bool $build = true, bool $with_fr_po = false): void
    {
        $directory = $this->root . '/' . self::LANG_DIRECTORY;
        MigratedPoFixture::writePo($directory . 'zzs_de.po', MigratedPoFixture::catalog('zzs', ['greeting' => 'Hallo', 'other' => 'Anders']));
        file_put_contents($directory . 'zzs.pot', '');
        if ($build) {
            MigratedPoFixture::build(
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), MigratedPoFixture::directory('zzs', self::LANG_DIRECTORY)),
                $this->root
            );
        }
        if ($with_fr_po) {
            MigratedPoFixture::writePo($directory . 'zzs_fr.po', MigratedPoFixture::catalog('zzs', ['greeting' => 'Bonjour']));
        }
    }

    /**
     * @param list<string>|null $installed_languages
     */
    private function forbidDatabase(?array $installed_languages = null): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->never())->method('query');
        $db->expects($this->never())->method('fetchAssoc');
        $this->setGlobalVariable('ilDB', $db);
        $this->setGlobalVariable('ilClientIniFile', $this->createStub(ilIniFile::class));
        (new ReflectionProperty(ilLanguage::class, 'installed_languages'))->setValue(null, $installed_languages);
    }

    public function testWithoutClientDataDirAndComponentGraphTheBuildAndTheOverlayOfIliasIniAreServed(): void
    {
        $this->buildShippedModule();
        MigratedPoFixture::writeOverlayRevision(
            MigratedLanguageFilePaths::overlayDirectory($this->client_data_dir, 'zzs', 'de') . '/zzs_de',
            MigratedPoFixture::catalog('zzs', ['greeting' => 'Overlay'])
        );
        $this->forbidDatabase();

        $this->assertFalse(defined('CLIENT_DATA_DIR'));
        $this->assertSame('Overlay', ilLanguage::_lookupEntry('de', 'zzs', 'greeting'));
        $this->assertSame('Anders', ilLanguage::_lookupEntry('de', 'zzs', 'other'));
        $this->assertSame('-unknown-', ilLanguage::_lookupEntry('de', 'zzs', 'unknown'));
    }

    /** The Setup creates the client directory and the build in the same process: reset() makes both visible, a missing directory is never remembered. */
    public function testTheOverlayOfIliasIniIsServedAfterTheSetupCreatedBuildAndClientDirectoryAndReset(): void
    {
        rmdir($this->client_data_dir);
        $this->buildShippedModule(false);
        $this->forbidDatabase();

        $this->expectOutputRegex('/There is no build of the language files/');
        $this->assertSame('-greeting-', ilLanguage::_lookupEntry('de', 'zzs', 'greeting'), 'fresh installation: no client directory, no build');

        $this->buildShippedModule();
        mkdir($this->client_data_dir, 0775, true);
        MigratedPoFixture::writeOverlayRevision(
            MigratedLanguageFilePaths::overlayDirectory($this->client_data_dir, 'zzs', 'de') . '/zzs_de',
            MigratedPoFixture::catalog('zzs', ['greeting' => 'Overlay'])
        );
        MigratedTranslations::reset();

        $this->assertSame('Overlay', ilLanguage::_lookupEntry('de', 'zzs', 'greeting'));
    }

    public function testTheClientDataDirectoryIsResolvedOncePerResetAlsoWhenMissing(): void
    {
        rmdir($this->client_data_dir);
        $this->assertNull(MigratedTranslations::clientDataDir());

        mkdir($this->client_data_dir, 0775, true);
        $this->assertNull(MigratedTranslations::clientDataDir(), 'a missing directory is kept until reset(): no ilias.ini.php per call');

        MigratedTranslations::reset();
        $this->assertSame($this->client_data_dir, MigratedTranslations::clientDataDir());

        rmdir($this->client_data_dir);
        $this->assertSame($this->client_data_dir, MigratedTranslations::clientDataDir(), 'a found one is kept');
    }

    /** Decided once per module and language until reset(); next to the template without a directory, else at the directory's path. */
    public function testWhetherAPoIsShippedWithoutABuildIsDecidedOncePerReset(): void
    {
        $this->buildShippedModule(false);
        $directory = MigratedPoFixture::directory('zzs', self::LANG_DIRECTORY);
        $this->assertTrue(MigratedTranslations::shipsPoWithoutBuild('zzs', 'de', null));
        $this->assertTrue(MigratedTranslations::shipsPoWithoutBuild('zzs', 'de', $directory));
        $this->assertFalse(MigratedTranslations::shipsPoWithoutBuild('zzs', 'fr', $directory));
        $this->assertFalse(MigratedTranslations::shipsPoWithoutBuild('zzs', '../de', null), 'no path');

        unlink($this->root . '/' . self::LANG_DIRECTORY . 'zzs_de.po');
        $this->assertTrue(MigratedTranslations::shipsPoWithoutBuild('zzs', 'de', null), 'remembered');

        MigratedTranslations::reset();
        $this->assertFalse(MigratedTranslations::shipsPoWithoutBuild('zzs', 'de', null));
        $this->assertFalse(MigratedTranslations::shipsPoWithoutBuild('zzs', 'de', $directory));
    }

    public function testResetForgetsWhichModulesAreShipped(): void
    {
        $this->assertFalse(MigratedTranslations::isShippedModule('zzs'), 'no template yet');

        $this->buildShippedModule(false);
        $this->assertFalse(MigratedTranslations::isShippedModule('zzs'), 'remembered per module');

        MigratedTranslations::reset();
        $this->assertTrue(MigratedTranslations::isShippedModule('zzs'));
    }

    /** No client directory (fresh installation, Setup): the administration lists the shipped values, no overlay is looked for. */
    public function testTheAdministrationListsTheShippedValuesWithoutAClientDataDirectory(): void
    {
        rmdir($this->client_data_dir);
        $this->buildShippedModule(false);
        $statement = $this->createStub(ilDBStatement::class);
        $statement->method('fetchRow')->willReturn(false);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn(mixed $value): string => "'" . (string) $value . "'");
        $db->method('in')->willReturn("module IN ('placeholder')");
        $db->method('query')->willReturn($statement);
        $this->setGlobalVariable('ilDB', $db);
        $this->setGlobalVariable('lng', (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor());
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), MigratedPoFixture::directory('zzs', self::LANG_DIRECTORY))
        );

        $this->assertSame(['zzs#:#greeting' => 'Hallo', 'zzs#:#other' => 'Anders'], ilObjLanguageExt::_getValues('de'));
    }

    public function testWithoutABuildAShippedModuleHasNoValuesAndTheMissingBuildIsReported(): void
    {
        $this->buildShippedModule(false);
        $this->forbidDatabase();

        $this->expectOutputRegex('/There is no build of the language files/');
        $this->assertSame('-greeting-', ilLanguage::_lookupEntry('de', 'zzs', 'greeting'));
    }

    public function testALanguageWithAPoButNotInTheBuildIsReportedAsNotBuilt(): void
    {
        $this->buildShippedModule(true, true);
        $this->forbidDatabase();

        $this->expectOutputRegex('/ships a language file for "fr" that is not built/');
        $this->assertSame('-greeting-', ilLanguage::_lookupEntry('fr', 'zzs', 'greeting'));
    }

    public function testALanguageWithoutAPoHasNoValuesAndNoReport(): void
    {
        $this->buildShippedModule();
        $this->forbidDatabase();

        $this->assertSame('-greeting-', ilLanguage::_lookupEntry('it', 'zzs', 'greeting'));
        $this->expectOutputString('');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function languagesWithoutServedValues(): array
    {
        return ['not installed' => ['de'], 'invalid key' => ['../de']];
    }

    #[DataProvider('languagesWithoutServedValues')]
    public function testANotInstalledOrInvalidLanguageHasNoValuesNoReportAndNoDatabaseAccess(string $lang_key): void
    {
        $this->buildShippedModule();
        $this->forbidDatabase(['en']);

        $this->assertSame('-greeting-', ilLanguage::_lookupEntry($lang_key, 'zzs', 'greeting'));
        $this->expectOutputString('');
    }

    public function testAModuleOfNoComponentIsStillReadFromTheDatabase(): void
    {
        $this->buildShippedModule();
        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn(mixed $value): string => "'" . (string) $value . "'");
        $db->method('query')->willReturn($statement);
        $db->method('fetchAssoc')->willReturn(['value' => 'from the database']);
        $this->setGlobalVariable('ilDB', $db);
        $this->setGlobalVariable('ilClientIniFile', $this->createStub(ilIniFile::class));

        $this->assertSame('from the database', ilLanguage::_lookupEntry('de', 'xplugin', 'greeting'));
    }
}
