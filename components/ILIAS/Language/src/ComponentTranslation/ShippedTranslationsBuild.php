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

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use RuntimeException;

/**
 * Builds what native gettext serves for the migrated modules (see MigratedTranslations) from every shipped
 * `.po` of every contributed LanguageFileDirectory; run by Setup's build (ShippedLanguageFilesCompiledObjective)
 * only, never by the web server (it does not write `artifacts/`; "merge" only writes the shipped `.po`, see
 * ShippedPoMerger).
 *
 * Requirement: the locale is copied from the C library of the build host and checked in that (CLI) process
 * only. The web server must use the same C library (glibc with `C.utf8`) and have the PHP extension "gettext";
 * with separate CLI/web images or a musl based system (which ignores LOCPATH) the runtime serves `-identifier-`
 * and reports why (see MigratedTranslations::getProblem()).
 *
 * Layout below `artifacts/language/` (see MigratedLanguageFilePaths):
 * - `<build>/messages/LC_MESSAGES/<module>.<lang>.mo`: the shipped state of a module, see
 *   ShippedTranslations::compile();
 * - `<build>/keys/<module>.php`: all identifiers of the module's `.po` files and template, the same for every
 *   language, as a PHP file for the opcache;
 * - `<build>/locale/`: the locale native gettext is activated with (see NativeGettext), a copy of "C.utf8";
 * - `<build>/manifest.json`: size and hash of every file, to tell an intact build, the hash of each built `.po`
 *   (see servesShippedPo()), the warnings and collisions;
 * - `current.json`: the build the runtime serves, its languages per module and every contributed module
 *   ("shipped", never read from the database) - read on every request, so nothing else.
 *
 * The build id fingerprints everything that determines the build: the shipped files, the compiling code (see
 * FORMAT_VERSION) and the locale. An intact build with the same id (its manifest also keeps its warnings) is
 * reused, otherwise a new one is written into a temporary directory and renamed to its id. Either way it is
 * checked with a real native gettext lookup before `current.json` is switched atomically; running requests
 * keep their build. The previous build is kept, older ones (and the files of the former layout
 * `<lang>/<module>.mo`, `index.json`) are removed. Builds are serialized by `build.lock`.
 *
 * A `.po` that cannot be compiled is reported and served with the catalog of the served (or else the previous)
 * build, so a broken file never turns a working language into `-identifier-`; without such a catalog the module is
 * not served for that language. Values with disallowed markup are compiled cleaned and reported; identifiers with different values in several modules (txt() serves the one loaded last) are
 * listed. All of that repeats on every build until fixed.
 *
 * Files are written without fsync(): a build can be repeated any time, and the manifest reveals a build
 * damaged by a crash.
 *
 * @phpstan-type Manifest array{files: array<string, array{0: int, 1: string}>, sample: array{module: string, lang: string, key: string, value: string}, languages: array<string, list<string>>, hashes: array<string, array<string, string>>, warnings: list<string>, collisions: list<string>, compatibility: string}
 * @phpstan-type Pointer array{build: ?string, previous: ?string, modules: array<string, list<string>>, shipped: ?list<string>}
 */
final class ShippedTranslationsBuild
{
    /**
     * Raise to rebuild when the output changes for a reason the fingerprint of the involved classes does
     * not capture, e.g. an update of the gettext library.
     */
    private const int FORMAT_VERSION = 4;

    private const string MANIFEST_FILE = 'manifest.json';

    private const string LOCK_FILE = 'build.lock';

    /**
     * Per build directory: a build never changes once written.
     *
     * @var array<string, Manifest>
     */
    private static array $manifests = [];

    /**
     * Where the C library's "C.utf8" locale is looked for (Debian/Ubuntu, Fedora/RHEL, Arch, ...).
     */
    private const array LOCALE_SOURCES = [
        '/usr/lib/locale/C.utf8',
        '/usr/lib/locale/C.UTF-8',
        '/usr/lib64/locale/C.utf8',
        '/usr/lib64/locale/C.UTF-8',
    ];

    /**
     * @param string|null $locale_source the locale directory to ship; default: found in LOCALE_SOURCES
     */
    public function __construct(
        private readonly LanguageFileDirectoryManager $language_file_directory_manager,
        private readonly string $ilias_absolute_path,
        private readonly ShippedTranslations $shipped_translations = new ShippedTranslations(),
        private readonly ?string $locale_source = null
    ) {
    }

    /**
     * @param callable(string $message): void $inform progress and warnings
     * @return array{build: ?string, compiled: int, unchanged: bool, removed: int, warnings: list<string>, collisions: list<string>}
     *         build: the id now served (`null` without any migrated module); compiled: `.po` files
     *         compiled (0 for an unchanged build); removed: files and directories of former builds
     * @throws RuntimeException if the build cannot be written, the locale is missing, or native gettext
     *         does not serve it - `current.json` is left unchanged then
     */
    public function run(callable $inform): array
    {
        $artifact_directory = MigratedLanguageFilePaths::shippedArtifactDirectory($this->ilias_absolute_path);
        $this->ensureDirectory($artifact_directory);

        // one build at a time (e.g. two Setup runs)
        $lock = @fopen($artifact_directory . '/' . self::LOCK_FILE, 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            throw new RuntimeException(sprintf('Could not lock "%s".', $artifact_directory . '/' . self::LOCK_FILE));
        }
        try {
            return $this->runLocked($artifact_directory, $inform);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * @param callable(string $message): void $inform
     * @return array{build: ?string, compiled: int, unchanged: bool, removed: int, warnings: list<string>, collisions: list<string>}
     */
    private function runLocked(string $artifact_directory, callable $inform): array
    {
        $pointer = self::readPointer($artifact_directory);
        [$modules, $shipped] = $this->findShippedFiles($inform);

        if ($modules === []) {
            $this->writePointer($artifact_directory, ['build' => null, 'previous' => null, 'modules' => [], 'shipped' => $shipped]);
            return [
                'build' => null,
                'compiled' => 0,
                'unchanged' => ($pointer['build'] ?? null) === null,
                'removed' => $this->removeObsolete($artifact_directory, []),
                'warnings' => [],
                'collisions' => [],
            ];
        }

        $locale_source = $this->findLocaleSource();
        $build_id = $this->buildId($modules, $locale_source);
        $build_directory = MigratedLanguageFilePaths::buildDirectory($artifact_directory, $build_id);
        $manifest = self::readManifest($build_directory);
        $compiled = 0;
        if ($manifest === null || !$this->isIntact($build_directory, $manifest)) {
            $former_builds = array_values(array_diff(
                array_filter([$pointer['build'] ?? null, $pointer['previous'] ?? null], 'is_string'),
                [$build_id]
            ));
            [$manifest, $compiled] = $this->write($artifact_directory, $build_directory, $build_id, $modules, $locale_source, $former_builds);
        }

        try {
            $this->assertNativeGettextServes($build_directory, $manifest['sample']);
        } catch (RuntimeException $e) {
            if (!in_array($build_id, [$pointer['build'] ?? null, $pointer['previous'] ?? null], true)) {
                $this->removeTree($artifact_directory, $build_directory);
            }
            throw $e;
        }

        $unchanged = ($pointer['build'] ?? null) === $build_id;
        $previous = ($pointer['build'] ?? null) !== null && !$unchanged
            ? $pointer['build']
            : ($pointer['previous'] ?? null);
        $new_pointer = [
            'build' => $build_id,
            'previous' => $previous,
            'modules' => $manifest['languages'],
            'shipped' => $shipped,
        ];
        if ($new_pointer !== $pointer) {
            $this->writePointer($artifact_directory, $new_pointer);
        }

        return [
            'build' => $build_id,
            'compiled' => $compiled,
            'unchanged' => $unchanged && $compiled === 0,
            'removed' => $this->removeObsolete($artifact_directory, array_values(array_filter([$build_id, $previous]))),
            'warnings' => $manifest['warnings'],
            'collisions' => $manifest['collisions'],
        ];
    }

    /**
     * `current.json` of $artifact_directory, `null` if it is missing or not valid.
     *
     * @return Pointer|null modules: module => the languages it is built for; shipped: `null` in a pointer
     *         written before it was recorded
     */
    public static function readPointer(string $artifact_directory): ?array
    {
        $content = @file_get_contents(MigratedLanguageFilePaths::buildPointerFile($artifact_directory));
        $data = $content === false ? null : json_decode($content, true);
        if (!is_array($data) || !is_array($data['modules'] ?? null)) {
            return null;
        }
        $build = $data['build'] ?? null;
        $previous = $data['previous'] ?? null;
        if (
            ($build !== null && (!is_string($build) || !MigratedLanguageFilePaths::isBuildId($build)))
            || ($previous !== null && (!is_string($previous) || !MigratedLanguageFilePaths::isBuildId($previous)))
        ) {
            return null;
        }

        $modules = [];
        foreach ($data['modules'] as $module => $languages) {
            if (!is_string($module) || !MigratedLanguageFilePaths::isValidModule($module) || !is_array($languages)) {
                return null;
            }
            $modules[$module] = array_values(array_filter(
                $languages,
                static fn($lang_key): bool => is_string($lang_key) && MigratedLanguageFilePaths::isValidLanguageKey($lang_key)
            ));
        }

        $shipped = $data['shipped'] ?? null;
        if ($shipped !== null && (!is_array($shipped) || !array_is_list($shipped))) {
            return null;
        }
        if ($shipped !== null) {
            $shipped = array_values(array_filter(
                $shipped,
                static fn($module): bool => is_string($module) && MigratedLanguageFilePaths::isValidModule($module)
            ));
        }

        return [
            'build' => $build,
            'previous' => $previous,
            'modules' => $modules,
            'shipped' => $shipped,
        ];
    }

    /**
     * Writes the build $build_id into a temporary directory and renames that to $build_directory.
     *
     * @param array<string, array{directory: LanguageFileDirectory, languages: array<string, string>, template: ?string}> $modules
     * @param list<string> $former_builds where the catalog of a `.po` that cannot be compiled is taken from
     * @return array{0: Manifest, 1: int} the manifest of the build and the number of compiled `.po` files
     */
    private function write(
        string $artifact_directory,
        string $build_directory,
        string $build_id,
        array $modules,
        string $locale_source,
        array $former_builds
    ): array {
        $temporary = $artifact_directory . '/.' . $build_id . '.' . bin2hex(random_bytes(6));
        $this->ensureDirectory($temporary . '/' . NativeGettext::CATALOG_DIRECTORY);
        $this->ensureDirectory($temporary . '/keys');
        try {
            $files = [];
            $warnings = [];
            $languages = [];
            $values_per_language = [];
            $sample = null;
            $hashes = [];
            $compiled = 0;
            foreach ($modules as $module => $shipped) {
                $keys = $this->templateKeys($shipped['template'], $module, $warnings);
                foreach ($shipped['languages'] as $lang_key => $shipped_po) {
                    $source = $this->relativePath($shipped_po);
                    $po_hash = (string) @hash_file('sha256', $shipped_po);
                    try {
                        $catalog = $this->shipped_translations->compile(
                            $shipped_po,
                            $module,
                            static function (string $identifier, array $violations) use (&$warnings, $source, $module): void {
                                $warnings[] = sprintf('Markup not allowed in a language value, %s (module "%s", key "%s"): removed %s', $source, $module, $identifier, implode(', ', $violations));
                            },
                            static function (string $message) use (&$warnings, $source, $module): void {
                                $warnings[] = sprintf('%s (module "%s"): %s', $source, $module, $message);
                            }
                        );
                        $compiled++;
                    } catch (\Throwable $t) {
                        $former = $this->formerCatalog($artifact_directory, $former_builds, $module, $lang_key);
                        if ($former === null) {
                            $warnings[] = sprintf(
                                'Could not compile "%s", module "%s" is not served for "%s" until the file is fixed (no former build has it): %s',
                                $source,
                                $module,
                                $lang_key,
                                $t->getMessage()
                            );
                            continue;
                        }
                        $warnings[] = sprintf(
                            'Could not compile "%s", module "%s" is served for "%s" as in build %s until the file is fixed: %s',
                            $source,
                            $module,
                            $lang_key,
                            $former['build'],
                            $t->getMessage()
                        );
                        $catalog = $former;
                        // the build does not serve this .po, see servesShippedPo()
                        $po_hash = $former['hash'];
                    }
                    $file = MigratedLanguageFilePaths::buildMoFile($temporary, $module, $lang_key);
                    $this->writeFile($file, $catalog['mo']);
                    $files[substr($file, strlen($temporary) + 1)] = [strlen($catalog['mo']), hash('sha256', $catalog['mo'])];
                    $languages[$module][] = $lang_key;
                    $hashes[$module][$lang_key] = $po_hash;
                    $keys += array_fill_keys(array_map('strval', array_keys($catalog['values'])), true);
                    $values_per_language[$lang_key][$module] = $catalog['values'];
                    foreach ($catalog['values'] as $identifier => $value) {
                        if ($sample === null && $value !== (string) $identifier) {
                            $sample = ['module' => $module, 'lang' => $lang_key, 'key' => (string) $identifier, 'value' => $value];
                        }
                    }
                }
                if (!isset($languages[$module])) {
                    continue;
                }
                ksort($keys, SORT_STRING);
                $content = "<?php\n\n// Generated by the ILIAS build (ShippedTranslationsBuild) - do not edit.\n\nreturn "
                    . var_export(array_fill_keys(array_map('strval', array_keys($keys)), $module), true) . ";\n";
                $file = MigratedLanguageFilePaths::buildKeysFile($temporary, $module);
                $this->writeFile($file, $content);
                $files[substr($file, strlen($temporary) + 1)] = [strlen($content), hash('sha256', $content)];
            }
            if ($sample === null) {
                throw new RuntimeException('No migrated module has a single translated value - nothing to check native gettext with.');
            }

            $this->copyLocale($locale_source, MigratedLanguageFilePaths::buildLocaleDirectory($temporary) . '/' . NativeGettext::LOCALE_NAME, $temporary, $files);
            ksort($files, SORT_STRING);
            $manifest = [
                'files' => $files,
                'sample' => $sample,
                'languages' => $languages,
                'hashes' => $hashes,
                'warnings' => $warnings,
                'collisions' => $this->findCollisions($values_per_language),
                'compatibility' => self::catalogCompatibility(),
            ];
            $this->writeFile($temporary . '/' . self::MANIFEST_FILE, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n");

            // a damaged build of the same id (see isIntact()) is replaced
            $this->removeTree($artifact_directory, $build_directory);
            if (!@rename($temporary, $build_directory)) {
                throw new RuntimeException(sprintf('Could not rename "%s" to "%s".', $temporary, $build_directory));
            }
        } catch (\Throwable $t) {
            $this->removeTree($artifact_directory, $temporary);
            throw $t instanceof RuntimeException ? $t : new RuntimeException($t->getMessage(), 0, $t);
        }

        return [$manifest, $compiled];
    }

    /**
     * The catalog of $module/$lang_key in the first former build that has it intact and compiled the same way
     * (see catalogCompatibility()).
     *
     * The bytes are taken over, not recompiled: reading a `.mo` back would shift plural forms (see
     * TranslationCatalog::fromMoString()). Values are checked against the markup policy again.
     *
     * @param list<string> $former_builds build ids
     * @return array{mo: string, values: array<string, string>, build: string, hash: string}|null values: see
     *         ShippedTranslations::compileCatalog(); hash: of the `.po` it was compiled from
     */
    private function formerCatalog(string $artifact_directory, array $former_builds, string $module, string $lang_key): ?array
    {
        $policy = new TranslationMarkupPolicy();
        foreach ($former_builds as $former_build) {
            $build_directory = MigratedLanguageFilePaths::buildDirectory($artifact_directory, $former_build);
            $manifest = self::readManifest($build_directory);
            if ($manifest === null || $manifest['compatibility'] !== self::catalogCompatibility()) {
                continue;
            }
            $file = MigratedLanguageFilePaths::buildMoFile($build_directory, $module, $lang_key);
            [$size, $hash] = $manifest['files'][substr($file, strlen($build_directory) + 1)] ?? [null, null];
            $resolved_file = realpath($file);
            $resolved_directory = realpath($build_directory);
            $mo = $size === null
                || $resolved_file === false
                || $resolved_directory === false
                || !str_starts_with($resolved_file, $resolved_directory . '/')
                ? false
                : @file_get_contents($resolved_file);
            if ($mo === false || strlen($mo) !== $size || hash('sha256', $mo) !== $hash) {
                continue;
            }
            try {
                $catalog = TranslationCatalog::fromMoString($mo);
            } catch (RuntimeException) {
                continue;
            }
            $values = [];
            foreach ($catalog->getEntries() as $entry) {
                foreach ([$entry->getTranslation(), ...$entry->getPluralTranslations()] as $value) {
                    if (!$policy->isAllowed($value)) {
                        continue 3;
                    }
                }
                if ($entry->getContext() === null && !$entry->isPlural() && $entry->getTranslation() !== '') {
                    $values[$entry->getId()] = $entry->getTranslation();
                }
            }

            return [
                'mo' => $mo,
                'values' => $values,
                'build' => $former_build,
                'hash' => $manifest['hashes'][$module][$lang_key] ?? '',
            ];
        }

        return null;
    }

    /**
     * Fingerprint of the compiling and cleaning code: formerCatalog() only takes over catalogs with the same one.
     */
    private static function catalogCompatibility(): string
    {
        $parts = [(string) self::FORMAT_VERSION];
        foreach ([ShippedTranslations::class, TranslationMarkupPolicy::class] as $class) {
            $file = (new \ReflectionClass($class))->getFileName();
            $parts[] = is_string($file) ? (string) @hash_file('sha256', $file) : '';
        }

        return hash('sha256', implode('|', $parts));
    }

    /**
     * The identifiers of $module in its template (`.pot`), `[]` if there is none or it cannot be read
     * (reported).
     *
     * @param list<string> $warnings
     * @return array<string, true>
     */
    private function templateKeys(?string $template, string $module, array &$warnings): array
    {
        if ($template === null) {
            return [];
        }
        try {
            return array_fill_keys(
                array_map('strval', array_keys(MigratedLanguageFileSync::moduleEntries(TranslationCatalog::fromPoFile($template), $module))),
                true
            );
        } catch (RuntimeException $e) {
            $warnings[] = sprintf('The template "%s" cannot be read: %s', $this->relativePath($template), $e->getMessage());
            return [];
        }
    }

    /**
     * The identifiers with different values in several modules, per language.
     *
     * @param array<string, array<string, array<string, string>>> $values_per_language lang => module => identifier => value
     * @return list<string>
     */
    private function findCollisions(array $values_per_language): array
    {
        $collisions = [];
        foreach ($values_per_language as $lang_key => $modules) {
            $values_of = [];
            foreach ($modules as $module => $values) {
                foreach ($values as $identifier => $value) {
                    $values_of[(string) $identifier][$module] = $value;
                }
            }
            foreach ($values_of as $identifier => $values) {
                if (count(array_unique($values)) > 1) {
                    ksort($values, SORT_STRING);
                    $collisions[$identifier . "\0" . implode(', ', array_keys($values))][] = (string) $lang_key;
                }
            }
        }
        ksort($collisions, SORT_STRING);

        $list = [];
        foreach ($collisions as $key => $lang_keys) {
            [$identifier, $modules] = explode("\0", (string) $key, 2);
            sort($lang_keys, SORT_STRING);
            $list[] = sprintf('Key "%s" has different values in the modules %s (languages: %s) - txt() serves the one of the module loaded last.', $identifier, $modules, implode(', ', $lang_keys));
        }

        return $list;
    }

    /**
     * Looks $sample up through native gettext in the build $build_directory.
     *
     * @param array{module: string, lang: string, key: string, value: string} $sample
     * @throws RuntimeException if native gettext does not serve the expected value
     */
    private function assertNativeGettextServes(string $build_directory, array $sample): void
    {
        if (!NativeGettext::activate(MigratedLanguageFilePaths::buildLocaleDirectory($build_directory))) {
            throw new RuntimeException(sprintf('Native gettext is not available: %s.', (string) NativeGettext::getProblem()));
        }
        $domain = MigratedLanguageFilePaths::shippedDomain($sample['module'], $sample['lang']);
        if (!NativeGettext::bind($domain, $build_directory)) {
            throw new RuntimeException(sprintf('Native gettext cannot bind the domain "%s" to "%s".', $domain, $build_directory));
        }
        $value = NativeGettext::translate($domain, $sample['key']);
        if ($value !== $sample['value']) {
            throw new RuntimeException(sprintf(
                'Native gettext does not translate: "%s" in the domain "%s" gave "%s" instead of "%s".',
                $sample['key'],
                $domain,
                $value,
                $sample['value']
            ));
        }
    }

    /**
     * Every shipped `.po` of every contributed directory with a module prefix (the main `lang/`
     * directory has none and holds no `.po`), per module.
     *
     * @param callable(string): void $inform
     * @return array{0: array<string, array{directory: LanguageFileDirectory, languages: array<string, string>, template: ?string}>, 1: list<string>}
     *         the modules with `.po` files, sorted by module (languages: lang => shipped `.po`, sorted by
     *         language), and every contributed module, sorted
     */
    private function findShippedFiles(callable $inform): array
    {
        $modules = [];
        $contributed = [];
        $matching_files = [];
        $all_shipped_files = [];
        foreach ($this->language_file_directory_manager->getDirectories() as $directory) {
            $module = $directory->getPrefix();
            if ($module === '') {
                continue;
            }
            if (!MigratedLanguageFilePaths::isValidModule($module)) {
                $inform(sprintf('WARNING: The language files of module "%s" are not compiled: the module name cannot be part of a file name.', $module));
                continue;
            }
            if (isset($modules[$module])) {
                $inform(sprintf('WARNING: Module "%s" is contributed twice - only "%s" is compiled.', $module, $modules[$module]['directory']->getPath()));
                continue;
            }
            try {
                $pattern = MigratedLanguageFilePaths::shippedFileNamePattern($directory);
                $shipped_files = MigratedLanguageFilePaths::findShippedPoFiles($this->ilias_absolute_path, $directory);
                $template = MigratedLanguageFilePaths::shippedTemplatePath($this->ilias_absolute_path, $directory);
            } catch (\InvalidArgumentException $e) {
                $inform(sprintf('WARNING: The language files of module "%s" are not compiled: %s', $module, $e->getMessage()));
                continue;
            }
            $contributed[$module] = $module;
            if (!MigratedLanguageFilePaths::isFoundWithoutGraph($this->ilias_absolute_path, $directory)) {
                $inform(sprintf(
                    'WARNING: Module "%s" has no template "components/<Vendor>/<Component>/lang/%s.pot" - without a build Setup and CLI do not recognise it as shipped.',
                    $module,
                    $module
                ));
            }
            $base = rtrim($this->ilias_absolute_path, '/') . '/' . ltrim($directory->getPath(), '/');
            $matching_files = [...$matching_files, ...(glob($base . sprintf($pattern, '*') . '.po') ?: [])];
            $all_shipped_files = [...$all_shipped_files, ...array_values($shipped_files)];
            if ($shipped_files === []) {
                continue;
            }
            $modules[$module] = [
                'directory' => $directory,
                'languages' => $shipped_files,
                'template' => is_file($template) ? $template : null,
            ];
        }
        // files matching a pattern that yield no language key, e.g. "tos_pt_BR.po"; "chatroom_*.po" also
        // matches "chatroom_adm_de.po" of another module in the same directory
        foreach (array_unique(array_diff($matching_files, $all_shipped_files)) as $unknown) {
            $inform(sprintf('WARNING: "%s" is not compiled: its name contains no valid language key.', $this->relativePath($unknown)));
        }
        ksort($modules, SORT_STRING);
        sort($contributed, SORT_STRING);

        return [$modules, array_values($contributed)];
    }

    /**
     * @param array<string, array{directory: LanguageFileDirectory, languages: array<string, string>, template: ?string}> $modules
     */
    private function buildId(array $modules, string $locale_source): string
    {
        $parts = [(string) self::FORMAT_VERSION, $this->codeFingerprint(), $this->directoryFingerprint($locale_source)];
        foreach ($modules as $module => $shipped) {
            $files = $shipped['languages'];
            if ($shipped['template'] !== null) {
                $files['pot'] = $shipped['template'];
            }
            foreach ($files as $lang_key => $file) {
                $hash = @hash_file('sha256', $file);
                $parts[] = $module . '|' . $lang_key . '|' . $this->relativePath($file) . '|' . ($hash === false ? 'unreadable' : $hash);
            }
        }

        return 'b-' . substr(hash('sha256', implode("\n", $parts)), 0, 32);
    }

    /**
     * Over the code that determines the build, see FORMAT_VERSION.
     */
    private function codeFingerprint(): string
    {
        $parts = [];
        foreach ([self::class, ShippedTranslations::class, TranslationMarkupPolicy::class, TranslationCatalog::class, TranslationEntry::class, MigratedLanguageFileSync::class, MigratedLanguageFilePaths::class, PluralForms::class, PluralFormKey::class, NativeGettext::class] as $class) {
            $file = (new \ReflectionClass($class))->getFileName();
            $parts[] = is_string($file) ? (string) @hash_file('sha256', $file) : '';
        }

        return hash('sha256', implode('|', $parts));
    }

    /**
     * Over the content of every file below $directory - a locale copied from another version of the
     * C library may not be loadable by the one in use.
     */
    private function directoryFingerprint(string $directory): string
    {
        $parts = [];
        foreach ($this->listFiles($directory) as $relative => $file) {
            $parts[] = $relative . '|' . (string) @hash_file('sha256', $file);
        }

        return hash('sha256', implode("\n", $parts));
    }

    /**
     * @return array<string, string> path relative to $directory => absolute path, sorted
     */
    private function listFiles(string $directory): array
    {
        $files = [];
        foreach (scandir($directory) ?: [] as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            $path = $directory . '/' . $name;
            if (is_dir($path)) {
                foreach ($this->listFiles($path) as $relative => $file) {
                    $files[$name . '/' . $relative] = $file;
                }
            } elseif (is_file($path)) {
                $files[$name] = $path;
            }
        }
        ksort($files, SORT_STRING);

        return $files;
    }

    /**
     * @throws RuntimeException if no locale to ship is found
     */
    private function findLocaleSource(): string
    {
        foreach ($this->locale_source !== null ? [$this->locale_source] : self::LOCALE_SOURCES as $candidate) {
            if (is_file($candidate . '/LC_MESSAGES/SYS_LC_MESSAGES') || is_file($candidate . '/LC_CTYPE')) {
                return $candidate;
            }
        }

        throw new RuntimeException(sprintf(
            'The locale "C.utf8" of the C library was not found (looked for %s) - it is needed to serve the language files with native gettext.',
            implode(', ', $this->locale_source !== null ? [$this->locale_source] : self::LOCALE_SOURCES)
        ));
    }

    /**
     * @param array<string, array{0: int, 1: string}> $files the manifest, extended by the copied files
     */
    private function copyLocale(string $source, string $target, string $build_directory, array &$files): void
    {
        foreach ($this->listFiles($source) as $relative => $file) {
            $content = @file_get_contents($file);
            if ($content === false) {
                throw new RuntimeException(sprintf('Could not read "%s".', $file));
            }
            $this->ensureDirectory(dirname($target . '/' . $relative));
            $this->writeFile($target . '/' . $relative, $content);
            $files[substr($target . '/' . $relative, strlen($build_directory) + 1)] = [strlen($content), hash('sha256', $content)];
        }
    }

    /**
     * @return Manifest|null
     */
    private static function readManifest(string $build_directory): ?array
    {
        $content = is_link($build_directory) ? false : @file_get_contents($build_directory . '/' . self::MANIFEST_FILE);
        $data = $content === false ? null : json_decode($content, true);
        $sample = $data['sample'] ?? null;
        if (
            !is_array($data)
            || !is_array($data['files'] ?? null)
            || !is_array($sample)
            || !is_string($sample['module'] ?? null)
            || !is_string($sample['lang'] ?? null)
            || !is_string($sample['key'] ?? null)
            || !is_string($sample['value'] ?? null)
            || !MigratedLanguageFilePaths::isValidModule($sample['module'])
            || !MigratedLanguageFilePaths::isValidLanguageKey($sample['lang'])
        ) {
            return null;
        }
        $files = [];
        foreach ($data['files'] as $file => $details) {
            if (!is_string($file) || !is_array($details) || !is_int($details[0] ?? null) || !is_string($details[1] ?? null)) {
                return null;
            }
            $files[$file] = [$details[0], $details[1]];
        }
        $languages = [];
        foreach (is_array($data['languages'] ?? null) ? $data['languages'] : [] as $module => $lang_keys) {
            if (!is_string($module) || !MigratedLanguageFilePaths::isValidModule($module) || !is_array($lang_keys)) {
                return null;
            }
            $languages[$module] = array_values(array_filter(
                $lang_keys,
                static fn($lang_key): bool => is_string($lang_key) && MigratedLanguageFilePaths::isValidLanguageKey($lang_key)
            ));
        }

        return [
            'files' => $files,
            'sample' => $sample,
            'languages' => $languages,
            'warnings' => self::stringList($data['warnings'] ?? []),
            'hashes' => self::hashes($data['hashes'] ?? []),
            'collisions' => self::stringList($data['collisions'] ?? []),
            'compatibility' => is_string($data['compatibility'] ?? null) ? $data['compatibility'] : '',
        ];
    }

    /**
     * Whether every file of $manifest is in $build_directory as it was written - without fsync(), a
     * crash can leave empty or truncated files behind.
     *
     * @param array{files: array<string, array{0: int, 1: string}>} $manifest
     */
    private function isIntact(string $build_directory, array $manifest): bool
    {
        foreach ($manifest['files'] as $relative => [$size, $hash]) {
            $file = $build_directory . '/' . $relative;
            clearstatcache(true, $file);
            if (
                str_contains($relative, '..')
                || is_link($file)
                || !is_file($file)
                || @filesize($file) !== $size
                || @hash_file('sha256', $file) !== $hash
            ) {
                return false;
            }
        }

        return $manifest['files'] !== [];
    }

    /**
     * @param Pointer $pointer
     */
    private function writePointer(string $artifact_directory, array $pointer): void
    {
        AtomicFileWriter::write(
            MigratedLanguageFilePaths::buildPointerFile($artifact_directory),
            json_encode(
                $pointer,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
            ) . "\n",
            $artifact_directory
        );
    }

    /**
     * Removes everything of a former build except the builds $keep: other builds, temporary directories and
     * files of aborted builds, and the former layout (`<lang>/<module>.mo`, `index.json`). Anything else stays.
     *
     * @param list<string> $keep build ids
     * @return int the number of removed entries
     */
    private function removeObsolete(string $artifact_directory, array $keep): int
    {
        $removed = 0;
        $pointer_name = basename(MigratedLanguageFilePaths::buildPointerFile($artifact_directory));
        foreach (scandir($artifact_directory) ?: [] as $name) {
            $path = $artifact_directory . '/' . $name;
            $obsolete = match (true) {
                $name === '.' || $name === '..' || in_array($name, $keep, true) => false,
                MigratedLanguageFilePaths::isBuildId($name),
                preg_match('/\A\.b-[0-9a-f]+\.[0-9a-f]+\z/', $name) === 1,
                str_starts_with($name, '.' . $pointer_name . '.'),
                $name === 'index.json',
                str_starts_with($name, '.index.json.') => true,
                preg_match('/\A[a-z]{2}\z/', $name) === 1 => is_dir($path) && !is_link($path) && $this->holdsOnlyCatalogs($path),
                default => false,
            };
            if ($obsolete) {
                $this->removeTree($artifact_directory, $path);
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * Whether $directory (of the former layout `<lang>/<module>.mo`) holds nothing but catalogs and
     * temporary files of them.
     */
    private function holdsOnlyCatalogs(string $directory): bool
    {
        foreach (scandir($directory) ?: [] as $name) {
            if ($name !== '.' && $name !== '..' && (!str_contains($name, '.mo') || !is_file($directory . '/' . $name))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes $path recursively - only below $artifact_directory, and without following a symbolic
     * link (a link itself is removed).
     */
    private function removeTree(string $artifact_directory, string $path): void
    {
        if (!str_starts_with($path, rtrim($artifact_directory, '/') . '/') || str_contains($path, '/../')) {
            return;
        }
        if (is_link($path) || is_file($path)) {
            @unlink($path);
            return;
        }
        if (!is_dir($path)) {
            return;
        }
        foreach (scandir($path) ?: [] as $name) {
            if ($name !== '.' && $name !== '..') {
                $this->removeTree($artifact_directory, $path . '/' . $name);
            }
        }
        @rmdir($path);
    }

    private function writeFile(string $file, string $content): void
    {
        if (@file_put_contents($file, $content) !== strlen($content)) {
            throw new RuntimeException(sprintf('Could not write "%s".', $file));
        }
    }

    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Could not create the directory "%s".', $directory));
        }
    }

    private function relativePath(string $file): string
    {
        $root = rtrim($this->ilias_absolute_path, '/') . '/';

        return str_starts_with($file, $root) ? substr($file, strlen($root)) : $file;
    }

    /**
     * @return array<string, array<string, string>> module => language => hash
     */
    private static function hashes(mixed $value): array
    {
        $hashes = [];
        foreach (is_array($value) ? $value : [] as $module => $languages) {
            foreach (is_array($languages) ? $languages : [] as $lang_key => $hash) {
                if (is_string($module) && is_string($lang_key) && is_string($hash)) {
                    $hashes[$module][$lang_key] = $hash;
                }
            }
        }

        return $hashes;
    }

    /**
     * Whether the build of $artifact_directory serves exactly the shipped `.po` $shipped_po of
     * $module for $lang_key (its content hash, see the manifest) - `false` without a build or after
     * a change of the `.po` since (e.g. by "merge", see ShippedPoMerger).
     */
    public static function servesShippedPo(string $artifact_directory, string $module, string $lang_key, string $shipped_po): bool
    {
        $build = self::readPointer($artifact_directory)['build'] ?? null;
        if ($build === null) {
            return false;
        }
        $build_directory = MigratedLanguageFilePaths::buildDirectory($artifact_directory, $build);
        if (!isset(self::$manifests[$build_directory])) {
            $manifest = self::readManifest($build_directory);
            if ($manifest === null) {
                return false;
            }
            self::$manifests[$build_directory] = $manifest;
        }
        $hash = self::$manifests[$build_directory]['hashes'][$module][$lang_key] ?? null;

        return $hash !== null && $hash === @hash_file('sha256', $shipped_po);
    }

    /**
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        return is_array($value) ? array_values(array_filter($value, 'is_string')) : [];
    }
}
