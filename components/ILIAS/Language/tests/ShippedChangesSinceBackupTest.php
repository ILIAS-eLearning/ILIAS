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
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * getShippedChangesSinceBackup() (filter "conflicts" of the developer mode): `.lang` entries that differ from their backup,
 * without PO modules (whose lines there are not their source), plus entries of a migrated module's shipped `.po` that differ
 * from its backup. Separate processes: ilLanguageFile caches the global language file per key in a static.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ShippedChangesSinceBackupTest extends ilLanguageBaseTestCase
{
    private const string SEPARATOR = '#:#';

    private string $temp_directory;
    private string $shipped_relative_path;
    private LanguageFileDirectoryManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        foreach (['ILIAS_HTTP_PATH' => 'http://localhost', 'ILIAS_VERSION' => 'test'] as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
        $this->temp_directory = sys_get_temp_dir() . '/ilias_shipped_changes_' . bin2hex(random_bytes(4));
        mkdir($this->temp_directory . '/lang', 0775, true);
        mkdir($this->temp_directory . '/backup', 0775, true);
        $this->shipped_relative_path = 'components/ILIAS/Language/tests/tmp-shipped-changes-' . bin2hex(random_bytes(4)) . '/';

        $language = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        $language->lang_path = $this->temp_directory . '/lang';
        $this->setGlobalVariable('lng', $language);

        $this->manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            MigratedPoFixture::directory('mtest', $this->shipped_relative_path)
        );
        $this->setGlobalVariable(LanguageFileDirectoryManager::class, $this->manager);
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeShippedDirectory($this->shipped_relative_path);
        MigratedPoFixture::removeDirectory($this->temp_directory);

        parent::tearDown();
    }

    /** @param list<string> $lines module#:#identifier#:#value */
    private function writeLang(string $file, array $lines): void
    {
        file_put_contents(
            $file,
            "HEADER\n<!-- language file start -->\n" . implode("\n", $lines) . "\n"
        );
    }

    /** @param array<string, string> $entries identifier => value */
    private function shipPo(array $entries): void
    {
        MigratedPoFixture::writeShippedPo($this->shipped_relative_path, 'mtest', 'de', MigratedPoFixture::catalog('mtest', $entries));
    }

    /** @param array<string, string> $entries identifier => value */
    private function backUpPo(array $entries): void
    {
        MigratedPoFixture::writePo($this->temp_directory . '/backup/mtest_de.po', MigratedPoFixture::catalog('mtest', $entries));
    }

    private function languageObject(): ilObjLanguageExt
    {
        $object = (new ReflectionClass(ilObjLanguageExt::class))->newInstanceWithoutConstructor();
        $object->key = 'de';
        $object->separator = self::SEPARATOR;
        (new ReflectionProperty(ilObjLanguage::class, 'language_file_directory_manager'))->setValue($object, $this->manager);

        return $object;
    }

    private function formerFile(): ilLanguageFile
    {
        $former = new ilLanguageFile($this->temp_directory . '/former.lang', 'de', 'global');
        $this->assertTrue($former->read());

        return $former;
    }

    public function testChangesOfAnOrdinaryModuleAndOfAMigratedModuleAreBothReportedUnderModuleSeparatorIdentifier(): void
    {
        $this->writeLang($this->temp_directory . '/former.lang', [
            'plainmod#:#changed#:#Old',
            'plainmod#:#same#:#Same',
            'mtest#:#greeting#:#Lang line before',
        ]);
        $this->writeLang($this->temp_directory . '/lang/ilias_de.lang', [
            'plainmod#:#changed#:#New',
            'plainmod#:#same#:#Same',
            'plainmod#:#added#:#Added',
        // the .lang line of a PO module is not its source: it must not count although it differs from the backup
            'mtest#:#greeting#:#Lang line after',
        ]);
        $this->backUpPo(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->shipPo(['greeting' => 'Hallo (neu)', 'farewell' => 'Tschüss']);

        $changes = $this->languageObject()->getShippedChangesSinceBackup($this->formerFile(), $this->temp_directory . '/backup');

        ksort($changes);
        $this->assertSame(
            [
                'mtest#:#greeting' => 'Hallo (neu)',
                'plainmod#:#added' => 'Added',
                'plainmod#:#changed' => 'New',
            ],
            $changes
        );
    }

    public function testAMigratedModuleWithoutBackupContributesNothingAndItsLangLinesStayExcluded(): void
    {
        $this->writeLang($this->temp_directory . '/former.lang', ['plainmod#:#a#:#1']);
        $this->writeLang($this->temp_directory . '/lang/ilias_de.lang', [
            'plainmod#:#a#:#2',
            'mtest#:#greeting#:#Lang line only',
        ]);
        $this->shipPo(['greeting' => 'Hallo']);

        $changes = $this->languageObject()->getShippedChangesSinceBackup($this->formerFile(), $this->temp_directory . '/backup');

        $this->assertSame(['plainmod#:#a' => '2'], $changes);
    }
}
