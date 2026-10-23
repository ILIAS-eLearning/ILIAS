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
 * Build status and problems of the ILIAS modules served by TranslationGettextRepository: which modules are
 * shipped, whether a `.po` is left unbuilt, and the problems of this request for the administration.
 *
 * Static: the problems are collected per request like a log, also from static callers (ilLanguage::_lookupEntry(),
 * the write paths). No part of TranslationRepository, as no translation is read here.
 *
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
     * @var array<string, string>|null see MigratedLanguageFilePaths::findShippedTemplates()
     */
    private static ?array $shipped_templates = null;

    /**
     * @var array<string, bool> see isShippedModule()
     */
    private static array $shipped_modules = [];

    private static ?string $client_data_dir = null;
    private static bool $client_data_dir_resolved = false;

    /**
     * @var array<string, bool>
     */
    private static array $shipped_without_build = [];

    /**
     * The problems of this request, each logged once: message => whether fatal (see reportProblem()).
     *
     * @var array<string, bool>
     */
    private static array $problems = [];

    private static bool $activation_problem_reported = false;

    /**
     * Whether $module belongs to the ILIAS components (never served from lng_data then). Works without the
     * component graph: from the build's recorded contributions, else from the shipped templates.
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
     * Whether the template directory of $module has a `<module>_<lang>.po`.
     */
    public static function shipsPoFor(string $module, string $lang_key): bool
    {
        $directory = self::shippedTemplates()[$module] ?? null;

        return $directory !== null
            && MigratedLanguageFilePaths::isValidLanguageKey($lang_key)
            && is_file($directory . '/' . $module . '_' . $lang_key . '.po');
    }

    /**
     * CLIENT_DATA_DIR, before it is defined (Setup, CLI) resolveClientDataDir(); cached until reset().
     */
    public static function clientDataDir(): ?string
    {
        if (defined('CLIENT_DATA_DIR')) {
            return (string) CLIENT_DATA_DIR;
        }
        if (!self::$client_data_dir_resolved) {
            self::$client_data_dir = MigratedLanguageFilePaths::resolveClientDataDir(self::iliasDirectory());
            self::$client_data_dir_resolved = true;
        }

        return self::$client_data_dir;
    }

    /**
     * Whether $module ships a `.po` for $lang_key that the build does not serve; cached until reset().
     */
    public static function shipsPoWithoutBuild(string $module, string $lang_key, ?LanguageFileDirectory $directory): bool
    {
        if (!isset(self::$shipped_without_build[$module . '|' . $lang_key])) {
            try {
                self::$shipped_without_build[$module . '|' . $lang_key] = $directory === null
                    ? self::shipsPoFor($module, $lang_key)
                    : is_file(MigratedLanguageFilePaths::shippedBasePath(self::iliasDirectory(), $directory, $lang_key) . '.po');
            } catch (\InvalidArgumentException) {
                self::$shipped_without_build[$module . '|' . $lang_key] = false;
            }
        }

        return self::$shipped_without_build[$module . '|' . $lang_key];
    }

    /**
     * Reports (fatal) a missing build; `false` if there is one.
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
                    (new TranslationGettextRepository())->getKeys((string) $module, $languages[0], $client_data_dir);
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
     * Native gettext cannot be activated (first use or later): one message per request, whatever the reason.
     */
    public static function reportActivationProblem(string $reason): void
    {
        if (self::$activation_problem_reported) {
            return;
        }
        self::$activation_problem_reported = true;
        self::reportProblem(sprintf('Native gettext is not available: %s.', $reason), true);
    }

    /**
     * Forgets what was read in this request, the served translations included (e.g. after a new build in the
     * same process).
     */
    public static function reset(): void
    {
        (new TranslationGettextRepository())->invalidateAll();
        self::$pointer_read = false;
        self::$pointer = null;
        self::$shipped_templates = null;
        self::$shipped_modules = [];
        self::$client_data_dir = null;
        self::$client_data_dir_resolved = false;
        self::$shipped_without_build = [];
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
     * @internal for TranslationGettextRepository, which shares the pointer read until reset()
     *
     * @return Pointer|null
     */
    public static function pointer(): ?array
    {
        if (!self::$pointer_read) {
            self::$pointer = ShippedTranslationsBuild::readPointer(self::artifactDirectory());
            self::$pointer_read = true;
        }

        return self::$pointer;
    }

    /**
     * @internal for TranslationGettextRepository
     */
    public static function artifactDirectory(): string
    {
        return MigratedLanguageFilePaths::shippedArtifactDirectory(self::iliasDirectory());
    }

    private static function iliasDirectory(): string
    {
        return defined('ILIAS_ABSOLUTE_PATH') ? (string) ILIAS_ABSOLUTE_PATH : dirname(__DIR__, 5);
    }
}
