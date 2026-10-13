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

use ILIAS\Logging\Logger\LoggerInterface;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Language\Setup\LanguageDataNotSavedException;
use ILIAS\Language\Setup\LanguageInstallationManager;
use ILIAS\Language\Setup\NullTranslationCache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Fixtures of the LanguageInstallationManager tests for modules migrated to PO/MO ("pilot"). The
 * database is a recording mock: quote() hands out placeholder tokens and remembers the real values, so
 * the written lng_data rows (including a NULL local_change) and lng_modules arrays can be asserted as
 * data instead of as SQL strings.
 */
abstract class LanguageInstallationManagerMigratedModulesTestCase extends TestCase
{
    protected const string NOW = '2026-01-02 03:04:05';
    protected const string MODULE = 'pilot';

    protected string $root;
    protected LanguageFileDirectory $directory;

    /** @var list<mixed> */
    protected array $quoted = [];
    /** @var list<string> */
    protected array $queries = [];
    /** @var list<array<string, string>>|null rows the collation check reads, null = what was written */
    protected ?array $collation_rows = null;
    /** @var list<array<string, string>> */
    protected array $pending_rows = [];
    /** @var list<array<string, string>> rows of "SELECT module, identifier, local_change FROM lng_data" */
    protected array $local_change_rows = [];

    /** @var array<string, array<string, string>> */
    protected array $db_local_changes = [];
    /** @var array<string, array<string, string>> */
    protected array $db_entries = [];
    /** @var list<string> */
    protected array $logged_warnings = [];
    private bool $expects_manager_warning = false;

    protected function setUp(): void
    {
        // never the build of the installation, see MigratedPoFixture::resetRuntime()
        MigratedPoFixture::resetRuntime();
        $this->root = sys_get_temp_dir() . '/ilias_lim_test_' . bin2hex(random_bytes(6));
        mkdir($this->root . '/lang/customizing', 0775, true);
        mkdir($this->root . '/client-data', 0775, true);
        $this->directory = MigratedPoFixture::directory(self::MODULE, 'components/pilot/lang/');
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->root);
        MigratedPoFixture::resetRuntime();
    }

    protected function db(): ilDBInterface
    {
        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(function (mixed $value, string $type): string {
            $this->quoted[] = $value;
            return 'Q' . (count($this->quoted) - 1) . 'Q';
        });
        $db->method('in')->willReturnCallback(
            static fn(string $field, array $values, bool $negate = false): string =>
                $field . ($negate ? " NOT IN ('" : " IN ('") . implode("','", $values) . "')"
        );
        // the "apply local changes" write runs under a table lock: the callable runs right away
        $db->method('buildAtomQuery')->willReturnCallback(function () use ($db): ilAtomQuery {
            $atom_query = $this->createStub(ilAtomQuery::class);
            $atom_query->method('addQueryCallable')->willReturnCallback(
                static function (callable $query) use ($db): void {
                    $query($db);
                }
            );
            return $atom_query;
        });
        $db->method('manipulate')->willReturnCallback(function (string $query): int {
            $this->queries[] = $query;
            return 1;
        });
        $db->method('query')->willReturnCallback(function (string $query) use ($statement): ilDBStatement {
            $this->queries[] = $query;
            if (str_starts_with($query, 'SELECT module, identifier, local_change FROM lng_data')) {
                $this->pending_rows = $this->local_change_rows;
                return $statement;
            }
            $this->pending_rows = $this->collation_rows ?? array_map(
                static fn(string $module, array $lang_array): array => ['module' => $module, 'lang_array' => serialize($lang_array)],
                array_keys($this->lngModules()),
                $this->lngModules()
            );
            return $statement;
        });
        $db->method('fetchAssoc')->willReturnCallback(fn(): ?array => array_shift($this->pending_rows));
        $db->method('now')->willReturn('NOW()');
        $db->method('nextId')->willReturn(1);

        return $db;
    }

    protected function repository(): InstalledLanguageRepository
    {
        $repository = $this->createStub(InstalledLanguageRepository::class);
        $repository->method('getLocalChanges')->willReturnCallback(
            fn(string $lang_key, string $min_date = ''): array => $min_date === '' ? $this->db_local_changes : []
        );
        $repository->method('getLanguageEntries')->willReturnCallback(fn(): array => $this->db_entries);

        return $repository;
    }

    protected function manager(
        ?\Closure $client_data_dir_resolver = null,
        ?\Closure $language_cache_invalidator = null,
        bool $with_resolver = true,
        bool $with_logger = true,
        ?LoggerInterface $logger = null
    ): LanguageInstallationManager {
        $client_data_dir = $this->root . '/client-data';
        return new LanguageInstallationManager(
            $this->db(),
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                new MainLanguageFileDirectory(),
                $this->directory
            ),
            $this->root,
            $this->repository(),
            static fn(): \DateTimeImmutable => new \DateTimeImmutable(self::NOW, new \DateTimeZone('UTC')),
            $language_cache_invalidator === null
                ? new NullTranslationCache()
                : new CallbackTranslationCache($language_cache_invalidator),
            $with_resolver ? ($client_data_dir_resolver ?? static fn(): string => $client_data_dir) : null,
            $logger ?? ($with_logger ? $this->recordingLogger() : null)
        );
    }

    private function recordingLogger(): LoggerInterface
    {
        $logger = $this->createStub(LoggerInterface::class);
        $logger->method('warning')->willReturnCallback(
            function (string|\Stringable $message): void {
                $this->logged_warnings[] = (string) $message;
            }
        );

        return $logger;
    }

    protected function expectManagerWarning(): void
    {
        $this->expects_manager_warning = true;
    }

    protected function assertPostConditions(): void
    {
        parent::assertPostConditions();
        if ($this->expects_manager_warning) {
            $this->assertNotSame([], $this->logged_warnings, 'the manager logged no warning');
        }
    }

    protected function loggedWarnings(): string
    {
        return implode("\n", $this->logged_warnings);
    }

    /**
     * @param array<string, string|array<string, mixed>> $entries
     */
    protected function shipPo(array $entries, string $lang_key = 'de'): void
    {
        MigratedPoFixture::writePo(
            $this->root . '/components/pilot/lang/pilot_' . $lang_key . '.po',
            MigratedPoFixture::catalog(self::MODULE, $entries)
        );
    }

    /**
     * @param array<string, string|array<string, mixed>> $entries
     */
    protected function seedOverlay(array $entries): void
    {
        MigratedPoFixture::writePair($this->overlayBase(), MigratedPoFixture::catalog(self::MODULE, $entries));
    }

    protected function overlayBase(): string
    {
        return $this->root . '/client-data/lang/pilot/de/pilot_de';
    }

    protected function overlayPo(): TranslationCatalog
    {
        return MigratedPoFixture::readPo($this->overlayBase() . '.po');
    }

    /**
     * Shipped `.po` plus overlay delta, as served (MigratedLanguageFileSync::loadModuleTranslations()) -
     * the overlay itself only holds the entries that differ from the shipped `.po`.
     *
     * @return array<string, array{value: string, local_change: bool, local_change_date: ?string, original: ?string}>
     */
    protected function effective(): array
    {
        return MigratedLanguageFileSync::loadModuleTranslations(
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $this->directory),
            'de',
            self::MODULE,
            $this->root . '/client-data',
            $this->root
        ) ?? [];
    }

    /**
     * @param list<string> $lines raw lines after the header marker
     */
    protected function writeLang(string $relative_file, array $lines): void
    {
        $file = $this->root . '/' . $relative_file;
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }
        file_put_contents($file, "header\n<!-- language file start -->\n" . implode("\n", $lines) . "\n");
    }

    protected function resolveToken(string $token): mixed
    {
        return $this->quoted[(int) trim($token, 'Q')];
    }

    /**
     * @return array<string, array{value: string, local_change: ?string}> "module|identifier" => row
     *         (last write wins, like ON DUPLICATE KEY UPDATE)
     */
    protected function lngData(): array
    {
        $rows = [];
        foreach ($this->queries as $query) {
            if (str_starts_with($query, /** @lang text */ 'INSERT INTO lng_data')) {
                preg_match_all('/\((Q\d+Q),(Q\d+Q),(Q\d+Q),(Q\d+Q),(Q\d+Q),(Q\d+Q)\)/', $query, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $key = $this->resolveToken($match[1]) . '|' . $this->resolveToken($match[2]);
                    $rows[$key] = ['value' => $this->resolveToken($match[4]), 'local_change' => $this->resolveToken($match[5])];
                }
            } elseif (str_starts_with($query, /** @lang text */ 'DELETE FROM lng_data')) {
                $rows = $this->applyLngDataDelete($query, $rows);
            }
        }

        return $rows;
    }

    /**
     * Applies one recorded "DELETE FROM lng_data" to $rows, in the two shapes LanguageInstallationManager issues:
     * the bulk delete of unchanged rows of a migrated module ("... local_change IS NULL AND module IN (...)") and the
     * per-module delete of dropped identifiers ("... AND module = ? AND identifier IN (...)"). Both use $ilDB->in(),
     * whose stub embeds the raw values literally instead of quote() tokens.
     *
     * @param array<string, array{value: string, local_change: ?string}> $rows
     * @return array<string, array{value: string, local_change: ?string}>
     */
    protected function applyLngDataDelete(string $query, array $rows): array
    {
        // the purge after "apply local changes" wrote a migrated module: its unchanged rows that
        // were not written
        if (preg_match(
            '/^DELETE FROM lng_data WHERE lang_key = Q\d+Q AND local_change IS NULL AND module = (Q\d+Q)(?: AND identifier NOT IN \(\'(.*)\'\))?$/',
            $query,
            $matches
        ) === 1) {
            $module = $this->resolveToken($matches[1]);
            $written = isset($matches[2]) ? explode("','", $matches[2]) : [];
            foreach ($rows as $key => $row) {
                [$row_module, $identifier] = explode('|', $key, 2);
                if ($row_module === $module && $row['local_change'] === null && !in_array($identifier, $written, true)) {
                    unset($rows[$key]);
                }
            }
            return $rows;
        }

        if (preg_match(
            '/^DELETE FROM lng_data WHERE lang_key = Q\d+Q AND local_change IS NULL AND module IN \(\'(.*)\'\)$/',
            $query,
            $matches
        ) === 1) {
            $modules = explode("','", $matches[1]);
            foreach ($rows as $key => $row) {
                [$module] = explode('|', $key, 2);
                if (in_array($module, $modules, true) && $row['local_change'] === null) {
                    unset($rows[$key]);
                }
            }
            return $rows;
        }

        if (preg_match(
            '/^DELETE FROM lng_data WHERE lang_key = Q\d+Q AND module = (Q\d+Q) AND identifier IN \(\'(.*)\'\)$/',
            $query,
            $matches
        ) === 1) {
            $module = $this->resolveToken($matches[1]);
            foreach (explode("','", $matches[2]) as $identifier) {
                unset($rows[$module . '|' . $identifier]);
            }
            return $rows;
        }

        return $rows;
    }

    /**
     * Identifiers of $module dropped via the per-module "DELETE FROM lng_data ... AND identifier IN (...)", as
     * opposed to keys merely absent because they were never touched.
     *
     * @return list<string>
     */
    protected function droppedIdentifiers(string $module): array
    {
        $dropped = [];
        foreach ($this->queries as $query) {
            if (
                preg_match(
                    '/^DELETE FROM lng_data WHERE lang_key = Q\d+Q AND module = (Q\d+Q) AND identifier IN \(\'(.*)\'\)$/',
                    $query,
                    $matches
                ) === 1
                && $this->resolveToken($matches[1]) === $module
            ) {
                $dropped = array_merge($dropped, explode("','", $matches[2]));
            }
        }

        return $dropped;
    }

    /**
     * @return array<string, array<string, string>> module => identifier => value
     */
    protected function lngModules(): array
    {
        $modules = [];
        foreach ($this->queries as $query) {
            if (!str_starts_with($query, /** @lang text */ 'INSERT INTO lng_modules')) {
                continue;
            }
            preg_match_all('/\((Q\d+Q),(Q\d+Q),(Q\d+Q)\)/', $query, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $modules[$this->resolveToken($match[1])] = unserialize($this->resolveToken($match[3]));
            }
        }

        return $modules;
    }

    /**
     * What error_log() wrote during this test so far - PHPUnit redirects error_log to a capture file
     * per test (call expectErrorLog() in tests that expect logging, so it is not echoed).
     */
    protected function errorLog(): string
    {
        $file = (string) ini_get('error_log');
        return $file !== '' && is_file($file) ? (string) file_get_contents($file) : '';
    }

    protected const string TWO_FORMS = 'nplurals=2; plural=(n != 1);';
    protected const string THREE_FORMS = 'nplurals=3; plural=(n == 1 ? 0 : n >= 2 && n <= 4 ? 1 : 2);';

    /**
     * @param array<string, list<string>|string> $entries
     */
    protected function shipPluralPo(array $entries, string $header): void
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $catalog->setHeader('Plural-Forms', $header);
        foreach ($entries as $identifier => $value) {
            $entry = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry(self::MODULE, (string) $identifier);
            if (is_array($value)) {
                $entry->setPlural($identifier . '_plural', $value);
            } else {
                $entry->translate($value);
            }
            $catalog->add($entry);
        }
        MigratedPoFixture::writePo($this->root . '/components/pilot/lang/pilot_de.po', $catalog);
    }

    /**
     * @param array<string, string> $flat_values
     */
    protected function writeLocalOverlay(array $flat_values): void
    {
        MigratedLanguageFileSync::sync(
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $this->directory),
            $this->root,
            'de',
            self::MODULE,
            $flat_values,
            $this->root . '/client-data'
        );
    }

    /**
     * @param array<string, string> $entries identifier => local value (original "Hallo")
     * @param list<string> $merged identifiers marked as merged into the shipped file
     */
    protected function seedOverlayMarkedAsMerged(array $entries, array $merged): void
    {
        $details = [];
        foreach ($entries as $identifier => $value) {
            $details[$identifier] = ['value' => $value, 'original' => 'Hallo', 'local_change' => '2025-04-04T04:04:04Z'];
        }
        $catalog = MigratedPoFixture::catalog(self::MODULE, $details);
        foreach ($merged as $identifier) {
            LocalChangeComments::setMergedIntoShipped($catalog->find(self::MODULE, $identifier), true);
        }
        MigratedPoFixture::writePair($this->overlayBase(), $catalog);
    }
}
