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
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\MigratedTranslations;
use ILIAS\Language\ComponentTranslation\TranslationGettextRepository;
use ILIAS\Language\ComponentTranslation\NativeGettext;
use ILIAS\Language\ComponentTranslation\PluralForms;
use ILIAS\Language\ComponentTranslation\ShippedTranslations;
use ILIAS\Language\ComponentTranslation\ShippedTranslationsBuild;

/**
 * Fixture helpers for PO/MO migrated modules, built with the component's own gettext adapter (TranslationCatalog,
 * TranslationEntry), never the gettext/gettext library directly.
 */
final class MigratedPoFixture
{
    private static bool $artifact_cleanup_registered = false;

    /** The C library never forgets a catalog path it has looked at, so every resetRuntime() gets a directory of its own. */
    private static int $artifact_generation = 0;

    /** @var list<string> */
    private static array $artifact_directories = [];

    /**
     * @param array<string, string|array{value: string, context?: string, original?: string,
     *        local_change?: string, fuzzy?: bool}> $entries identifier => value or details.
     *        'context' defaults to $module, 'original' seeds a LocalChangeComments "original",
     *        'local_change' a raw "local_change: <value>" comment, 'fuzzy' the fuzzy flag.
     */
    public static function catalog(string $module, array $entries): TranslationCatalog
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        foreach ($entries as $identifier => $details) {
            $details = is_array($details) ? $details : ['value' => $details];
            $entry = new TranslationEntry(array_key_exists('context', $details) ? $details['context'] : $module, (string) $identifier);
            $entry->translate($details['value']);
            if (isset($details['original'])) {
                LocalChangeComments::setOriginal($entry, $details['original']);
            }
            if (isset($details['local_change'])) {
                $entry->addTranslatorComment('local_change: ' . $details['local_change']);
            }
            if (!empty($details['fuzzy'])) {
                $entry->addFlag('fuzzy');
            }
            $catalog->add($entry);
        }

        return $catalog;
    }

    public static function entry(?string $context, string $id, string $value): TranslationEntry
    {
        $entry = new TranslationEntry($context, $id);
        $entry->translate($value);

        return $entry;
    }

    public static function writePo(string $file, TranslationCatalog $catalog): void
    {
        self::ensureDirectory(dirname($file));
        file_put_contents($file, $catalog->toPoString());
    }

    public static function writeMo(string $file, TranslationCatalog $catalog): void
    {
        self::ensureDirectory(dirname($file));
        file_put_contents($file, $catalog->toMoString());
    }

    /** Writes "<base>.po" and, like MigratedLanguageFileSync::sync(), its compiled revision named by `current`. */
    public static function writePair(string $base_path, TranslationCatalog $catalog): void
    {
        self::writePo($base_path . '.po', $catalog);
        self::writeOverlayRevision($base_path, $catalog);
    }

    /** Compiles $catalog into a revision of the overlay $base_path and points `current` to it, like sync() without the `.po`. */
    public static function writeOverlayRevision(string $base_path, TranslationCatalog $catalog): void
    {
        $overlay_directory = dirname($base_path);
        $lang_key = basename($overlay_directory);
        $module = basename(dirname($overlay_directory));
        $compiled = ShippedTranslations::compileCatalog($catalog, $module, false);
        $keys_json = json_encode(array_map('strval', array_keys($compiled['values'])), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
        $revision = 'r-' . substr(hash('sha256', $compiled['mo'] . "\0" . $keys_json), 0, 32);
        $revision_directory = MigratedLanguageFilePaths::overlayRevisionDirectory($overlay_directory, $revision);
        $mo_file = MigratedLanguageFilePaths::overlayMoFile($revision_directory, $module, $lang_key);
        self::ensureDirectory(dirname($mo_file));
        file_put_contents($mo_file, $compiled['mo']);
        file_put_contents(MigratedLanguageFilePaths::overlayKeysFile($revision_directory), $keys_json);
        file_put_contents(MigratedLanguageFilePaths::overlayCurrentFile($overlay_directory), $revision . "\n");
    }

    /** The overlay of $directory's module for $lang_key without the `.po` extension. */
    public static function overlayBase(string $client_data_dir, LanguageFileDirectory $directory, string $lang_key): string
    {
        return substr(MigratedLanguageFilePaths::overlayPoFile($client_data_dir, $directory, $lang_key), 0, -strlen('.po'));
    }

    /** The compiled catalog of the revision `current` names; a non-existing path if there is none. */
    public static function overlayMo(string $base_path): string
    {
        $overlay_directory = dirname($base_path);
        $current = MigratedLanguageFilePaths::overlayCurrentFile($overlay_directory);
        $revision = is_file($current) ? trim((string) file_get_contents($current)) : '';
        if (!MigratedLanguageFilePaths::isOverlayRevision($revision)) {
            return $overlay_directory . '/no-current-revision.mo';
        }

        return MigratedLanguageFilePaths::overlayMoFile(
            MigratedLanguageFilePaths::overlayRevisionDirectory($overlay_directory, $revision),
            basename(dirname($overlay_directory)),
            basename($overlay_directory)
        );
    }

    public static function overlayCurrent(string $base_path): string
    {
        return MigratedLanguageFilePaths::overlayCurrentFile(dirname($base_path));
    }

    public static function overlayLock(string $base_path): string
    {
        return MigratedLanguageFilePaths::overlayLockFile(dirname($base_path));
    }

    public static function readPo(string $file): TranslationCatalog
    {
        return TranslationCatalog::fromPoFile($file);
    }

    /**
     * @return array<string, string> identifier => translation of every message without context of the `.mo` $file,
     *         as native gettext serves it for txt() (a plural message under its identifier with its default form)
     */
    public static function readMo(string $file): array
    {
        $result = [];
        foreach (self::moStrings((string) file_get_contents($file)) as [$original, $translated]) {
            if ($original === '' || str_contains($original, "\x04")) {
                continue;
            }
            $result[explode("\0", $original, 2)[0]] = explode("\0", $translated, 2)[0];
        }

        return $result;
    }

    /**
     * Every message of the `.mo` $data as [original, translated], header included (original ""):
     * "<context>\x04<msgid>", "<msgid>\0<msgid_plural>", forms separated by NUL.
     *
     * @return list<array{0: string, 1: string}>
     */
    public static function moStrings(string $data): array
    {
        $format = unpack('V', $data)[1] === 0x950412de ? 'V' : 'N';
        ['count' => $count, 'originals' => $originals, 'translations' => $translations] = unpack(
            $format . 'count/' . $format . 'originals/' . $format . 'translations',
            $data,
            8
        );
        $strings = [];
        for ($i = 0; $i < $count; $i++) {
            ['length' => $length, 'offset' => $offset] = unpack($format . 'length/' . $format . 'offset', $data, $originals + $i * 8);
            $original = substr($data, $offset, $length);
            ['length' => $length, 'offset' => $offset] = unpack($format . 'length/' . $format . 'offset', $data, $translations + $i * 8);
            $strings[] = [$original, substr($data, $offset, $length)];
        }

        return $strings;
    }

    /**
     * Messages of the `.mo` $data by identifier (contexts dropped), a plural message with its default form
     * (PluralForms, rule from the "Plural-Forms" header of $data).
     *
     * @return array<string, string>
     */
    public static function readMoTranslations(string $data): array
    {
        $plural_forms = PluralForms::fromHeaderOrGermanic(self::moPluralFormsHeader($data));
        $result = [];
        foreach (self::moStrings($data) as [$original, $translated]) {
            if ($original === '') {
                continue;
            }
            $id_and_plural = explode("\0", explode("\x04", $original, 2)[1] ?? $original, 2);
            $result[$id_and_plural[0]] = isset($id_and_plural[1])
                ? $plural_forms->defaultValueOf(explode("\0", $translated))
                : explode("\0", $translated, 2)[0];
        }

        return $result;
    }

    /**
     * Every form of the plural messages of the `.mo` $data by identifier (contexts dropped).
     *
     * @return array<string, list<string>>
     */
    public static function readMoPluralForms(string $data): array
    {
        $result = [];
        foreach (self::moStrings($data) as [$original, $translated]) {
            $id_and_plural = explode("\0", explode("\x04", $original, 2)[1] ?? $original, 2);
            if ($original !== '' && isset($id_and_plural[1])) {
                $result[$id_and_plural[0]] = explode("\0", $translated);
            }
        }

        return $result;
    }

    /** The "Plural-Forms" header of the `.mo` $data, `null` without. */
    public static function moPluralFormsHeader(string $data): ?string
    {
        foreach (self::moStrings($data) as [$original, $translated]) {
            if ($original === '' && preg_match('/^Plural-Forms:\s*(.*)$/mi', $translated, $matches) === 1) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    public static function artifactDirectory(): string
    {
        // per process: two PHPUnit runs at once (e.g. PHP 8.4 and 8.5 on one host) must not empty each other's build
        return sys_get_temp_dir() . '/ilias_lang_test_artifacts_' . getmypid() . '_' . self::$artifact_generation;
    }

    public static function glibcLocaleAvailable(): bool
    {
        foreach (['/usr/lib/locale/C.utf8', '/usr/lib/locale/C.UTF-8', '/usr/lib64/locale/C.utf8', '/usr/lib64/locale/C.UTF-8'] as $candidate) {
            if (is_file($candidate . '/LC_MESSAGES/SYS_LC_MESSAGES') || is_file($candidate . '/LC_CTYPE')) {
                return true;
            }
        }

        return false;
    }

    /** Skips $test without the glibc locale (macOS, BSD), otherwise makes NativeGettext use it. */
    public static function requireGlibcLocale(\PHPUnit\Framework\TestCase $test): void
    {
        if (!self::glibcLocaleAvailable()) {
            $test::markTestSkipped('needs the glibc locale "C.utf8" (LOCPATH), which this platform does not have');
        }
        NativeGettext::useSystemLocaleForTests(false);
    }

    /** Forgets the static state of the runtime read path and points build and runtime at $artifact_directory (default: the emptied temporary one), never at the installation's artifacts. */
    public static function resetRuntime(?string $artifact_directory = null): void
    {
        if ($artifact_directory === null) {
            self::removeDirectory(self::artifactDirectory());
            self::$artifact_generation++;
            self::$artifact_directories[] = self::artifactDirectory();
            if (!self::$artifact_cleanup_registered) {
                self::$artifact_cleanup_registered = true;
                register_shutdown_function(static function (): void {
                    array_map(self::removeDirectory(...), self::$artifact_directories);
                });
            }
        }
        (new ReflectionProperty(ilLanguage::class, 'installed_languages'))->setValue(null, null);
        (new ReflectionProperty(ilLanguage::class, 'indexed_manager'))->setValue(null, null);
        MigratedLanguageFilePaths::useArtifactDirectoryForTests($artifact_directory ?? self::artifactDirectory());
        MigratedTranslations::resetForTests();
        NativeGettext::resetForTests();
    }

    /** Moves the builds to a directory this process has not looked at: the C library keeps what it read from a path (mapped, not copied), so a file damaged in place must not be one it read. */
    public static function moveArtifactsToFreshDirectory(): void
    {
        $old = self::artifactDirectory();
        self::$artifact_generation++;
        $new = self::artifactDirectory();
        self::$artifact_directories[] = $new;
        if (!rename($old, $new)) {
            throw new RuntimeException('Cannot move "' . $old . '" to "' . $new . '".');
        }
        MigratedLanguageFilePaths::useArtifactDirectoryForTests($new);
        MigratedTranslations::resetForTests();
    }

    /**
     * What the runtime serves for $module/$lang_key (identifier => value), `null` if the module is not migrated for it,
     * after building the shipped `.po` files of the LanguageFileDirectoryManager in $DIC with $build.
     *
     * @return array<string, string>|null
     */
    public static function servedTexts(string $module, string $lang_key, bool $build = true): ?array
    {
        global $DIC;
        if ($build && isset($DIC) && $DIC->offsetExists(LanguageFileDirectoryManager::class)) {
            self::build($DIC[LanguageFileDirectoryManager::class], (string) ILIAS_ABSOLUTE_PATH);
        }
        $translations = new TranslationGettextRepository();
        $keys = (new ReflectionMethod(ilLanguage::class, 'migratedKeysOf'))->invoke(null, $translations, $module, $lang_key);
        if ($keys === null) {
            return null;
        }
        $texts = [];
        foreach (array_keys($keys) as $key) {
            $value = $translations->getText($module, $lang_key, (string) $key, defined('CLIENT_DATA_DIR') ? (string) CLIENT_DATA_DIR : null);
            if ($value !== null) {
                $texts[(string) $key] = $value;
            }
        }

        return $texts;
    }

    /**
     * Builds the shipped `.po` files of $manager below $ilias_absolute_path into the artifact directory of resetRuntime().
     *
     * @return array{build: ?string, compiled: int, unchanged: bool, removed: int, warnings: list<string>, collisions: list<string>}
     */
    public static function build(LanguageFileDirectoryManager $manager, string $ilias_absolute_path): array
    {
        $result = (new ShippedTranslationsBuild($manager, $ilias_absolute_path))->run(static function (string $message): void {
        });
        MigratedTranslations::resetForTests();

        return $result;
    }

    public static function directory(string $prefix, string $path, bool $local = false): LanguageFileDirectory
    {
        return new class ($prefix, $path, $local) implements LanguageFileDirectory {
            public function __construct(
                private readonly string $prefix,
                private readonly string $path,
                private readonly bool $local
            ) {
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
                return $this->local ? '.local' : '';
            }

            public function isLocal(): bool
            {
                return $this->local;
            }
        };
    }

    /** Writes $catalog as the shipped `.po`: a module only counts as migrated while that exists. Remove with removeShippedDirectory(). */
    public static function writeShippedPo(
        string $relative_path,
        string $module,
        string $lang_key,
        TranslationCatalog $catalog
    ): void {
        self::writePo(self::shippedDirectory($relative_path) . '/' . $module . '_' . $lang_key . '.po', $catalog);
    }

    public static function removeShippedDirectory(string $relative_path): void
    {
        self::removeDirectory(self::shippedDirectory($relative_path));
    }

    private static function shippedDirectory(string $relative_path): string
    {
        $directory = rtrim((string) ILIAS_ABSOLUTE_PATH, '/') . '/' . trim($relative_path, '/');
        self::ensureDirectory($directory);

        return $directory;
    }

    /**
     * Guards every fixture that writes under CLIENT_DATA_DIR: a full-suite run may have defined it as the real
     * /var/iliasdata (a constant cannot be redefined), so a foreign non-temp CLIENT_DATA_DIR is never written into
     * and never deleted: the test skips instead.
     *
     * @return bool true only if this call defined CLIENT_DATA_DIR (the caller then owns the root and may remove it
     *         in tearDown()); false if it was already defined
     */
    public static function ensureClientDataDirDefinedOrSkip(\PHPUnit\Framework\TestCase $test): bool
    {
        if (defined('CLIENT_DATA_DIR') && !str_starts_with(CLIENT_DATA_DIR, sys_get_temp_dir() . '/')) {
            $test->markTestSkipped(
                'CLIENT_DATA_DIR ("' . CLIENT_DATA_DIR . '") is not a test-owned temp directory - '
                . 'refusing to write fixture files there.'
            );
        }
        if (!defined('CLIENT_DATA_DIR')) {
            define('CLIENT_DATA_DIR', sys_get_temp_dir() . '/ilias_lang_test_client_data_dir_' . getmypid());
            return true;
        }
        return false;
    }

    public static function removeDirectory(string $directory): void
    {
        if (is_link($directory)) {
            unlink($directory);
            return;
        }
        if (!is_dir($directory)) {
            return;
        }
        @chmod($directory, 0775);
        foreach (scandir($directory) ?: [] as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            $path = $directory . '/' . $name;
            if (is_dir($path) && !is_link($path)) {
                self::removeDirectory($path);
            } else {
                @chmod($path, 0664);
                unlink($path);
            }
        }
        rmdir($directory);
    }

    private static function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }
}
