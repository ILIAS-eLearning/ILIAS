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
 * The runtime read path of the migrated modules (see ilLanguage): what the build serves
 * (ShippedTranslationsBuild) with the overlay of local changes (MigratedLanguageFileSync) on top, looked up
 * via native gettext (NativeGettext), never via the gettext/gettext library or lng_data/lng_modules.
 *
 * A module counts as migrated for a language if the build in `artifacts/language/current.json` was built for
 * it. On first use per module and language in a request (keysOf()):
 * - native gettext is activated with the build's locale, the domain "<module>.<lang>" bound to the build and,
 *   if `<client data dir>/lang/<module>/<lang>/current` names a revision, "<module>.<lang>.overlay" to it;
 * - the identifiers are those of the build's `keys/<module>.php` plus the overlay revision's `keys.json`.
 * text() looks an identifier of the overlay up in the overlay domain, every other in the build's.
 *
 * Problems (no build, no native gettext, an unreadable catalog) are logged once per request and kept for the
 * administration (see getProblem()); identifiers of the module concerned are not found then (txt() serves
 * "-<identifier>-"). An unreadable overlay is logged and left out: the shipped values are served.
 *
 * Static: ilLanguage::_lookupEntry() is static, and native gettext's state is per process anyway.
 *
 * @phpstan-type State array{keys: array<string, string>, ok: bool, domain: string, overlay_domain: ?string, overlay_keys: array<string, true>, overlay_checked: bool}
 * @phpstan-import-type Pointer from ShippedTranslationsBuild
 */
final class MigratedTranslations
{
    private static bool $pointer_read = false;

    /**
     * @var Pointer|null
     */
    private static ?array $pointer = null;

    /**
     * The identifiers of the build per module, `null` if they cannot be read.
     *
     * @var array<string, array<string, string>|null>
     */
    private static array $build_keys = [];

    /**
     * @var array<string, string>|null see MigratedLanguageFilePaths::findShippedTemplates()
     */
    private static ?array $shipped_templates = null;

    /**
     * @var array<string, bool> see isShippedModule()
     */
    private static array $shipped_modules = [];

    private static ?string $client_data_dir = null;

    /**
     * Per "<module>|<lang>", `null` for a module not migrated for the language.
     *
     * @var array<string, State|null>
     */
    private static array $states = [];

    /**
     * The problems of this request, each logged once: message => whether fatal (see reportProblem()).
     *
     * @var array<string, bool>
     */
    private static array $problems = [];

    private static bool $activation_problem_reported = false;

    /**
     * `current` only holds the name of a revision - more is never read.
     */
    private const int MAX_REVISION_FILE_SIZE = 128;

    /**
     * The identifiers of $module for $lang_key (identifier => $module, shipped and overlay), `null` if
     * $module is not migrated for it. Activates native gettext and binds the domains, see the class docblock.
     *
     * @param string|null $client_data_dir where the overlay is looked up, none without
     * @return array<string, string>|null
     */
    public static function keysOf(string $module, string $lang_key, ?string $client_data_dir): ?array
    {
        return self::state($module, $lang_key, $client_data_dir)['keys'] ?? null;
    }

    /**
     * Whether $module belongs to the ILIAS components: never served from lng_data/lng_modules then. Known
     * without the component graph, so also in the Setup: from the contributions the build recorded, without a
     * build (or one that did not record them) from the shipped templates.
     */
    public static function isShippedModule(string $module): bool
    {
        if (!isset(self::$shipped_modules[$module])) {
            $pointer = self::pointer();
            // a pointer without build (e.g. written from no directories) lists nothing reliable
            self::$shipped_modules[$module] = $pointer !== null && $pointer['build'] !== null && $pointer['shipped'] !== null
                ? in_array($module, $pointer['shipped'], true)
                : isset($pointer['modules'][$module]) || isset(self::shippedTemplates()[$module]);
        }

        return self::$shipped_modules[$module];
    }

    /**
     * Whether the template directory of $module has a `.po` for $lang_key named by the default pattern
     * ("<module>_<lang>.po").
     */
    public static function shipsPoFor(string $module, string $lang_key): bool
    {
        $directory = self::shippedTemplates()[$module] ?? null;

        return $directory !== null
            && MigratedLanguageFilePaths::isValidLanguageKey($lang_key)
            && is_file($directory . '/' . $module . '_' . $lang_key . '.po');
    }

    /**
     * CLIENT_DATA_DIR, before it is defined (Setup, CLI) MigratedLanguageFilePaths::resolveClientDataDir();
     * a found directory is kept until reset(), a missing one is looked for again.
     */
    public static function clientDataDir(): ?string
    {
        if (defined('CLIENT_DATA_DIR')) {
            return (string) CLIENT_DATA_DIR;
        }

        return self::$client_data_dir ??= MigratedLanguageFilePaths::resolveClientDataDir(self::iliasDirectory());
    }

    /**
     * Reports (fatal) that there is no build; `false` if there is one.
     */
    public static function reportMissingBuild(): bool
    {
        $pointer = self::pointer();
        if ($pointer !== null && $pointer['build'] !== null) {
            return false;
        }
        self::reportProblem(self::missingBuildMessage(), true);

        return true;
    }

    /**
     * The value of $key in $module for $lang_key, `null` if there is none (not migrated, not
     * translated, or a problem, see the class docblock).
     */
    public static function text(string $module, string $lang_key, string $key, ?string $client_data_dir): ?string
    {
        $state = self::servedState($module, $lang_key, $key, $client_data_dir);
        if ($state === null) {
            return null;
        }
        if ($state['overlay_domain'] !== null && isset($state['overlay_keys'][$key])) {
            // listed in the overlay: a value equal to the identifier is a value as well
            $value = self::lookup($state['overlay_domain'], $key);
            return $value === false ? null : $value ?? $key;
        }

        $value = self::lookup($state['domain'], $key);
        if ($value !== null) {
            return $value === false ? null : $value;
        }

        return self::lookup($state['domain'], ShippedTranslations::IDENTITY_CONTEXT . "\x04" . $key) === '1' ? $key : null;
    }

    /**
     * The form of the plural message $key the plural rule of the language selects for $n; `null` if $key is
     * no plural message there (also if the overlay holds a singular value) or where text() returns `null`.
     */
    public static function pluralText(string $module, string $lang_key, string $key, int $n, ?string $client_data_dir): ?string
    {
        $state = self::servedState($module, $lang_key, $key, $client_data_dir);
        if ($state === null) {
            return null;
        }
        $domain = $state['overlay_domain'] !== null && isset($state['overlay_keys'][$key])
            ? $state['overlay_domain']
            : $state['domain'];
        $message_id = ShippedTranslations::PLURAL_CONTEXT . "\x04" . $key;
        $value = NativeGettext::translatePlural($domain, $message_id, $n);
        if ($value === $message_id && !NativeGettext::isActive()) {
            if (!NativeGettext::reactivateIfLost()) {
                self::reportLostActivation();
                return null;
            }
            $value = NativeGettext::translatePlural($domain, $message_id, $n);
        }

        return $value === $message_id ? null : $value;
    }

    /**
     * Records a problem (logged once per request, see getProblem()).
     *
     * @param bool $fatal whether it concerns every migrated module (no native gettext, no build) rather than
     *        one module, language or overlay
     */
    public static function reportProblem(string $message, bool $fatal = false): void
    {
        if (isset(self::$problems[$message])) {
            return;
        }
        self::$problems[$message] = $fatal;

        global $DIC;
        try {
            $DIC->logger()->forComponent('lang')->warning($message);
        } catch (\Throwable) {
            error_log($message);
        }
    }

    /**
     * The first fatal problem of this request, otherwise the first one - after checking the first module of
     * the build, so the administration can show it even if no migrated module was used yet. `null` if there
     * is none. Paths below the ILIAS directory and the client data directory are relative in the message
     * (`<data>/…`); the log has them in full.
     *
     * @return array{message: string, fatal: bool}|null
     */
    public static function getProblem(?string $client_data_dir): ?array
    {
        $pointer = self::pointer();
        if ($pointer === null) {
            self::reportProblem(self::missingBuildMessage(), true);
        } else {
            foreach ($pointer['modules'] as $module => $languages) {
                if ($languages !== []) {
                    self::state((string) $module, $languages[0], $client_data_dir);
                    break;
                }
            }
        }

        if (self::$problems === []) {
            return null;
        }
        $message = (string) (array_search(true, self::$problems, true) ?: array_key_first(self::$problems));
        $replace = [
            rtrim(self::iliasDirectory(), '/') . '/' => '',
            rtrim(self::artifactDirectory(), '/') . '/' => 'artifacts/language/',
        ];
        if ($client_data_dir !== null) {
            $replace[rtrim($client_data_dir, '/') . '/'] = '<data>/';
        }

        return ['message' => strtr($message, $replace), 'fatal' => self::$problems[$message]];
    }

    /**
     * Forgets what was read for $module/$lang_key, so a write of its overlay that just happened (see
     * MigratedLanguageFileSync) is served for the rest of the request.
     */
    public static function invalidate(string $module, string $lang_key): void
    {
        unset(self::$states[$module . '|' . $lang_key]);
    }

    /**
     * Forgets what was read for $lang_key (e.g. it was uninstalled).
     */
    public static function forgetLanguage(string $lang_key): void
    {
        foreach (array_keys(self::$states) as $cache_key) {
            if (str_ends_with((string) $cache_key, '|' . $lang_key)) {
                unset(self::$states[$cache_key]);
            }
        }
    }

    /**
     * Forgets everything read in this request (e.g. after a new build in the same process).
     */
    public static function reset(): void
    {
        self::$pointer_read = false;
        self::$pointer = null;
        self::$build_keys = [];
        self::$states = [];
        self::$shipped_templates = null;
        self::$shipped_modules = [];
        self::$client_data_dir = null;
    }

    /**
     * Forgets everything read so far, the problems included - for tests only.
     *
     * @internal
     */
    public static function resetForTests(): void
    {
        self::reset();
        self::$problems = [];
        self::$activation_problem_reported = false;
    }

    /**
     * The state of $module/$lang_key if it is readable and lists the identifier $key, `null` otherwise.
     *
     * @return State|null
     */
    private static function servedState(string $module, string $lang_key, string $key, ?string $client_data_dir): ?array
    {
        $state = self::state($module, $lang_key, $client_data_dir);

        return $state !== null && $state['ok'] && isset($state['keys'][$key]) ? $state : null;
    }

    /**
     * @return State|null
     */
    private static function state(string $module, string $lang_key, ?string $client_data_dir): ?array
    {
        $cache_key = $module . '|' . $lang_key;
        if (array_key_exists($cache_key, self::$states)) {
            $state = self::$states[$cache_key];
            // the client data directory became known later in the request (e.g. Setup)
            if ($state !== null && $state['ok'] && !$state['overlay_checked'] && $client_data_dir !== null) {
                $state = self::$states[$cache_key] = self::withOverlay($state, $client_data_dir, $module, $lang_key);
            }
            return $state;
        }
        $pointer = self::pointer();
        if (
            $pointer === null
            || $pointer['build'] === null
            || !MigratedLanguageFilePaths::isValidModule($module)
            || !MigratedLanguageFilePaths::isValidLanguageKey($lang_key)
            || !in_array($lang_key, $pointer['modules'][$module] ?? [], true)
        ) {
            return self::$states[$cache_key] = null;
        }

        $build_directory = MigratedLanguageFilePaths::buildDirectory(self::artifactDirectory(), $pointer['build']);
        $domain = MigratedLanguageFilePaths::shippedDomain($module, $lang_key);
        $keys = self::buildKeys($build_directory, $module);
        $state = ['keys' => $keys ?? [], 'ok' => false, 'domain' => $domain, 'overlay_domain' => null, 'overlay_keys' => [], 'overlay_checked' => false];
        if ($keys === null) {
            self::reportProblem(sprintf(
                'The identifiers of module "%s" cannot be read from "%s" - run "php cli/setup.php build".',
                $module,
                MigratedLanguageFilePaths::buildKeysFile($build_directory, $module)
            ));
        } elseif (!NativeGettext::activate(MigratedLanguageFilePaths::buildLocaleDirectory($build_directory))) {
            self::reportActivationProblem((string) NativeGettext::getProblem());
        } elseif (!NativeGettext::bind($domain, $build_directory) || !NativeGettext::isLoaded($domain)) {
            self::reportProblem(sprintf(
                'Native gettext cannot read "%s" - run "php cli/setup.php build".',
                MigratedLanguageFilePaths::buildMoFile($build_directory, $module, $lang_key)
            ), true);
        } else {
            $state['ok'] = true;
            if ($client_data_dir !== null) {
                $state = self::withOverlay($state, $client_data_dir, $module, $lang_key);
            }
        }

        return self::$states[$cache_key] = $state;
    }

    /**
     * $state with the overlay revision `current` of the overlay of $module/$lang_key names, if any.
     *
     * @param State $state
     * @return State
     */
    private static function withOverlay(array $state, string $client_data_dir, string $module, string $lang_key): array
    {
        $state['overlay_checked'] = true;
        $overlay_directory = MigratedLanguageFilePaths::overlayDirectory($client_data_dir, $module, $lang_key);
        $current_file = MigratedLanguageFilePaths::overlayCurrentFile($overlay_directory);
        if (!file_exists($current_file) && !is_link($current_file)) {
            return $state;
        }
        // like the write path: no symbolic link below the overlay root is followed
        if (self::hasSymbolicLink([dirname($overlay_directory), $overlay_directory, $current_file])) {
            self::reportProblem(sprintf('The overlay "%s" contains a symbolic link - its local changes are not served.', $overlay_directory));
            return $state;
        }
        $revision = self::readStart($current_file, self::MAX_REVISION_FILE_SIZE);
        $revision = $revision === null ? '' : trim($revision);
        if (!MigratedLanguageFilePaths::isOverlayRevision($revision)) {
            self::reportProblem(sprintf('The overlay "%s" names no valid revision - its local changes are not served.', $current_file));
            return $state;
        }
        $revision_directory = MigratedLanguageFilePaths::overlayRevisionDirectory($overlay_directory, $revision);
        $keys_file = MigratedLanguageFilePaths::overlayKeysFile($revision_directory);
        $mo_file = MigratedLanguageFilePaths::overlayMoFile($revision_directory, $module, $lang_key);
        if (self::hasSymbolicLink([$revision_directory, dirname($mo_file, 2), dirname($mo_file), $mo_file, $keys_file])) {
            self::reportProblem(sprintf('The overlay revision "%s" contains a symbolic link - its local changes are not served.', $revision_directory));
            return $state;
        }
        $content = @file_get_contents($keys_file);
        $keys = $content === false ? null : json_decode($content, true);
        if (!is_array($keys) || !array_is_list($keys) || array_filter($keys, 'is_string') !== $keys) {
            self::reportProblem(sprintf('The overlay revision "%s" has no valid "keys.json" - its local changes are not served.', $revision_directory));
            return $state;
        }
        $domain = MigratedLanguageFilePaths::overlayDomain($module, $lang_key);
        if (!NativeGettext::bind($domain, $revision_directory) || !NativeGettext::isLoaded($domain)) {
            self::reportProblem(sprintf(
                'Native gettext cannot read "%s" - its local changes are not served.',
                MigratedLanguageFilePaths::overlayMoFile($revision_directory, $module, $lang_key)
            ));
            return $state;
        }

        $state['overlay_domain'] = $domain;
        $state['overlay_keys'] = array_fill_keys($keys, true);
        $state['keys'] += array_fill_keys($keys, $module);

        return $state;
    }

    /**
     * The translation of $message_id in $domain: `null` if native gettext answers with $message_id (none),
     * `false` if it does not work any more (settings changed and it cannot be activated again); retried
     * after activating it again.
     */
    private static function lookup(string $domain, string $message_id): string|false|null
    {
        $value = NativeGettext::translate($domain, $message_id);
        if ($value !== $message_id) {
            return $value;
        }
        if (!NativeGettext::isActive()) {
            if (!NativeGettext::reactivateIfLost()) {
                self::reportLostActivation();
                return false;
            }
            $value = NativeGettext::translate($domain, $message_id);
        }

        return $value === $message_id ? null : $value;
    }

    private static function reportLostActivation(): void
    {
        self::reportActivationProblem(NativeGettext::getProblem() ?? 'LC_MESSAGES or LANGUAGE were changed and it cannot be activated again');
    }

    /**
     * Native gettext cannot be activated (first use or later): one message per request, whatever the reason.
     */
    private static function reportActivationProblem(string $reason): void
    {
        if (self::$activation_problem_reported) {
            return;
        }
        self::$activation_problem_reported = true;
        self::reportProblem(sprintf('Native gettext is not available: %s.', $reason), true);
    }

    /**
     * @param list<string> $paths
     */
    private static function hasSymbolicLink(array $paths): bool
    {
        foreach ($paths as $path) {
            if (is_link($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * At most the first $length bytes of $file, `null` if it cannot be read.
     */
    private static function readStart(string $file, int $length): ?string
    {
        $handle = is_file($file) ? @fopen($file, 'rb') : false;
        if ($handle === false) {
            return null;
        }
        try {
            $content = fread($handle, $length);
            return $content === false ? null : $content;
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return array<string, string>|null
     */
    private static function buildKeys(string $build_directory, string $module): ?array
    {
        if (!array_key_exists($module, self::$build_keys)) {
            $file = MigratedLanguageFilePaths::buildKeysFile($build_directory, $module);
            $keys = is_file($file) ? (static fn(string $file): mixed => include $file)($file) : null;
            self::$build_keys[$module] = is_array($keys) ? $keys : null;
        }

        return self::$build_keys[$module];
    }

    private static function missingBuildMessage(): string
    {
        return sprintf(
            'There is no build of the language files ("%s") - run "php cli/setup.php build".',
            MigratedLanguageFilePaths::buildPointerFile(self::artifactDirectory())
        );
    }

    /**
     * @return array<string, string>
     */
    private static function shippedTemplates(): array
    {
        return self::$shipped_templates ??= MigratedLanguageFilePaths::findShippedTemplates(self::iliasDirectory());
    }

    /**
     * @return Pointer|null
     */
    private static function pointer(): ?array
    {
        if (!self::$pointer_read) {
            self::$pointer = ShippedTranslationsBuild::readPointer(self::artifactDirectory());
            self::$pointer_read = true;
        }

        return self::$pointer;
    }

    private static function artifactDirectory(): string
    {
        return MigratedLanguageFilePaths::shippedArtifactDirectory(self::iliasDirectory());
    }

    private static function iliasDirectory(): string
    {
        return defined('ILIAS_ABSOLUTE_PATH') ? (string) ILIAS_ABSOLUTE_PATH : dirname(__DIR__, 5);
    }
}
