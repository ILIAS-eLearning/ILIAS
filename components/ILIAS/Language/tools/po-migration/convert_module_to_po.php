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

/**
 * Pilot conversion tool for the PO/MO migration.
 *
 * Reads the legacy `lang/ilias_<lang>.lang` files of one language module and writes, via the language component's
 * TranslationCatalog adapter (so the tool writes exactly what the runtime reads):
 *   - A POT template (<module>.pot, or the file name pattern without "%s", see --pattern)
 *   - One PO file per shipped language (<module>_<lang>.po, or named by --pattern)
 *
 * The entries have no msgctxt: a file belongs to one module (the directory's prefix) and stays valid when the module
 * is renamed (e.g. a plugin that becomes a component). The runtime still reads files with the module as msgctxt.
 *
 * No `.mo` file is written: the `.po` files are the shipped source of truth and are compiled by Setup's build
 * (ShippedTranslationsBuild) into `artifacts/language/`. The per-installation overlay only holds local changes.
 *
 * Headers of an existing target `.po` are kept, so a re-run only changes what the `.lang` files changed. The
 * "Plural-Forms" header comes from plurals.json next to this tool. "Language-Team" is the English language name
 * from `meta_l_<lang>` of `lang/ilias_en.lang`, or, once "meta" is migrated, of a shipped
 * `components/<Vendor>/<Component>/lang/meta_en.po` (default file name pattern only).
 *
 * The reference language (default "en") gives the msgids, their order and the `.pot`. A reference key that a
 * language lacks or has empty gets the reference value, flagged "fuzzy" (fuzzy entries are shipped like all
 * others). A key only other languages have cannot be taken over (see --skip-unmigratable-keys).
 *
 * Plural messages (plurals.json, "modules"): the tool invents no text, it distributes existing values (one taken
 * from the reference language counts). Where the rule has a form for n = 1 alone (found by evaluating the rule:
 * form 0 in en, de, es, it, cs, pl; form 1 in ar), that form gets the configured singular key's value - only if the
 * language translated it (not empty, not from the reference language, no "new variable" marker) - and every other
 * form the message's own value; otherwise every form is the own value. The msgid_plural is "<msgid>_plural" unless
 * configured. The fuzzy flag follows the own value, and is also set where the own value was copied into several
 * forms - except in the source languages (reference language, de, en). The singular key stays an entry of its own.
 *
 * Deliberately declares no named classes or functions: this file lives inside the classmap-scanned components/ tree,
 * and anything named here would end up in Composer's classmap and Setup's class discovery. Everything is a closure.
 *
 * Usage: php convert_module_to_po.php [--pattern=<pattern>] [--skip-unmigratable-keys] [--remove-from-lang]
 *          <module> [referenceLangKey (default "en")] [outputDir]
 *   --pattern  file name of a language without ".po", "%s" = language key (default "<module>_%s");
 *              must match the pattern of the module's ComponentLanguageFileDirectory
 *   --skip-unmigratable-keys  instead of failing: leave out (WARNING on stderr) every key the reference language
 *              does not have, the empty key and every key containing " [" (plural form syntax "<key> [<form>]"),
 *              and use one occurrence of a key that occurs more than once with the same value and comment every
 *              time, as the installer reads them (other duplicates of a left out key only warn, all others fail);
 *              left out keys are not part of the round-trip check
 *   --remove-from-lang  only after a completely successful run (every file written and round-tripped): remove every
 *              line of the module - the left out ones included - from every lang/ilias_<lang>.lang read (atomically
 *              per file, all other bytes unchanged; computed and checked before anything is written). Requires
 *              (checked first) an existing outputDir components/<Vendor>/<Component>/lang whose <Component>.php
 *              already contributes the module's ComponentLanguageFileDirectory, no symbolic link in lang/ and the
 *              start marker in every lang/ilias_<lang>.lang
 * <module> is required. Unknown options (anything starting with "-") fail; module and reference language must match
 * [A-Za-z0-9_]+. Like the installer, a `.lang` file is only read behind its line "<!-- language file start -->"; a
 * file without that line is skipped with a WARNING (a FAILURE with --remove-from-lang). A module without any line in
 * lang/ fails without writing anything (most likely migrated already). Every check runs before anything is
 * written: a failing run writes no `.po`/`.pot` and changes no `.lang` file (except on write errors or a `.lang`
 * file changed during the run, both reported).
 * Example: php convert_module_to_po.php tos en ../../../TermsOfService/lang
 */

// a build tool: never reachable through the web server
if (PHP_SAPI !== 'cli') {
    exit(1);
}

require dirname(__DIR__, 5) . '/vendor/composer/vendor/autoload.php';

use ILIAS\Language\ComponentTranslation\AtomicFileWriter;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use ILIAS\Language\ComponentTranslation\LegacyFuzzyMarker;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\PluralForms;

/**
 * Makes control characters (e.g. the ESC of a terminal escape sequence) visible in everything printed from a `.lang`
 * file or the command line.
 */
$printable = static function (string $text): string {
    return addcslashes($text, "\0..\37\177");
};

/**
 * Read once, so the lines and the hash checked by --remove-from-lang come from the same bytes.
 */
$read_file = static function (string $file): string {
    $content = file_get_contents($file);
    if ($content === false) {
        throw new RuntimeException("Cannot read $file");
    }

    return $content;
};

/**
 * The lines of $content the installer reads (see LanguageFileParsing::cutHeader()).
 *
 * @return array<int, string> line number (0-based) => line, split at "\n" only like file()
 */
$body_lines = static function (string $content): array {
    $lines = explode("\n", $content);
    foreach ($lines as $number => $line) {
        if (trim($line) === '<!-- language file start -->') {
            return array_slice($lines, $number + 1, null, true);
        }
    }

    return [];
};

$has_start_marker = static function (string $content): bool {
    foreach (explode("\n", $content) as $line) {
        if (trim($line) === '<!-- language file start -->') {
            return true;
        }
    }

    return false;
};

/**
 * @param string $content of the `.lang` file $file (only named in warnings)
 * @return list<array{0: string, 1: string, 2: string}> [module, key, raw value] of every entry line
 */
$read_lines = static function (string $file, string $content) use ($body_lines): array {
    $entries = [];
    // a "\r" before the "\n" is trimmed below
    foreach ($body_lines($content) as $number => $line) {
        // trimmed like ILIAS' own .lang reader, or a value would keep trailing whitespace the database never had
        $parts = explode('#:#', trim($line));
        if (count($parts) < 3) {
            continue;
        }
        if (count($parts) > 3) {
            // parsed like LanguageInstallationManager::insertLanguage(): the part after a further "#:#" is dropped
            fwrite(STDERR, sprintf(
                "WARNING: %s:%d contains \"#:#\" in its value - only the part before it is used, like the installer does.\n",
                $file,
                $number + 1
            ));
        }
        $entries[] = [$parts[0], $parts[1], $parts[2]];
    }

    return $entries;
};

/**
 * @return list<array{0: string, 1: string}> [key, raw value] of every line of $module in file order
 */
$module_lines = static function (string $file, string $content, string $module) use ($read_lines): array {
    $lines = [];
    foreach ($read_lines($file, $content) as [$mod, $key, $raw_value]) {
        if ($mod === $module) {
            $lines[] = [$key, $raw_value];
        }
    }

    return $lines;
};

/**
 * @param list<array{0: string, 1: string}> $lines see $module_lines - a later line of the same key wins
 * @return array<string, array{value: string, comment: ?string}>
 */
$entries_of = static function (array $lines): array {
    $entries = [];
    foreach ($lines as [$key, $raw_value]) {
        $comment = null;
        $value = $raw_value;
        $comment_pos = strpos($raw_value, '###');
        if ($comment_pos !== false) {
            $value = substr($raw_value, 0, $comment_pos);
            $comment = trim(substr($raw_value, $comment_pos + 3));
        }
        $entries[$key] = ['value' => $value, 'comment' => $comment];
    }

    return $entries;
};

/**
 * @return array<string, array{value: string, comment: ?string}>
 */
$parse_module = static function (string $file, string $module) use ($read_file, $module_lines, $entries_of): array {
    return $entries_of($module_lines($file, $read_file($file), $module));
};

/**
 * A legacy `.lang` file can define the same identifier twice for one module (uniqueness validation of the FR
 * "PO-Files for improving language handling", 2.4).
 *
 * @param list<array{0: string, 1: string}> $lines see $module_lines
 * @return array<string, bool> identifier occurring more than once => whether all occurrences have the same value and
 *         "###" comment as the installer reads them (surrounding whitespace and a part after a further "#:#" ignored)
 */
$find_duplicate_keys = static function (array $lines): array {
    $raw_values = [];
    foreach ($lines as [$key, $raw_value]) {
        $raw_values[$key][] = $raw_value;
    }
    $duplicates = [];
    foreach ($raw_values as $key => $values) {
        if (count($values) > 1) {
            $duplicates[(string) $key] = count(array_unique($values)) === 1;
        }
    }

    return $duplicates;
};

/**
 * @param array<array-key> $keys
 */
$format_keys = static function (array $keys) use ($printable): string {
    return implode(', ', array_map(
        static fn(int|string $key): string => (string) $key === '' ? '"" (empty key)' : $printable((string) $key),
        $keys
    ));
};

/**
 * $content without the lines of $module - exactly those $read_lines reports, and only behind the line
 * "<!-- language file start -->" (the installer ignores everything before it; only called for files with that line).
 * Every other byte stays: header, comments, line endings, order, a missing final line break.
 *
 * @return array{0: string, 1: int} the new content and the number of removed lines
 */
$without_module_lines = static function (string $content, string $module): array {
    $lines = preg_split('/(?<=\n)/', $content, -1, PREG_SPLIT_NO_EMPTY);
    $in_body = true;
    foreach ($lines as $line) {
        if (trim($line) === '<!-- language file start -->') {
            $in_body = false;
            break;
        }
    }
    $kept = [];
    $removed = 0;
    foreach ($lines as $line) {
        if (!$in_body) {
            $in_body = trim($line) === '<!-- language file start -->';
            $kept[] = $line;
            continue;
        }
        $parts = explode('#:#', trim($line));
        if (count($parts) >= 3 && $parts[0] === $module) {
            $removed++;
            continue;
        }
        $kept[] = $line;
    }
    $new_content = implode('', $kept);
    // the removed line was the last one and had no line break: the new last line loses its own
    if ($removed > 0 && !str_ends_with($content, "\n") && str_ends_with($new_content, "\n")) {
        $new_content = substr($new_content, 0, str_ends_with($new_content, "\r\n") ? -2 : -1);
    }

    return [$new_content, $removed];
};

/**
 * Whether a `###` comment is the dated "not translated yet" marker (-> "#, fuzzy") rather than a note (-> "#."), see
 * LegacyFuzzyMarker (shared with the installation).
 */
$is_fuzzy_marker = LegacyFuzzyMarker::matches(...);

/**
 * The form that serves n = 1 if it serves no other n, else null. Found by evaluating the rule (not its formula
 * text): form 0 in en, de, es, it, cs, pl; form 1 in ar; null where that form also serves 0 (fr, pt), 21, 101 (ru,
 * hr, sl) and in single-form languages.
 */
$singular_form_index = static function (PluralForms $plural_forms): ?int {
    if ($plural_forms->getCount() < 2) {
        return null;
    }
    $index = $plural_forms->formIndexFor(1);
    foreach ([...range(0, 200), 1000, 1001, 1011, 100000, 1000000, 1000001, 2000000] as $n) {
        if ($n !== 1 && $plural_forms->formIndexFor($n) === $index) {
            return null;
        }
    }

    return $index;
};
/**
 * The form of the plural message $key that gets the singular key's value, or null: only if the singular key is
 * translated (not empty, not from the reference language, no "new variable" marker) and the rule has a form for
 * n = 1 alone. Else every form keeps the own value: an untranslated placeholder must not replace a translated form.
 *
 * @param array<string, array{value: string, comment: ?string}> $entries the language's entries of the module
 * @param array{singular?: string, plural_id?: string} $definition
 * @param array<string, true> $filled_keys keys whose value was taken from the reference language
 */
$singular_form_of = static function (
    string $key,
    array $definition,
    array $entries,
    PluralForms $plural_forms,
    array $filled_keys
) use ($singular_form_index, $is_fuzzy_marker): ?int {
    $singular_key = $definition['singular'] ?? null;
    if (
        ($entries[$key]['value'] ?? '') === ''
        || $singular_key === null
        || ($entries[$singular_key]['value'] ?? '') === ''
        || isset($filled_keys[$singular_key])
    ) {
        return null;
    }
    $singular_comment = $entries[$singular_key]['comment'] ?? null;
    if ($singular_comment !== null && $is_fuzzy_marker($singular_comment)) {
        return null;
    }

    return $singular_form_index($plural_forms);
};
/**
 * @param array<string, array{value: string, comment: ?string}> $entries
 * @param array{singular?: string, plural_id?: string} $definition
 * @param array<string, true> $filled_keys
 * @return list<string>
 */
$plural_forms_of = static function (
    string $key,
    array $definition,
    array $entries,
    PluralForms $plural_forms,
    array $filled_keys
) use ($singular_form_of): array {
    $forms = array_fill(0, $plural_forms->getCount(), $entries[$key]['value'] ?? '');
    $singular_form = $singular_form_of($key, $definition, $entries, $plural_forms, $filled_keys);
    if ($singular_form !== null) {
        $forms[$singular_form] = $entries[$definition['singular']]['value'];
    }

    return $forms;
};
/**
 * Whether the own value of the plural message $key was copied into several forms (flagged fuzzy, see the file docblock).
 *
 * @param array<string, array{value: string, comment: ?string}> $entries
 * @param array{singular?: string, plural_id?: string} $definition
 * @param array<string, true> $filled_keys
 */
$copies_own_value = static function (
    string $key,
    array $definition,
    array $entries,
    PluralForms $plural_forms,
    array $filled_keys
) use ($singular_form_of): bool {
    // counted by position, not by value: a singular equal to the own value is no copy
    $own_forms = $plural_forms->getCount()
        - ($singular_form_of($key, $definition, $entries, $plural_forms, $filled_keys) === null ? 0 : 1);

    return ($entries[$key]['value'] ?? '') !== '' && $own_forms > 1;
};

/**
 * @param array<array-key, array{value: string, comment: ?string}> $reference_entries msgid order + fallback extracted comments
 * @param array<string, array{value: string, comment: ?string}>|null $translation_entries null => POT (no msgstr, no fuzzy)
 * @param array<string, string> $existing_headers headers of an already existing target file
 * @param array<string, array{singular?: string, plural_id?: string}> $plural_definitions msgid => definition
 * @param string|null $language_name English name of the language, null => keep an existing "Language-Team"
 * @param array<string, true> $filled_keys keys whose value was taken from the reference language (-> fuzzy)
 * @param bool $is_source_language original texts (see $source_lang_keys): a plural value copied into several forms is not fuzzy
 */
$build_catalog = static function (
    string $module,
    string $lang_key,
    array $reference_entries,
    ?array $translation_entries,
    array $existing_headers,
    ?PluralForms $plural_forms,
    array $plural_definitions,
    ?string $language_name = null,
    array $filled_keys = [],
    bool $is_source_language = false
) use ($is_fuzzy_marker, $plural_forms_of, $copies_own_value): TranslationCatalog {
    $is_template = $translation_entries === null;
    $catalog = new TranslationCatalog();
    foreach ($existing_headers as $name => $value) {
        $catalog->setHeader($name, $value);
    }
    if (!$is_template && $plural_forms !== null) {
        $catalog->setHeader('Plural-Forms', $plural_forms->getHeader());
    }
    $catalog->setHeader('MIME-Version', '1.0');
    $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
    $catalog->setHeader('Content-Transfer-Encoding', '8bit');
    $catalog->setHeader('X-Domain', $module);
    if (!$is_template) {
        $catalog->setHeader('Language', $lang_key);
    }
    if (!$is_template && $language_name !== null) {
        $catalog->setHeader('Language-Team', $language_name);
    }

    foreach ($reference_entries as $key => $ref) {
        // no msgctxt, see the file docblock
        $entry = new TranslationEntry(null, (string) $key);
        $comment = $ref['comment'];

        $plural_definition = $plural_definitions[(string) $key] ?? null;
        if ($plural_definition !== null) {
            $entry->setPlural(
                $plural_definition['plural_id'] ?? $key . '_plural',
                $is_template ? ['', ''] : $plural_forms_of((string) $key, $plural_definition, $translation_entries, $plural_forms, $filled_keys)
            );
        }

        if (!$is_template) {
            $own = $translation_entries[$key] ?? null;
            if ($plural_definition === null) {
                $entry->translate($own['value'] ?? '');
            }
            // deliberately no "original" comment (LocalChangeComments): that only exists in the per-installation overlay
            $own_comment = $own['comment'] ?? null;
            if (isset($filled_keys[(string) $key])) {
                $entry->addFlag('fuzzy');
            }
            if ($own_comment !== null && $is_fuzzy_marker($own_comment)) {
                $entry->addFlag('fuzzy');
            } elseif ($own_comment !== null) {
                $comment = $own_comment;
            }
            // the own value copied into several forms still needs a translation, except in a source language
            if (!$is_source_language && $plural_definition !== null && $copies_own_value((string) $key, $plural_definition, $translation_entries, $plural_forms, $filled_keys)) {
                $entry->addFlag('fuzzy');
            }
        }

        if ($comment !== null && $comment !== '' && !$is_fuzzy_marker($comment)) {
            $entry->addExtractedComment($comment);
        }

        $catalog->add($entry);
    }

    return $catalog;
};

/**
 * Why $output_argument is no place --remove-from-lang may rely on, or null: it must be the existing
 * components/<Vendor>/<Component>/lang whose component class contributes a ComponentLanguageFileDirectory for $module
 * there with the file name pattern $pattern - otherwise the installation would not find the .po files and the module
 * would lose its values.
 *
 * A text check without bootstrap: only `new ComponentLanguageFileDirectory($this, '<module>'[, '<path>'[, '<pattern>']])`
 * with string literals as positional arguments in an assignment to `$contribute[LanguageFileDirectory::class]` is
 * recognised (comments ignored); any other form is refused.
 */
$removal_target_problem = static function (string $repo_root, ?string $output_argument, string $module, string $pattern) use ($printable): ?string {
    if ($output_argument === null) {
        return 'the outputDir argument is required - the existing lang/ directory of the component that contributes the module.';
    }
    $resolved = realpath($output_argument);
    if ($resolved === false || !is_dir($resolved)) {
        return "outputDir '" . $printable($output_argument) . "' does not exist - the component's lang/ directory must exist before the run.";
    }
    $components = realpath($repo_root . '/components');
    if (
        $components === false
        || preg_match('#\A' . preg_quote($components, '#') . '/([^/]+)/([^/]+)/lang\z#', $resolved, $m) !== 1
    ) {
        return "outputDir '" . $printable($output_argument) . "' is not components/<Vendor>/<Component>/lang of this repository.";
    }
    $component_file = "$components/{$m[1]}/{$m[2]}/{$m[2]}.php";
    $source = is_file($component_file) ? file_get_contents($component_file) : false;
    if ($source === false) {
        return "the component class $component_file was not found.";
    }
    $code = '';
    foreach (token_get_all($source) as $token) {
        if (!is_array($token) || !in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
            $code .= is_array($token) ? $token[1] : $token;
        }
    }
    $class = '(?:\\\\?(?:\w+\\\\)*)?';
    $string = static fn(int $quote, int $text): string => "(['\"])(?<t$text>[^'\"]*)\\$quote";
    preg_match_all(
        '/\$contribute\s*\[\s*' . $class . 'LanguageFileDirectory::class\s*\]\s*=[^;]*?new\s+' . $class
        . 'ComponentLanguageFileDirectory\s*\(\s*\$this\s*,\s*' . $string(1, 1)
        . '\s*(?:,\s*' . $string(3, 2) . '\s*)?(?:,\s*' . $string(5, 3) . '\s*)?,?\s*\)/',
        $code,
        $contributions,
        PREG_SET_ORDER
    );
    foreach ($contributions as $contribution) {
        if ($contribution['t1'] !== $module) {
            continue;
        }
        $path_inside = ($contribution['t2'] ?? '') === '' ? 'lang/' : $contribution['t2'];
        $contributed_pattern = ($contribution['t3'] ?? '') === '' ? $module . '_%s' : $contribution['t3'];
        if (trim($path_inside, '/') !== 'lang') {
            return "$component_file contributes module '$module' from '" . $printable($path_inside) . "', not from lang/.";
        }
        if ($contributed_pattern !== $pattern) {
            return "$component_file contributes module '$module' with the file name pattern '" . $printable($contributed_pattern)
                . "', but the run uses '" . $printable($pattern) . "' (see --pattern).";
        }

        return null;
    }

    return "$component_file contributes no ComponentLanguageFileDirectory for module '$module' (expected: "
        . "\$contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(\$this, '$module'); "
        . "register the contribution first).";
};

$existing_headers = static function (string $file): array {
    try {
        return is_file($file) ? TranslationCatalog::fromPoFile($file)->getHeaders() : [];
    } catch (RuntimeException $e) {
        fwrite(STDERR, "WARNING: the existing $file is not readable, its headers are not kept: {$e->getMessage()}\n");
        return [];
    }
};

$write = static function (string $file, string $content): void {
    if (file_put_contents($file, $content) === false) {
        throw new RuntimeException("Cannot write $file");
    }
};

// outputDir defaults to this tool's output/<module> scratch folder; for a module whose component already contributes
// a ComponentLanguageFileDirectory pass that component's lang/ directory, e.g. for "tos":
//   php convert_module_to_po.php tos en ../../../TermsOfService/lang

$arguments = [];
$pattern = null;
$skip_unmigratable_keys = false;
$remove_from_lang = false;
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--pattern=')) {
        $pattern = substr($argument, strlen('--pattern='));
        continue;
    }
    if ($argument === '--skip-unmigratable-keys') {
        $skip_unmigratable_keys = true;
        continue;
    }
    if ($argument === '--remove-from-lang') {
        $remove_from_lang = true;
        continue;
    }
    // a mistyped option must not end up as module name or output directory
    if (str_starts_with($argument, '-')) {
        fwrite(STDERR, "FAILURE: unknown option '" . $printable($argument) . "' (known: --pattern=<pattern>, --skip-unmigratable-keys, --remove-from-lang).\n");
        exit(1);
    }
    $arguments[] = $argument;
}
if (!isset($arguments[0])) {
    fwrite(STDERR, "FAILURE: the module is required. Usage: php convert_module_to_po.php [--pattern=<pattern>]"
        . " [--skip-unmigratable-keys] [--remove-from-lang] <module> [referenceLangKey] [outputDir]\n");
    exit(1);
}
$module = $arguments[0];
$reference_lang_key = $arguments[1] ?? 'en';
// a module name is a lng_data module: it names the output directory and the files
if (preg_match('/\A[A-Za-z0-9_]+\z/', $module) !== 1 || preg_match('/\A[A-Za-z0-9_]+\z/', $reference_lang_key) !== 1) {
    fwrite(STDERR, "FAILURE: module and reference language may only consist of A-Z, a-z, 0-9 and \"_\".\n");
    exit(1);
}
$pattern ??= $module . '_%s';
try {
    MigratedLanguageFilePaths::assertValidShippedFileNamePattern($pattern);
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$repo_root = dirname(__DIR__, 5);

// --remove-from-lang removes the module's only other source: first check that the .po files land where the installation reads them
if ($remove_from_lang) {
    $target_problem = $removal_target_problem($repo_root, $arguments[2] ?? null, $module, $pattern);
    if ($target_problem !== null) {
        fwrite(STDERR, "FAILURE: --remove-from-lang: $target_problem Nothing was written.\n");
        exit(1);
    }
}

$plural_config_file = __DIR__ . '/plurals.json';
$plural_config = ['plural_forms' => [], 'modules' => []];
if (is_file($plural_config_file)) {
    try {
        $plural_config = json_decode((string) file_get_contents($plural_config_file), true, 16, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        fwrite(STDERR, "FAILURE: $plural_config_file is no valid JSON: {$e->getMessage()}\n");
        exit(1);
    }
} else {
    echo "NOTE: no plurals.json next to the tool - no Plural-Forms headers and no plural messages are written.\n";
}
$plural_rules = [];
foreach ($plural_config['plural_forms'] ?? [] as $lang_key => $header) {
    try {
        $plural_rules[(string) $lang_key] = PluralForms::fromHeader((string) $header);
    } catch (InvalidArgumentException $e) {
        fwrite(STDERR, "FAILURE: invalid Plural-Forms of '$lang_key' in $plural_config_file: {$e->getMessage()}\n");
        exit(1);
    }
}
/** @var array<string, array{singular?: string, plural_id?: string}> $plural_definitions */
$plural_definitions = array_map(
    static fn(mixed $definition): array => is_array($definition) ? $definition : [],
    $plural_config['modules'][$module] ?? []
);
$output_dir = isset($arguments[2]) ? rtrim($arguments[2], '/') : (__DIR__ . '/output/' . $module);
$lang_dir = $repo_root . '/lang';

$lang_files = glob($lang_dir . '/ilias_*.lang');
sort($lang_files);
if ($lang_files === []) {
    fwrite(STDERR, "No lang files found under $lang_dir\n");
    exit(1);
}

$per_language = [];
// lang_key => path, hash, number of the module's lines and the content without them of every file read
$read_files = [];
$files_without_marker = [];
$removal_problems = [];
// lang_key => identifier => whether all occurrences are the same, see $find_duplicate_keys
$duplicates = [];
$unsafe_files = [];
foreach ($lang_files as $file) {
    if (preg_match('/ilias_([a-z]+)\.lang\z/', $file, $m) !== 1) {
        fwrite(STDERR, "WARNING: skipping $file - no language key in its name.\n");
        continue;
    }
    // a symbolic link would only fail when written, after other files are changed already
    if (is_link($file) || !is_file($file)) {
        if ($remove_from_lang) {
            $unsafe_files[] = $file;
            continue;
        }
        if (!is_file($file)) {
            fwrite(STDERR, "WARNING: skipping $file - no regular file.\n");
            continue;
        }
    }
    $lang_key = $m[1];
    $content = $read_file($file);
    if (!$has_start_marker($content)) {
        $files_without_marker[] = $file;
        continue;
    }
    $lines = $module_lines($file, $content, $module);
    [$new_content, $removed] = $without_module_lines($content, $module);
    // a safeguard: exactly the lines read are removed
    if ($remove_from_lang && $removed !== count($lines)) {
        $removal_problems[] = "$file: " . count($lines) . " line(s) of the module were read, but $removed would be removed";
    }
    $per_language[$lang_key] = $entries_of($lines);
    $read_files[$lang_key] = [
        'path' => $file,
        'hash' => hash('sha256', $content),
        'lines' => count($lines),
        'new_content' => $new_content,
        'removed' => $removed,
    ];
    $duplicates[$lang_key] = $find_duplicate_keys($lines);
}
if ($unsafe_files !== []) {
    fwrite(STDERR, "FAILURE: --remove-from-lang only changes regular files, these are symbolic links or no files (nothing was written):\n  "
        . implode("\n  ", $unsafe_files) . "\n");
    exit(1);
}
if ($files_without_marker !== [] && $remove_from_lang) {
    fwrite(STDERR, "FAILURE: --remove-from-lang: these files have no line \"<!-- language file start -->\", the installer"
        . " does not read them (nothing was written):\n  " . implode("\n  ", $files_without_marker) . "\n");
    exit(1);
}
foreach ($files_without_marker as $file) {
    fwrite(STDERR, "WARNING: skipping $file - no line \"<!-- language file start -->\", the installer does not read it.\n");
}
if ($removal_problems !== []) {
    fwrite(STDERR, "FAILURE: --remove-from-lang (nothing was written):\n  " . implode("\n  ", $removal_problems) . "\n");
    exit(1);
}

// the .po files are UTF-8 and are read back as such: a value in another encoding (e.g. Latin-1) is no text there
$invalid_encoding = [];
foreach ($per_language as $lang_key => $entries) {
    foreach ($entries as $key => $entry) {
        if (!mb_check_encoding((string) $key . $entry['value'] . ($entry['comment'] ?? ''), 'UTF-8')) {
            $invalid_encoding[$lang_key][] = mb_check_encoding((string) $key, 'UTF-8') ? (string) $key : bin2hex((string) $key) . ' (hex)';
        }
    }
}
if ($invalid_encoding !== []) {
    fwrite(STDERR, "FAILURE: module '$module' has lines that are not valid UTF-8 in the source .lang files (nothing was written):\n");
    foreach ($invalid_encoding as $lang_key => $keys) {
        fwrite(STDERR, "  $lang_key: " . $format_keys($keys) . "\n");
    }
    exit(1);
}

// a module removed with --remove-from-lang before (or a misspelled module name): nothing to convert,
// and the existing .po/.pot - the module's only source now - must stay as they are
if (array_sum(array_column($read_files, 'lines')) === 0) {
    fwrite(STDERR, "FAILURE: module '$module' has no lines in any of the " . count($read_files) . " lang/ilias_*.lang files under $lang_dir"
        . " - most likely it is migrated already (its lines removed with --remove-from-lang), or the module name is misspelled."
        . " Nothing was written; existing .po/.pot files are unchanged.\n");
    exit(1);
}

if (!isset($per_language[$reference_lang_key])) {
    fwrite(STDERR, "Reference language '$reference_lang_key' not found.\n");
    exit(1);
}
$reference_keys = array_map('strval', array_keys($per_language[$reference_lang_key]));

// fail before writing anything rather than silently keeping one of the occurrences, except on request for keys whose
// occurrences are all the same or that are left out anyway
$duplicate_report = [];
$identical_duplicate_report = [];
foreach ($duplicates as $lang_key => $keys) {
    foreach ($keys as $key => $identical) {
        $left_out = (string) $key === '' || str_contains((string) $key, ' [') || !in_array((string) $key, $reference_keys, true);
        if ($skip_unmigratable_keys && ($identical || $left_out)) {
            $identical_duplicate_report[$lang_key][] = $key;
        } else {
            $duplicate_report[$lang_key][] = $key;
        }
    }
}
if ($duplicate_report !== []) {
    fwrite(STDERR, "FAILURE: module '$module' has duplicate identifiers in the source .lang files (nothing was written):\n");
    foreach ($duplicate_report as $lang_key => $duplicates) {
        fwrite(STDERR, "  $lang_key: " . $format_keys($duplicates) . "\n");
    }
    if (!$skip_unmigratable_keys) {
        fwrite(STDERR, "  (--skip-unmigratable-keys keeps one occurrence of a key whose occurrences all have the same value)\n");
    }
    exit(1);
}
if ($identical_duplicate_report !== []) {
    fwrite(STDERR, "WARNING: module '$module' has duplicate identifiers in the source .lang files - all occurrences the same, one is used; or left out anyway, see below (--skip-unmigratable-keys):\n");
    foreach ($identical_duplicate_report as $lang_key => $duplicates) {
        fwrite(STDERR, "  $lang_key: " . $format_keys($duplicates) . "\n");
    }
}

// the catalogs are built from the reference keys: a key only another language has would be dropped silently. The
// empty key (msgid "" is the PO header) and a key containing " [" (the form syntax of PluralFormKey) cannot be migrated.
$unmigratable_keys = [];
foreach ($per_language as $lang_key => $entries) {
    $keys = array_map('strval', array_keys($entries));
    $unmigratable = array_values(array_diff($keys, $reference_keys));
    if (isset($entries[''])) {
        $unmigratable = array_values(array_unique(['', ...$unmigratable]));
    }
    $form_like = array_filter($keys, static fn(string $key): bool => str_contains($key, ' ['));
    $unmigratable = array_values(array_unique([...$unmigratable, ...$form_like]));
    if ($unmigratable !== []) {
        $unmigratable_keys[$lang_key] = $unmigratable;
    }
}
if ($unmigratable_keys !== [] && !$skip_unmigratable_keys) {
    fwrite(STDERR, "FAILURE: module '$module' has keys that the reference language '$reference_lang_key' does not have, the empty key, or a key containing \" [\" (the syntax of a plural form) (nothing was written):\n");
    foreach ($unmigratable_keys as $lang_key => $keys) {
        fwrite(STDERR, "  $lang_key: " . $format_keys($keys) . "\n");
    }
    fwrite(STDERR, "  (--skip-unmigratable-keys leaves these keys out instead)\n");
    exit(1);
}
if ($unmigratable_keys !== []) {
    fwrite(STDERR, "WARNING: module '$module' has keys that the reference language '$reference_lang_key' does not have, the empty key, or a key containing \" [\" (the syntax of a plural form) - left out (--skip-unmigratable-keys):\n");
    foreach ($unmigratable_keys as $lang_key => $keys) {
        fwrite(STDERR, "  $lang_key: " . $format_keys($keys) . "\n");
        foreach ($keys as $key) {
            unset($per_language[$lang_key][$key]);
        }
    }
}

$reference_entries = $per_language[$reference_lang_key];
// languages whose values are original texts, not translations: the reference language and the mandatory languages of
// AddLanguageEntry - a plural value copied into several forms is not flagged fuzzy there
$source_lang_keys = array_values(array_unique([$reference_lang_key, 'de', 'en']));
if ($reference_entries === []) {
    $languages_with_lines = array_keys(array_filter($read_files, static fn(array $read): bool => $read['lines'] > 0));
    fwrite(STDERR, "FAILURE: module '$module' has no (migratable) entries in the reference language '$reference_lang_key',"
        . " but lines in " . implode(', ', $languages_with_lines) . ". Nothing was written.\n");
    exit(1);
}

$own_entry_counts = array_map('count', $per_language);
$filled = [];
foreach ($per_language as $lang_key => $entries) {
    if ($lang_key === $reference_lang_key) {
        continue;
    }
    foreach ($reference_entries as $key => $ref) {
        $key = (string) $key;
        if ($ref['value'] !== '' && ($entries[$key]['value'] ?? '') === '') {
            $per_language[$lang_key][$key] = ['value' => $ref['value'], 'comment' => $entries[$key]['comment'] ?? null];
            $filled[$lang_key][$key] = true;
        }
    }
}

$plural_problems = [];
foreach ($plural_definitions as $key => $definition) {
    if (!isset($reference_entries[$key])) {
        $plural_problems[] = "plural message '$key' is no key of the module";
    }
    if (isset($definition['singular']) && !isset($reference_entries[$definition['singular']])) {
        $plural_problems[] = "singular key '{$definition['singular']}' of '$key' is no key of the module";
    }
}
if ($plural_definitions !== []) {
    foreach (array_keys($per_language) as $lang_key) {
        if (!isset($plural_rules[$lang_key])) {
            $plural_problems[] = "no Plural-Forms for language '$lang_key'";
        }
    }
}
if ($plural_problems !== []) {
    fwrite(STDERR, "FAILURE: plurals.json does not fit module '$module' (nothing was written):\n  " . implode("\n  ", array_map($printable, $plural_problems)) . "\n");
    exit(1);
}

$language_names = [];
$english_lang_file = $repo_root . '/lang/ilias_en.lang';
if (is_file($english_lang_file)) {
    foreach ($parse_module($english_lang_file, 'meta') as $key => $entry) {
        $name = trim($entry['value']);
        if (str_starts_with((string) $key, 'meta_l_') && $name !== '') {
            $language_names[substr((string) $key, strlen('meta_l_'))] = $name;
        }
    }
}
// once "meta" is migrated, its shipped English .po is the source (default file name in a component's lang/ directory)
if ($language_names === []) {
    foreach (glob($repo_root . '/components/*/*/lang/meta_en.po') ?: [] as $meta_po) {
        try {
            foreach (TranslationCatalog::fromPoFile($meta_po)->getEntries() as $entry) {
                $name = trim($entry->getTranslation());
                if (in_array($entry->getContext(), [null, 'meta'], true) && str_starts_with($entry->getId(), 'meta_l_') && $name !== '') {
                    $language_names[substr($entry->getId(), strlen('meta_l_'))] = $name;
                }
            }
        } catch (RuntimeException $e) {
            fwrite(STDERR, "WARNING: $meta_po cannot be read for the language names: {$e->getMessage()}\n");
        }
    }
}
foreach (array_keys($per_language) as $lang_key) {
    if (!isset($language_names[$lang_key])) {
        echo "NOTE: no meta_l_$lang_key in lang/ilias_en.lang (or a shipped meta_en.po) - the Language-Team header of '$lang_key' is left as it is.\n";
    }
}

// every file is built and read back in memory first: a failing run writes nothing
$pot_path = $output_dir . '/' . MigratedLanguageFilePaths::templateFileName($pattern, $module);
$contents = [];
$report = [];
$pot_headers = $existing_headers($pot_path);
try {
    $contents[$pot_path] = $build_catalog($module, '', $reference_entries, null, $pot_headers, null, $plural_definitions)->toPoString();
} catch (RuntimeException $e) {
    fwrite(STDERR, "FAILURE: the template of module '$module' cannot be written: {$e->getMessage()} Nothing was written.\n");
    exit(1);
}
foreach ($per_language as $lang_key => $entries) {
    $po_path = $output_dir . '/' . sprintf($pattern, $lang_key) . '.po';
    $po_mismatches = [];
    $po_headers = $existing_headers($po_path);
    try {
        $contents[$po_path] = $build_catalog(
            $module,
            $lang_key,
            $reference_entries,
            $entries,
            $po_headers,
            $plural_rules[$lang_key] ?? null,
            $plural_definitions,
            $language_names[$lang_key] ?? null,
            $filled[$lang_key] ?? [],
            in_array($lang_key, $source_lang_keys, true)
        )->toPoString();
        $parsed = TranslationCatalog::fromPoString($contents[$po_path]);
    } catch (RuntimeException $e) {
        $parsed = null;
        $po_mismatches[] = '(not readable back) ' . $e->getMessage();
    }

    // self-check: every value (also one taken from the reference language, flagged fuzzy) must match and nothing else may be in it
    $expected_keys = array_unique(array_merge(
        array_map('strval', array_keys($reference_entries)),
        array_map('strval', array_keys($entries))
    ));
    foreach ($parsed !== null ? $expected_keys : [] as $key) {
        $expected = $entries[$key]['value'] ?? '';
        $parsed_entry = $parsed->find(null, $key);
        // find(null, ...) also finds msgctxt "" (same library id) - only an entry without msgctxt counts
        if ($parsed_entry?->getContext() !== null) {
            $po_mismatches[] = $key;
            continue;
        }
        if (isset($filled[$lang_key][$key]) && $parsed_entry?->hasFlag('fuzzy') !== true) {
            $po_mismatches[] = $key . ' (not fuzzy)';
            continue;
        }
        $plural_definition = $plural_definitions[$key] ?? null;
        if ($plural_definition === null) {
            if ($parsed_entry?->isPlural() !== false || $parsed_entry->getTranslation() !== $expected) {
                $po_mismatches[] = $key;
            }
            continue;
        }
        $rule = $plural_rules[$lang_key];
        $expected_forms = $plural_forms_of($key, $plural_definition, $entries, $rule, $filled[$lang_key] ?? []);
        $parsed_forms = array_pad(array_slice($parsed_entry?->getPluralTranslations() ?? [], 0, $rule->getCount()), $rule->getCount(), '');
        if (
            $parsed_entry?->getPluralId() !== ($plural_definition['plural_id'] ?? $key . '_plural')
            || count($expected_forms) !== $rule->getCount()
            || $parsed_forms !== $expected_forms
            || $rule->defaultValueOf($parsed_forms) !== $expected
        ) {
            $po_mismatches[] = $key . ' (plural)';
        }
    }
    foreach ($parsed?->getEntries() ?? [] as $parsed_entry) {
        if ($parsed_entry->getContext() !== null || !in_array($parsed_entry->getId(), $expected_keys, true)) {
            $po_mismatches[] = '(unexpected) ' . $parsed_entry->getId();
        }
    }

    $fuzzy_count = 0;
    foreach ($entries as $e) {
        if ($e['comment'] !== null && $is_fuzzy_marker($e['comment'])) {
            $fuzzy_count++;
        }
    }

    $report[] = [
        'lang' => $lang_key,
        'entries' => $own_entry_counts[$lang_key],
        'fuzzy' => $fuzzy_count,
        'filled' => count($filled[$lang_key] ?? []),
        'po_ok' => $po_mismatches === [],
        'po_mismatches' => $po_mismatches,
    ];
}

// entries: the language's own lines of the module; fuzzy: of those, marked "new variable";
// filled: reference keys the language lacks or has empty, written with the reference value as fuzzy
printf("%-6s %8s %8s %8s %8s\n", 'lang', 'entries', 'fuzzy', 'filled', 'po_ok');
$all_ok = true;
foreach ($report as $row) {
    printf("%-6s %8d %8d %8d %8s\n", $row['lang'], $row['entries'], $row['fuzzy'], $row['filled'], $row['po_ok'] ? 'yes' : 'NO');
    if (!$row['po_ok']) {
        $all_ok = false;
        fwrite(STDERR, "  po_mismatches: " . $printable(implode(', ', $row['po_mismatches'])) . "\n");
    }
}

echo "\n";
if (!$all_ok) {
    fwrite(STDERR, "FAILURE: at least one language did not round-trip correctly, see stderr above. Nothing was written.\n");
    exit(1);
}

if (!is_dir($output_dir) && !@mkdir($output_dir, 0775, true) && !is_dir($output_dir)) {
    fwrite(STDERR, "FAILURE: cannot create $output_dir. Nothing was written.\n");
    exit(1);
}
$written = [];
foreach ($contents as $path => $content) {
    try {
        $write($path, $content);
    } catch (RuntimeException $e) {
        fwrite(STDERR, "FAILURE: {$e->getMessage()} - written already: " . ($written === [] ? 'nothing' : implode(', ', $written))
            . ". No .lang file was changed.\n");
        exit(1);
    }
    $written[] = basename($path);
}
echo "OK: all " . count($report) . " languages round-trip identically through PO ("
    . array_sum(array_column($report, 'filled')) . " entries filled from '$reference_lang_key', flagged fuzzy).\n";

if (!$remove_from_lang) {
    exit(0);
}

// --remove-from-lang: the module's .po files become its only source; the new contents were computed while reading
$new_lang_contents = [];
$changed_files = [];
foreach ($read_files as $lang_key => $read) {
    $content = file_get_contents($read['path']);
    if ($content === false || hash('sha256', $content) !== $read['hash']) {
        $changed_files[] = $read['path'];
        continue;
    }
    if ($read['removed'] > 0) {
        $new_lang_contents[$lang_key] = [$read['new_content'], $read['removed']];
    }
}
if ($changed_files !== []) {
    fwrite(STDERR, "FAILURE: --remove-from-lang: no .lang file was changed (the .po/.pot files are written), these were"
        . " changed during the run or cannot be read again:\n  " . implode("\n  ", $changed_files) . "\n");
    exit(1);
}

echo "\n";
$removed_total = 0;
foreach ($new_lang_contents as $lang_key => [$new_content, $removed]) {
    $path = $read_files[$lang_key]['path'];
    try {
        AtomicFileWriter::write($path, $new_content, $lang_dir);
    } catch (RuntimeException $e) {
        fwrite(STDERR, "FAILURE: --remove-from-lang: {$e->getMessage()} - the files listed above are changed already, the others not.\n");
        exit(1);
    }
    $removed_total += $removed;
    printf("removed %5d line(s) of '%s' from %s\n", $removed, $module, 'lang/' . basename($path));
}
echo "OK: removed $removed_total line(s) of module '$module' from " . count($new_lang_contents) . " .lang file(s).\n";

exit(0);
