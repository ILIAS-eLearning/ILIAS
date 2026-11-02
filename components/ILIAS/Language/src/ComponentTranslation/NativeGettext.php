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
 * The process state native gettext (PHP extension "gettext") needs to serve the compiled catalogs of the
 * migrated modules, and the one place that calls it.
 *
 * Native gettext only translates while LC_MESSAGES is not "C"/"POSIX" and finds a catalog as
 * `<bound directory>/<language>/LC_MESSAGES/<domain>.mo`. ILIAS does not depend on the locale's language, so a
 * real locale in LC_MESSAGES (e.g. of ilInitialisation::initLocale()) stays. Only for "C"/"POSIX"/"C.<codeset>":
 * - glibc: the build ships its own locale (a copy of "C.utf8", see ShippedTranslationsBuild) named
 *   LOCALE_NAME; activate() loads it via LOCPATH and removes LOCPATH again right away (while it is set,
 *   setlocale() finds no system locale);
 * - macOS/BSD (see usesSystemLocale()): the C library ignores LOCPATH, gettext comes from GNU libintl, so
 *   activate() sets the first existing of SYSTEM_LOCALES for LC_MESSAGES and the environment variable LC_ALL
 *   (GNU libintl ignores LANGUAGE for "C.<codeset>");
 * - LANGUAGE is CATALOG_LANGUAGE, so "<language>" is always "messages"; the language of a catalog is part
 *   of its domain ("<module>.<lang>", see MigratedLanguageFilePaths), as the C library caches per process
 *   and a later LANGUAGE change would not reliably switch catalogs;
 * - every domain is bound with codeset UTF-8, so LC_CTYPE never converts a value.
 *
 * Both settings are process-wide. PHP takes them back at the end of every request (putenv() variables,
 * setlocale() reset to "C"), so no shutdown function is needed (it would stop later shutdown functions
 * from translating). In a thread-safe (ZTS) PHP the end of another thread's request can take them back
 * mid-request: isActive() checks, reactivateIfLost() activates again (the administration shows a hint,
 * see isThreadSafe()). Child processes inherit LANGUAGE=messages, which is harmless, and on macOS/BSD
 * LC_ALL=<active locale> (a kept one too, e.g. the locale of the administration), which overrides the
 * locale of the environment for them.
 *
 * The C library never reloads a catalog file it has read once (also not a replaced one), so every new
 * state (a build, an overlay revision) gets a directory of its own, and binding the domain loads it.
 */
final class NativeGettext
{
    public const string LOCALE_NAME = 'ilias_messages';

    /** LC_MESSAGES candidates where the system locale is used, see usesSystemLocale(). */
    public const array SYSTEM_LOCALES = ['en_US.UTF-8', 'en_US.utf8'];

    private const array SYSTEM_LOCALE_OS_FAMILIES = ['Darwin', 'BSD'];

    /** The value of LANGUAGE: the directory below a bound directory the catalogs are looked up in. */
    public const string CATALOG_LANGUAGE = 'messages';

    public const string CATALOG_DIRECTORY = self::CATALOG_LANGUAGE . '/LC_MESSAGES';

    private const string CODESET = 'UTF-8';

    /** Longer message ids are refused by PHP (ValueError), so they are never found. */
    private const int MAX_MESSAGE_LENGTH = 4096;

    private static ?string $locale_directory = null;

    /** What setlocale() returned in the last successful activate(). */
    private static ?string $active_locale = null;

    /**
     * LANGUAGE, LC_MESSAGES and the environment variable LC_ALL before the first activate() since the
     * last restore() (`false`: not set), see restore().
     *
     * @var array{language: string|false, messages: string|false, all: string|false}|null
     */
    private static ?array $previous = null;

    private static ?bool $thread_safe_for_tests = null;

    private static ?bool $system_locale_for_tests = null;

    private static ?string $problem = null;

    private static bool $messages_set = false;

    public static function isExtensionLoaded(): bool
    {
        return extension_loaded('gettext');
    }

    /**
     * Whether PHP is thread-safe (ZTS): the settings of activate() then apply to every thread and are
     * not taken back.
     */
    public static function isThreadSafe(): bool
    {
        return self::$thread_safe_for_tests ?? (bool) PHP_ZTS;
    }

    /**
     * Makes isThreadSafe() return $thread_safe (`null`: PHP_ZTS again) - for tests only.
     *
     * @internal
     */
    public static function useThreadSafeForTests(?bool $thread_safe): void
    {
        self::$thread_safe_for_tests = $thread_safe;
    }

    /** macOS/BSD: activate() uses a SYSTEM_LOCALES entry, their C library ignores LOCPATH. */
    public static function usesSystemLocale(): bool
    {
        return self::$system_locale_for_tests ?? in_array(PHP_OS_FAMILY, self::SYSTEM_LOCALE_OS_FAMILIES, true);
    }

    /**
     * Makes usesSystemLocale() return $system_locale (`null`: by PHP_OS_FAMILY again) - for tests only.
     *
     * @internal
     */
    public static function useSystemLocaleForTests(?bool $system_locale): void
    {
        self::$system_locale_for_tests = $system_locale;
    }

    /**
     * Sets LANGUAGE and, if LC_MESSAGES holds no real locale, the locale LOCALE_NAME of $locale_directory (or, see
     * usesSystemLocale(), a system locale) for it; if it fails, getProblem() tells why.
     */
    public static function activate(string $locale_directory): bool
    {
        if (!self::isExtensionLoaded()) {
            self::$problem = 'the PHP extension "gettext" is not loaded in this PHP (the web server needs it like the CLI that ran the build)';
            return false;
        }
        if (self::$locale_directory === $locale_directory && self::isActive()) {
            return true;
        }
        if (self::usesSystemLocale()) {
            return self::activateSystemLocale($locale_directory);
        }
        // also checked when a real locale stays: a build without its locale is broken
        if (!is_dir($locale_directory . '/' . self::LOCALE_NAME)) {
            self::$problem = sprintf('the locale "%s" is missing in "%s"', self::LOCALE_NAME, $locale_directory);
            return false;
        }

        self::$previous ??= self::processState();
        $kept = self::keptLocale();
        if ($kept !== null) {
            return self::activated($locale_directory, $kept, false);
        }
        $locpath = getenv('LOCPATH');
        putenv('LOCPATH=' . $locale_directory);
        $locale = setlocale(LC_MESSAGES, self::LOCALE_NAME);
        // as long as LOCPATH is set, setlocale() finds no locale of the system (e.g. for LC_TIME)
        putenv($locpath === false ? 'LOCPATH' : 'LOCPATH=' . $locpath);
        if ($locale !== self::LOCALE_NAME) {
            self::$problem = sprintf('the locale "%s" of "%s" cannot be activated', self::LOCALE_NAME, $locale_directory);
            return false;
        }

        return self::activated($locale_directory, $locale, true);
    }

    private static function activateSystemLocale(string $locale_directory): bool
    {
        self::$previous ??= self::processState();
        $kept = self::keptLocale();
        $locale = $kept ?? setlocale(LC_MESSAGES, self::SYSTEM_LOCALES);
        if ($locale === false) {
            self::$problem = sprintf(
                'none of the system locales %s exists for LC_MESSAGES (needed on %s, where native gettext cannot use the locale of the build)',
                implode(', ', self::SYSTEM_LOCALES),
                PHP_OS_FAMILY
            );
            return false;
        }
        // GNU libintl on macOS reads the locale of the environment before setlocale()'s, and "C" there ignores LANGUAGE
        putenv('LC_ALL=' . $locale);

        return self::activated($locale_directory, $locale, $kept === null);
    }

    /** A real LC_MESSAGES locale stays, so ICU & Co. keep seeing it (e.g. the one of ilInitialisation::initLocale()). */
    private static function keptLocale(): ?string
    {
        $locale = setlocale(LC_MESSAGES, '0');
        if (
            $locale === false
            || $locale === 'C'
            || $locale === 'POSIX'
            || str_starts_with($locale, 'C.')
            || $locale === self::LOCALE_NAME
            || (self::$messages_set && $locale === self::$active_locale)
        ) {
            return null;
        }

        return $locale;
    }

    /**
     * @return array{language: string|false, messages: string|false, all: string|false}
     */
    private static function processState(): array
    {
        return ['language' => getenv('LANGUAGE'), 'messages' => setlocale(LC_MESSAGES, '0'), 'all' => getenv('LC_ALL')];
    }

    private static function activated(string $locale_directory, string $locale, bool $messages_set): bool
    {
        putenv('LANGUAGE=' . self::CATALOG_LANGUAGE);
        self::$locale_directory = $locale_directory;
        self::$active_locale = $locale;
        self::$messages_set = $messages_set;
        self::$problem = null;

        return true;
    }

    /**
     * Whether activate() succeeded and its settings are still in place (other code, e.g.
     * ilInitialisation::initLocale() with setlocale(LC_ALL, …), may have changed LC_MESSAGES since).
     */
    public static function isActive(): bool
    {
        return self::$locale_directory !== null
            && setlocale(LC_MESSAGES, '0') === self::$active_locale
            && getenv('LANGUAGE') === self::CATALOG_LANGUAGE
            && (!self::usesSystemLocale() || getenv('LC_ALL') === self::$active_locale);
    }

    /**
     * Activates the locale again if it was activated before and the settings changed since.
     *
     * @return bool whether it had to be (and could be) activated again - a lookup that found
     *         nothing is worth repeating then
     */
    public static function reactivateIfLost(): bool
    {
        if (self::$locale_directory === null || self::isActive()) {
            return false;
        }

        return self::activate(self::$locale_directory);
    }

    /**
     * Takes LANGUAGE, LC_MESSAGES (if activate() set it) and LC_ALL back to the state before the first
     * activate(); settings changed by other code since stay. Only for long-running processes (e.g. Setup's build
     * after its check), a web request needs no call. No-op in a thread-safe PHP, where other threads may use them.
     * The activated directory is kept, so reactivateIfLost() can activate it again.
     */
    public static function restore(): void
    {
        if (self::$previous === null || self::isThreadSafe()) {
            return;
        }
        if (self::$messages_set && self::$previous['messages'] !== false && setlocale(LC_MESSAGES, '0') === self::$active_locale) {
            setlocale(LC_MESSAGES, self::$previous['messages']);
        }
        if (getenv('LANGUAGE') === self::CATALOG_LANGUAGE) {
            putenv(self::$previous['language'] === false ? 'LANGUAGE' : 'LANGUAGE=' . self::$previous['language']);
        }
        if (self::usesSystemLocale() && getenv('LC_ALL') === self::$active_locale) {
            putenv(self::$previous['all'] === false ? 'LC_ALL' : 'LC_ALL=' . self::$previous['all']);
        }
        self::$previous = null;
    }

    public static function getProblem(): ?string
    {
        return self::$problem;
    }

    /**
     * Binds $domain to $directory with the codeset UTF-8.
     *
     * @return bool `false` if the extension is missing or $directory does not exist
     */
    public static function bind(string $domain, string $directory): bool
    {
        return self::isExtensionLoaded()
            && @bindtextdomain($domain, $directory) !== false
            && @bind_textdomain_codeset($domain, self::CODESET) !== false;
    }

    /**
     * Whether the catalog of the bound $domain is readable: its header (the translation of the empty
     * message id) is not empty.
     */
    public static function isLoaded(string $domain): bool
    {
        return self::isExtensionLoaded() && dgettext($domain, '') !== '';
    }

    /**
     * The translation of $message_id in $domain, or $message_id itself if there is none (as the C
     * library answers), also for an empty or too long $message_id.
     */
    public static function translate(string $domain, string $message_id): string
    {
        if (!self::canLookUp($message_id)) {
            return $message_id;
        }

        return dgettext($domain, $message_id);
    }

    /**
     * The form of the plural message $message_id the "Plural-Forms" rule of the catalog selects for $n
     * (a negative $n counts as its absolute value), or $message_id itself if there is none.
     */
    public static function translatePlural(string $domain, string $message_id, int $n): string
    {
        if (!self::canLookUp($message_id)) {
            return $message_id;
        }
        $n = $n === PHP_INT_MIN ? PHP_INT_MAX : abs($n);

        return dngettext($domain, $message_id, $message_id, $n);
    }

    private static function canLookUp(string $message_id): bool
    {
        return $message_id !== '' && strlen($message_id) <= self::MAX_MESSAGE_LENGTH && self::isExtensionLoaded();
    }

    /**
     * Forgets the activation (without taking the settings back) - for tests only.
     *
     * @internal
     */
    public static function resetForTests(): void
    {
        self::$thread_safe_for_tests = null;
        self::restore();
        self::$system_locale_for_tests = null;
        self::$locale_directory = null;
        self::$active_locale = null;
        self::$messages_set = false;
        self::$previous = null;
        self::$problem = null;
    }
}
