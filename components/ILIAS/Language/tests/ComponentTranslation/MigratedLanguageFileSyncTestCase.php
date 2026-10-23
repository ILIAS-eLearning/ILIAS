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
use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Fixtures of the MigratedLanguageFileSync tests. Two roots as in production: the SHIPPED `.po` below the ILIAS root
 * ($this->fixture_directory, never written by the class under test) and the OVERLAY `.po`/`.mo` below a throwaway
 * client data directory ($this->client_data_dir).
 */
abstract class MigratedLanguageFileSyncTestCase extends TestCase
{
    protected const string MODULE = 'stest';

    protected string $fixture_directory;
    protected string $client_data_dir;
    protected string $ilias_root;
    protected LanguageFileDirectory $directory;
    protected LanguageFileDirectoryManager $manager;

    protected function setUp(): void
    {
        // never the build of the installation, see MigratedPoFixture::resetRuntime()
        MigratedPoFixture::resetRuntime();
        parent::setUp();

        $this->ilias_root = (string) realpath(__DIR__ . '/../../../../../');

        $this->fixture_directory = __DIR__ . '/tmp-sync-fixtures-' . bin2hex(random_bytes(4));
        mkdir($this->fixture_directory, 0775, true);
        $this->client_data_dir = sys_get_temp_dir() . '/ilias_mlfs_test_' . bin2hex(random_bytes(4));
        mkdir($this->client_data_dir, 0775, true);

        $this->directory = MigratedPoFixture::directory(
            self::MODULE,
            'components/ILIAS/Language/tests/ComponentTranslation/' . basename($this->fixture_directory) . '/'
        );
        $this->manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $this->directory);
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->fixture_directory);
        MigratedPoFixture::removeDirectory($this->client_data_dir);
        MigratedPoFixture::resetRuntime();

        parent::tearDown();
    }

    /**
     * @param array<string, string|array<string, mixed>> $entries see MigratedPoFixture::catalog()
     */
    protected function seedShipped(array $entries, string $lang_key = 'de', string $module = self::MODULE): void
    {
        MigratedPoFixture::writePo($this->shippedPo($lang_key, $module), MigratedPoFixture::catalog($module, $entries));
    }

    /**
     * @param array<string, string|array<string, mixed>> $entries see MigratedPoFixture::catalog()
     */
    protected function seedOverlay(array $entries, string $lang_key = 'de'): void
    {
        MigratedPoFixture::writePair($this->overlayBase($lang_key), MigratedPoFixture::catalog(self::MODULE, $entries));
    }

    protected function shippedPo(string $lang_key = 'de', string $module = self::MODULE): string
    {
        return $this->fixture_directory . '/' . $module . '_' . $lang_key . '.po';
    }

    protected function overlayBase(string $lang_key = 'de'): string
    {
        return MigratedPoFixture::overlayBase($this->client_data_dir, $this->directory, $lang_key);
    }

    protected function overlayPo(): TranslationCatalog
    {
        return MigratedPoFixture::readPo($this->overlayBase() . '.po');
    }

    /**
     * @param array<int|string, string> $entries
     */
    protected function sync(array $entries, bool $refresh = false, ?string $client_data_dir = 'default'): void
    {
        MigratedLanguageFileSync::sync(
            $this->manager,
            $this->ilias_root,
            'de',
            self::MODULE,
            $entries,
            $client_data_dir === 'default' ? $this->client_data_dir : $client_data_dir,
            $refresh
        );
    }

    /**
     * @return list<string>
     */
    protected function overlayIdentifiers(): array
    {
        return array_map(static fn($entry): string => $entry->getId(), $this->overlayPo()->getEntries());
    }

    protected function overlayDirectory(): string
    {
        return dirname($this->overlayBase());
    }

    /**
     * @param list<string> $lang_keys
     * @return list<string>
     */
    protected function findUnwritable(array $lang_keys = ['de'], ?string $client_data_dir = 'default', ?LanguageFileDirectoryManager $manager = null): array
    {
        return MigratedLanguageFileSync::findUnwritableOverlayDirectories(
            $manager ?? $this->manager,
            $this->ilias_root,
            $lang_keys,
            $client_data_dir === 'default' ? $this->client_data_dir : $client_data_dir
        );
    }


    /**
     * @return array<string, array{0: bool}>
     */
    public static function refreshFlags(): array
    {
        return ['admin edit (refresh = false)' => [false], 'reconciling write (refresh = true)' => [true]];
    }
}
