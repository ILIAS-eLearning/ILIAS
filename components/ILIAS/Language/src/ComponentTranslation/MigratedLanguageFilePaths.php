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

namespace ILIAS\Language\ComponentTranslation;

/**
 * The single place that knows where the files of a module migrated to PO/MO live:
 *
 * - the shipped, git-tracked `.po`: <ILIAS root>/<directory path><shipped file name>.po
 * - the build (see ShippedTranslationsBuild): `artifacts/language/current.json` names the build
 *   `artifacts/language/<build>/` with `messages/LC_MESSAGES/<module>.<lang>.mo`, `keys/<module>.php`
 *   and `locale/`
 * - the per-installation overlay (see MigratedLanguageFileSync):
 *   <client data dir>/lang/<module>/<lang>/ with `<shipped file name>.po`, `lock`, `current` and
 *   `r-<hash>/messages/LC_MESSAGES/<module>.<lang>.overlay.mo` plus `r-<hash>/keys.json`
 *
 * and how the client data directory is determined. Setup, ilLanguage, the administration write paths and
 * LanguageInstallationManager use this class instead of assembling these paths themselves.
 */
final class MigratedLanguageFilePaths
{
    private const string LANGUAGE_KEY_FORMAT = '/^[a-z]{2}\z/';

    /**
     * A module name as part of a file name and gettext domain "<module>.<lang>": no ".", so never ambiguous.
     */
    private const string MODULE_FORMAT = '/^[A-Za-z0-9_-]+\z/';

    private const string SHIPPED_ARTIFACT_DIRECTORY = 'artifacts/language';
    private const string BUILD_POINTER_FILE = 'current.json';
    private const string BUILD_ID_FORMAT = '/^b-[0-9a-f]{16,64}\z/';
    private const string OVERLAY_REVISION_FORMAT = '/^r-[0-9a-f]{16,64}\z/';
    private const string SHIPPED_TEMPLATES = 'components/*/*/lang/*.pot';

    private static ?string $artifact_directory_for_tests = null;

    /**
     * The client data directory (CLIENT_DATA_DIR) of this installation, `null` if it cannot be determined or
     * does not exist (yet).
     *
     * In a fully initialised request the CLIENT_DATA_DIR constant wins. Elsewhere (CLI Setup, objectives
     * without a Setup environment) it is composed like ilInitialisation does from ilias.ini.php:
     * `[clients] datadir` . '/' . `[clients] default`; never guessed from the data directory contents, which
     * holds further entries (e.g. "logs").
     *
     * A client directory that does not exist yet (fresh installation) yields `null`: the next `setup update`
     * creates the overlay then, instead of pre-creating a directory the installer may want to create itself.
     *
     * Only a fallback; the component graph and the Setup resolve it themselves.
     */
    public static function resolveClientDataDir(?string $ilias_absolute_path = null): ?string
    {
        if (defined('CLIENT_DATA_DIR')) {
            return (string) CLIENT_DATA_DIR;
        }

        $ilias_absolute_path ??= dirname(__DIR__, 5);
        $ini_file = rtrim($ilias_absolute_path, '/') . '/ilias.ini.php';
        if (!is_file($ini_file) || !is_readable($ini_file)) {
            return null;
        }
        $ini = @parse_ini_file($ini_file, true);
        if (!is_array($ini)) {
            return null;
        }

        $data_dir = $ini['clients']['datadir'] ?? null;
        if (is_string($data_dir) && $data_dir !== '' && !str_starts_with($data_dir, '/')) {
            $data_dir = rtrim($ilias_absolute_path, '/') . '/' . $data_dir;
        }

        return self::fromDataDirAndClientId(
            is_string($data_dir) ? $data_dir : null,
            is_string($ini['clients']['default'] ?? null) ? $ini['clients']['default'] : null
        );
    }

    /**
     * Composes the client data directory from its two parts (e.g. the Setup environment's ilias.ini resource
     * and client id); `null` if a part is missing, the client id is no plain directory name or it does not exist.
     */
    public static function fromDataDirAndClientId(?string $data_dir, ?string $client_id): ?string
    {
        if ($data_dir === null || $data_dir === '' || $client_id === null || $client_id === '') {
            return null;
        }
        if ($client_id === '.' || $client_id === '..' || strpbrk($client_id, "/\\\0") !== false) {
            return null;
        }

        $client_data_dir = rtrim($data_dir, '/') . '/' . $client_id;

        return is_dir($client_data_dir) ? $client_data_dir : null;
    }

    /**
     * Without extension; named by shippedFileNamePattern().
     *
     * @throws \InvalidArgumentException for a $lang_key that is not an ILIAS language key or an
     *         invalid file name pattern
     */
    public static function shippedBasePath(
        string $ilias_absolute_path,
        LanguageFileDirectory $directory,
        string $lang_key
    ): string {
        self::assertValidLanguageKey($lang_key);

        return self::shippedDirectoryPath($ilias_absolute_path, $directory)
            . sprintf(self::shippedFileNamePattern($directory), $lang_key);
    }

    /**
     * The shipped `.po` files of $directory by language key, found via its file name pattern (see
     * shippedFileNamePattern()). A file whose name yields no language key (e.g. "tos_pt_BR.po") is not listed.
     *
     * @return array<string, string> language key => absolute path, sorted by language key
     * @throws \InvalidArgumentException for a directory with an invalid file name pattern
     */
    public static function findShippedPoFiles(string $ilias_absolute_path, LanguageFileDirectory $directory): array
    {
        $pattern = self::shippedFileNamePattern($directory);
        $base = self::shippedDirectoryPath($ilias_absolute_path, $directory);
        [$before, $after] = explode('%s', $pattern, 2);
        $name_format = '/\A' . preg_quote($before, '/') . '([a-z]{2})' . preg_quote($after, '/') . '\.po\z/';

        $files = [];
        foreach (glob($base . sprintf($pattern, '*') . '.po') ?: [] as $file) {
            if (is_file($file) && preg_match($name_format, basename($file), $matches) === 1) {
                $files[$matches[1]] = $file;
            }
        }
        ksort($files, SORT_STRING);

        return $files;
    }

    /**
     * The template (`.pot`) next to the shipped `.po` files, see templateFileName().
     *
     * @throws \InvalidArgumentException for a directory with an invalid file name pattern
     */
    public static function shippedTemplatePath(string $ilias_absolute_path, LanguageFileDirectory $directory): string
    {
        return self::shippedDirectoryPath($ilias_absolute_path, $directory)
            . self::templateFileName(self::shippedFileNamePattern($directory), $directory->getPrefix());
    }

    /**
     * The modules with a template in the `lang/` directory of a component (`<module>.pot`, see templateFileName())
     * => that directory; without the component graph, e.g. in the Setup.
     *
     * @return array<string, string>
     */
    public static function findShippedTemplates(string $ilias_absolute_path): array
    {
        $templates = [];
        foreach (glob(rtrim($ilias_absolute_path, '/') . '/' . self::SHIPPED_TEMPLATES) ?: [] as $file) {
            $module = basename($file, '.pot');
            if (self::isValidModule($module) && is_file($file)) {
                $templates[$module] ??= dirname($file);
            }
        }

        return $templates;
    }

    /**
     * The file name of the template (`.pot`) for the pattern $pattern of $module: the pattern without "%s" and
     * the separators around it ("tos_%s" -> "tos.pot"), "<module>.pot" if nothing is left. Shared by the
     * conversion tool and the runtime.
     */
    public static function templateFileName(string $pattern, string $module): string
    {
        $name = trim(str_replace('%s', '', $pattern), '_-.');

        return ($name === '' ? $module : $name) . '.pot';
    }

    private static function shippedDirectoryPath(string $ilias_absolute_path, LanguageFileDirectory $directory): string
    {
        return rtrim($ilias_absolute_path, '/') . '/' . ltrim($directory->getPath(), '/');
    }

    /**
     * The name of $directory's shipped `.po` without ".po", with "%s" for the language key: its own pattern
     * (NamesShippedLanguageFiles), otherwise "<prefix>_%s". Only the shipped and overlay `.po` are named by
     * it; directories and compiled catalogs are named by the module (the prefix).
     *
     * @throws \InvalidArgumentException see assertValidShippedFileNamePattern()
     */
    public static function shippedFileNamePattern(LanguageFileDirectory $directory): string
    {
        $pattern = $directory instanceof NamesShippedLanguageFiles
            ? $directory->getShippedFileNamePattern()
            : $directory->getPrefix() . '_%s';
        self::assertValidShippedFileNamePattern($pattern);

        return $pattern;
    }

    /**
     * @throws \InvalidArgumentException unless $pattern contains exactly one "%s" and otherwise only
     *         letters, digits, "_", "-" and "." - no "/", no "..", no other "%"
     */
    public static function assertValidShippedFileNamePattern(string $pattern): void
    {
        if (
            substr_count($pattern, '%s') !== 1
            || str_contains($pattern, '..')
            || preg_match('/\A[A-Za-z0-9_.\-]*%s[A-Za-z0-9_.\-]*\z/', $pattern) !== 1
        ) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid file name pattern for shipped language files.', $pattern));
        }
    }

    /**
     * The directory Setup's build writes into (see ShippedTranslationsBuild), in the untracked `artifacts/`.
     */
    public static function shippedArtifactDirectory(string $ilias_absolute_path): string
    {
        return self::$artifact_directory_for_tests
            ?? rtrim($ilias_absolute_path, '/') . '/' . self::SHIPPED_ARTIFACT_DIRECTORY;
    }

    /**
     * Makes shippedArtifactDirectory() return $artifact_directory (`null`: the default) - for tests only,
     * so they never touch the artifacts of the installation.
     *
     * @internal
     */
    public static function useArtifactDirectoryForTests(?string $artifact_directory): void
    {
        self::$artifact_directory_for_tests = $artifact_directory;
    }

    /**
     * `artifacts/language/current.json`: names the build the runtime serves (see
     * ShippedTranslationsBuild) - switched atomically once a build is complete.
     */
    public static function buildPointerFile(string $artifact_directory): string
    {
        return rtrim($artifact_directory, '/') . '/' . self::BUILD_POINTER_FILE;
    }

    /**
     * `artifacts/language/<build>`: one complete build, bound with bindtextdomain() as it is (the C library
     * looks below it in `messages/LC_MESSAGES/`, see NativeGettext).
     *
     * @throws \InvalidArgumentException for a build id that is not a plain build name
     */
    public static function buildDirectory(string $artifact_directory, string $build_id): string
    {
        if (preg_match(self::BUILD_ID_FORMAT, $build_id) !== 1) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid build id.', $build_id));
        }

        return rtrim($artifact_directory, '/') . '/' . $build_id;
    }

    public static function isBuildId(string $name): bool
    {
        return preg_match(self::BUILD_ID_FORMAT, $name) === 1;
    }

    /**
     * The compiled catalog of $module for $lang_key: `<build>/messages/LC_MESSAGES/<module>.<lang>.mo`,
     * domain shippedDomain().
     *
     * @throws \InvalidArgumentException for an invalid module name or language key
     */
    public static function buildMoFile(string $build_directory, string $module, string $lang_key): string
    {
        return rtrim($build_directory, '/') . '/' . NativeGettext::CATALOG_DIRECTORY . '/' . self::shippedDomain($module, $lang_key) . '.mo';
    }

    /**
     * The identifiers of $module in the build, the same for every language: `<build>/keys/<module>.php`
     * (a PHP file returning identifier => module, so opcache keeps it).
     *
     * @throws \InvalidArgumentException for an invalid module name
     */
    public static function buildKeysFile(string $build_directory, string $module): string
    {
        self::assertValidModule($module);

        return rtrim($build_directory, '/') . '/keys/' . $module . '.php';
    }

    /**
     * The directory with the locale NativeGettext activates (NativeGettext::LOCALE_NAME): `<build>/locale`.
     */
    public static function buildLocaleDirectory(string $build_directory): string
    {
        return rtrim($build_directory, '/') . '/locale';
    }

    /**
     * The gettext domain of the shipped state of $module for $lang_key: "<module>.<lang>".
     *
     * @throws \InvalidArgumentException for an invalid module name or language key
     */
    public static function shippedDomain(string $module, string $lang_key): string
    {
        self::assertValidModule($module);
        self::assertValidLanguageKey($lang_key);

        return $module . '.' . $lang_key;
    }

    /**
     * The gettext domain of the overlay of $module for $lang_key: "<module>.<lang>.overlay".
     *
     * @throws \InvalidArgumentException for an invalid module name or language key
     */
    public static function overlayDomain(string $module, string $lang_key): string
    {
        return self::shippedDomain($module, $lang_key) . '.overlay';
    }

    /**
     * The overlay of $module for $lang_key lives in `<client data dir>/lang/<module>/<lang>/`:
     * the `.po` (see overlayPoFile()), `lock`, `current` and the compiled revisions `r-<hash>/`.
     *
     * @throws \InvalidArgumentException for an invalid module name or language key - the language
     *         key becomes part of a file path, and some callers pass it through from public APIs
     *         (e.g. ilLanguage::_lookupEntry())
     */
    public static function overlayDirectory(string $client_data_dir, string $module, string $lang_key): string
    {
        self::assertValidModule($module);
        self::assertValidLanguageKey($lang_key);

        return rtrim($client_data_dir, '/') . '/lang/' . $module . '/' . $lang_key;
    }

    /**
     * The overlay `.po` of $directory's module for $lang_key (the delta with its bookkeeping), named
     * like the shipped `.po` (see shippedFileNamePattern()).
     *
     * @throws \InvalidArgumentException see overlayDirectory() and shippedFileNamePattern()
     */
    public static function overlayPoFile(string $client_data_dir, LanguageFileDirectory $directory, string $lang_key): string
    {
        return self::overlayDirectory($client_data_dir, $directory->getPrefix(), $lang_key)
            . '/' . sprintf(self::shippedFileNamePattern($directory), $lang_key) . '.po';
    }

    /**
     * The lock file serializing the writes of the overlay in $overlay_directory.
     */
    public static function overlayLockFile(string $overlay_directory): string
    {
        return rtrim($overlay_directory, '/') . '/lock';
    }

    /**
     * The file naming the revision (see overlayRevisionDirectory()) of the overlay in
     * $overlay_directory the runtime serves - switched atomically.
     */
    public static function overlayCurrentFile(string $overlay_directory): string
    {
        return rtrim($overlay_directory, '/') . '/current';
    }

    /**
     * One compiled revision of the overlay in $overlay_directory: `r-<hash>/`, bound with
     * bindtextdomain() as it is (see buildDirectory()).
     *
     * @throws \InvalidArgumentException for a revision that is not a plain revision name
     */
    public static function overlayRevisionDirectory(string $overlay_directory, string $revision): string
    {
        if (!self::isOverlayRevision($revision)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid overlay revision.', $revision));
        }

        return rtrim($overlay_directory, '/') . '/' . $revision;
    }

    public static function isOverlayRevision(string $name): bool
    {
        return preg_match(self::OVERLAY_REVISION_FORMAT, $name) === 1;
    }

    /**
     * The compiled overlay of $module for $lang_key in $revision_directory:
     * `r-<hash>/messages/LC_MESSAGES/<module>.<lang>.overlay.mo`.
     *
     * @throws \InvalidArgumentException for an invalid module name or language key
     */
    public static function overlayMoFile(string $revision_directory, string $module, string $lang_key): string
    {
        return rtrim($revision_directory, '/') . '/' . NativeGettext::CATALOG_DIRECTORY . '/' . self::overlayDomain($module, $lang_key) . '.mo';
    }

    /**
     * The identifiers the overlay revision $revision_directory has a value for: `r-<hash>/keys.json`
     * (JSON, not PHP: the client data directory is writable by the web server).
     */
    public static function overlayKeysFile(string $revision_directory): string
    {
        return rtrim($revision_directory, '/') . '/keys.json';
    }

    /**
     * The lock file serializing the writes of $directory's template (`.pot`) - shared by all
     * languages, next to the overlays of the module (see ShippedPoMerger).
     *
     * @throws \InvalidArgumentException for a module (the directory's prefix) that is not a plain
     *         file name
     */
    public static function templateLockFile(string $client_data_dir, LanguageFileDirectory $directory): string
    {
        $module = $directory->getPrefix();
        self::assertValidModule($module);

        return rtrim($client_data_dir, '/') . '/lang/' . $module . '/template.lock';
    }

    public static function isValidModule(string $module): bool
    {
        return preg_match(self::MODULE_FORMAT, $module) === 1;
    }

    public static function isValidLanguageKey(string $lang_key): bool
    {
        return preg_match(self::LANGUAGE_KEY_FORMAT, $lang_key) === 1;
    }

    /**
     * @throws \InvalidArgumentException for a module name that cannot be part of a file name
     */
    private static function assertValidModule(string $module): void
    {
        if (!self::isValidModule($module)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid module name.', $module));
        }
    }

    private static function assertValidLanguageKey(string $lang_key): void
    {
        if (!self::isValidLanguageKey($lang_key)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid language key.', $lang_key));
        }
    }
}
