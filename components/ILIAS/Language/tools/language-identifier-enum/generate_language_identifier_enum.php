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
 * Generates the enum of the language identifiers of one module (an implementation of
 * ILIAS\Language\LanguageIdentifier, see \ilLanguage::translate()) from the module's `.pot`, or checks an
 * existing one against it (--check, for CI). See README.md next to this file.
 *
 * Usage: php generate_language_identifier_enum.php [--check | --stdout] [--module=<module>]
 *          --namespace=<Namespace> <pot file> <enum file>
 *   <pot file>   the `.pot` of the module, inside this ILIAS directory
 *   <enum file>  the enum, "<EnumName>.php", inside this ILIAS directory - written atomically (its
 *                directory must exist), with --stdout only printed, with --check only read
 *   --module     the module (default: the "X-Domain" header of the `.pot`, else its file name)
 *   --namespace  the namespace of the enum, e.g. "ILIAS\Poll"
 *   --check      compares the enum with the `.pot`: missing or surplus cases, wrong values, wrong
 *                module - exit code 1 if it deviates. For an enum without cases, module() must
 *                return a single-quoted string literal (it cannot be called without a case).
 * Exit codes: 0 success (no deviation), 1 deviation (--check), 2 invalid arguments or input.
 *
 * Case names: the identifier in upper case, every run of characters other than A-Z, 0-9 and "_"
 * replaced by one "_", a leading digit and the names PHP does not allow for a case (CLASS,
 * __HALT_COMPILER) prefixed with "KEY_". Identifiers
 * that end up with the same name (e.g. "style" and "Style") are numbered deterministically: the
 * identifier in lower case keeps the name, the others (in byte order) get "_2", "_3", ... (skipping
 * every name taken otherwise) - reported on stderr. One case per msgid, plural messages included.
 *
 * Deliberately declares no named classes or functions: this file lives inside the classmap-scanned
 * components/ tree (see convert_module_to_po.php).
 */

// a development tool: never reachable through the web server
if (PHP_SAPI !== 'cli') {
    exit(1);
}

$repo_root = dirname(__DIR__, 5);
require $repo_root . '/vendor/composer/vendor/autoload.php';

use ILIAS\Language\ComponentTranslation\AtomicFileWriter;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\LanguageIdentifier;

const LANGUAGE_IDENTIFIER_ENUM_TOOL = 'components/ILIAS/Language/tools/language-identifier-enum/generate_language_identifier_enum.php';

$fail = static function (string $message): never {
    fwrite(STDERR, 'ERROR: ' . $message . PHP_EOL);
    exit(2);
};

/**
 * Makes control characters visible in everything printed that comes from a file or the command line.
 */
$printable = static fn(string $text): string => (string) preg_replace_callback(
    '/[\x00-\x1F\x7F]/',
    static fn(array $match): string => sprintf('\x%02X', ord($match[0])),
    $text
);

$identifier_format = '/^[A-Za-z_][A-Za-z0-9_]*$/';

/**
 * Case names PHP rejects (all other keywords are allowed as names of class constants and cases).
 */
$reserved_case_names = ['CLASS', '__HALT_COMPILER'];

$comment_safe = static fn(string $text): string => str_replace('*/', '*\\/', $text);

/**
 * The absolute path of $path if it lies inside $root, `null` otherwise. Symbolic links of the deepest existing
 * ancestor are resolved; the non-existing rest must not contain "." or "..".
 */
$inside_repository = static function (string $path, string $root): ?string {
    if ($path === '' || str_contains($path, "\0")) {
        return null;
    }
    if (!str_starts_with($path, '/')) {
        $path = getcwd() . '/' . $path;
    }
    $missing = [];
    $existing = rtrim($path, '/');
    while ($existing !== '' && !file_exists($existing)) {
        $missing[] = basename($existing);
        $existing = dirname($existing);
    }
    $resolved = realpath($existing === '' ? '/' : $existing);
    $resolved_root = realpath($root);
    if ($resolved === false || $resolved_root === false) {
        return null;
    }
    foreach ($missing as $segment) {
        if ($segment === '' || $segment === '.' || $segment === '..') {
            return null;
        }
    }
    $absolute = $resolved . ($missing === [] ? '' : '/' . implode('/', array_reverse($missing)));

    return str_starts_with($absolute . '/', rtrim($resolved_root, '/') . '/') && $absolute !== $resolved_root
        ? $absolute
        : null;
};

/**
 * @param list<string> $identifiers
 * @return array{names: array<string, string>, collisions: list<string>}
 */
$case_names = static function (array $identifiers) use ($reserved_case_names): array {
    $bases = [];
    foreach ($identifiers as $identifier) {
        $base = (string) preg_replace('/[^A-Z0-9_]+/', '_', strtoupper($identifier));
        if (preg_match('/^[0-9]/', $base) === 1 || in_array($base, $reserved_case_names, true)) {
            $base = 'KEY_' . $base;
        }
        $bases[$identifier] = $base;
    }
    $groups = [];
    foreach ($bases as $identifier => $base) {
        $groups[$base][] = (string) $identifier;
    }
    $taken = array_fill_keys(array_keys($groups), true);
    $names = [];
    $collisions = [];
    ksort($groups, SORT_STRING);
    foreach ($groups as $base => $group) {
        usort($group, static fn(string $a, string $b): int => [$a !== strtolower($a), $a] <=> [$b !== strtolower($b), $b]);
        $names[$group[0]] = (string) $base;
        $described = [sprintf('"%s" -> %s', $group[0], $base)];
        $suffix = 2;
        foreach (array_slice($group, 1) as $identifier) {
            while (isset($taken[$base . '_' . $suffix])) {
                $suffix++;
            }
            $name = $base . '_' . $suffix;
            $taken[$name] = true;
            $names[$identifier] = $name;
            $described[] = sprintf('"%s" -> %s', $identifier, $name);
        }
        if (count($group) > 1) {
            $collisions[] = sprintf('Case name %s: %s', $base, implode(', ', $described));
        }
    }

    $ordered = [];
    foreach ($identifiers as $identifier) {
        $ordered[$identifier] = $names[$identifier];
    }

    return ['names' => $ordered, 'collisions' => $collisions];
};

/**
 * @param array<string, string> $names identifier => case name
 */
$render = static function (
    string $namespace,
    string $enum_name,
    string $module,
    string $pot_path,
    array $names
) use ($comment_safe): string {
    $code = <<<'PHP'
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

        PHP;
    $code .= "\nnamespace " . $namespace . ";\n\n";
    if ($namespace !== 'ILIAS\\Language') {
        $code .= "use ILIAS\\Language\\LanguageIdentifier;\n\n";
    }
    $code .= "/**\n"
        . " * The language identifiers of the module \"" . $comment_safe($module) . "\", see LanguageIdentifier.\n"
        . " *\n"
        . " * Generated from " . $comment_safe($pot_path) . "\n"
        . " * by " . LANGUAGE_IDENTIFIER_ENUM_TOOL . " - do not edit;\n"
        . " * generate it again after every change of the .pot (CI: --check).\n"
        . " */\n"
        . "enum " . $enum_name . ": string implements LanguageIdentifier\n{\n";
    foreach ($names as $identifier => $name) {
        $code .= '    case ' . $name . ' = ' . var_export((string) $identifier, true) . ";\n";
    }
    if ($names !== []) {
        $code .= "\n";
    }
    $code .= "    public function module(): string\n    {\n        return " . var_export($module, true) . ";\n    }\n}\n";

    return $code;
};

/**
 * module() cannot be called on an enum without cases, so its body is read; it must be `return '<literal>';` only.
 */
$literal_module_of = static function (ReflectionEnum $reflection): ?string {
    if (!$reflection->hasMethod('module')) {
        return null;
    }
    $method = $reflection->getMethod('module');
    $file = $method->getFileName();
    $lines = $file === false ? false : file($file);
    if ($lines === false || $method->getStartLine() === false || $method->getEndLine() === false) {
        return null;
    }
    $source = implode('', array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));
    $tokens = array_values(array_filter(
        token_get_all('<?php ' . $source),
        static fn(mixed $token): bool => !is_array($token) || !in_array($token[0], [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)
    ));
    $start = array_search('{', $tokens, true);
    $body = $start === false ? [] : array_slice($tokens, $start + 1);
    if (
        count($body) !== 4
        || !is_array($body[0]) || $body[0][0] !== T_RETURN
        || !is_array($body[1]) || $body[1][0] !== T_CONSTANT_ENCAPSED_STRING || !str_starts_with($body[1][1], "'")
        || $body[2] !== ';' || $body[3] !== '}'
    ) {
        return null;
    }

    return strtr(substr($body[1][1], 1, -1), ['\\\\' => '\\', "\\'" => "'"]);
};

// --- arguments ---

$options = ['check' => false, 'stdout' => false, 'module' => null, 'namespace' => null];
$arguments = [];
foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--check' || $argument === '--stdout') {
        $options[substr($argument, 2)] = true;
    } elseif (str_starts_with($argument, '--module=')) {
        $options['module'] = substr($argument, strlen('--module='));
    } elseif (str_starts_with($argument, '--namespace=')) {
        $options['namespace'] = substr($argument, strlen('--namespace='));
    } elseif (str_starts_with($argument, '-')) {
        $fail(sprintf('Unknown option "%s".', $printable($argument)));
    } else {
        $arguments[] = $argument;
    }
}
if (count($arguments) !== 2 || $options['namespace'] === null) {
    $fail('Usage: php generate_language_identifier_enum.php [--check | --stdout] [--module=<module>] --namespace=<Namespace> <pot file> <enum file>');
}
if ($options['check'] && $options['stdout']) {
    $fail('--check and --stdout exclude each other.');
}
[$pot_argument, $enum_argument] = $arguments;

$namespace = (string) $options['namespace'];
if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $namespace) !== 1) {
    $fail(sprintf('Invalid namespace "%s" (e.g. "ILIAS\\Poll", without leading backslash).', $printable($namespace)));
}

$pot_file = $inside_repository($pot_argument, $repo_root);
if ($pot_file === null || !is_file($pot_file) || !str_ends_with($pot_file, '.pot')) {
    $fail(sprintf('"%s" is no .pot file inside %s.', $printable($pot_argument), $printable($repo_root)));
}
$enum_file = $inside_repository($enum_argument, $repo_root);
if ($enum_file === null || !str_ends_with($enum_file, '.php')) {
    $fail(sprintf('"%s" is no .php file path inside %s.', $printable($enum_argument), $printable($repo_root)));
}
$enum_name = basename($enum_file, '.php');
if (preg_match($identifier_format, $enum_name) !== 1) {
    $fail(sprintf('"%s" is no valid enum name (the file name without ".php").', $printable($enum_name)));
}
if ($options['check'] && !is_file($enum_file)) {
    $fail(sprintf('The enum "%s" does not exist.', $printable($enum_argument)));
}
if (!$options['check'] && !$options['stdout'] && !is_dir(dirname($enum_file))) {
    $fail(sprintf('The directory of "%s" does not exist.', $printable($enum_argument)));
}

try {
    $catalog = TranslationCatalog::fromPoFile($pot_file);
} catch (RuntimeException $e) {
    $fail($printable($e->getMessage()));
}
$module = $options['module'] ?? $catalog->getHeader('X-Domain') ?? basename($pot_file, '.pot');
if (!MigratedLanguageFilePaths::isValidModule($module)) {
    $fail(sprintf('Invalid module "%s".', $printable($module)));
}

$identifiers = [];
foreach ($catalog->getEntries() as $entry) {
    $context = $entry->getContext();
    if ($context !== null && $context !== $module) {
        $fail(sprintf('msgid "%s" has the msgctxt "%s" - only none or the module is supported.', $printable($entry->getId()), $printable($context)));
    }
    $identifier = $entry->getId();
    if ($identifier === '' || preg_match('/[\x00-\x1F\x7F]/', $identifier) === 1) {
        $fail(sprintf('msgid "%s" cannot be a case (empty or with a control character).', $printable($identifier)));
    }
    $identifiers[$identifier] = $identifier;
}
['names' => $names, 'collisions' => $collisions] = $case_names(array_values($identifiers));
foreach ($collisions as $collision) {
    fwrite(STDERR, 'NOTE: ' . $printable($collision) . PHP_EOL);
}

$pot_path = substr($pot_file, strlen(rtrim((string) realpath($repo_root), '/')) + 1);
// the path goes into a doc comment of the generated code: nothing but plain path characters
if (preg_match('/\A[A-Za-z0-9_.\/-]+\z/', $pot_path) !== 1) {
    $fail(sprintf('The path "%s" of the .pot may only contain A-Z, a-z, 0-9, "_", ".", "/" and "-".', $printable($pot_path)));
}
$fqcn = $namespace . '\\' . $enum_name;

// --- check ---

if ($options['check']) {
    if (!interface_exists(LanguageIdentifier::class)) {
        require_once dirname(__DIR__, 2) . '/src/LanguageIdentifier.php';
    }
    $declared_before = get_declared_classes();
    (static function (string $file): void {
        require $file;
    })($enum_file);
    $declared = array_map('strtolower', array_diff(get_declared_classes(), $declared_before));
    if (!in_array(strtolower($fqcn), $declared, true) || !enum_exists($fqcn, false)) {
        $fail(sprintf('"%s" does not declare the enum %s.', $printable($enum_argument), $printable($fqcn)));
    }
    $reflection = new ReflectionEnum($fqcn);
    $deviations = [];
    if (!$reflection->implementsInterface(LanguageIdentifier::class)) {
        $deviations[] = sprintf('%s does not implement %s.', $fqcn, LanguageIdentifier::class);
    }
    if ((string) $reflection->getBackingType() !== 'string') {
        $deviations[] = sprintf('%s is not backed by string.', $fqcn);
    }
    $actual = [];
    foreach ($reflection->getCases() as $case) {
        $actual[$case->getName()] = $case instanceof ReflectionEnumBackedCase ? $case->getBackingValue() : null;
    }
    $expected = array_flip($names);
    foreach ($expected as $name => $identifier) {
        if (!array_key_exists($name, $actual)) {
            $deviations[] = sprintf('Missing case %s = %s.', $name, var_export((string) $identifier, true));
        } elseif ($actual[$name] !== (string) $identifier) {
            $deviations[] = sprintf('Case %s has the value %s instead of %s.', $name, var_export($actual[$name], true), var_export((string) $identifier, true));
        }
    }
    foreach (array_diff_key($actual, $expected) as $name => $value) {
        $deviations[] = sprintf('Surplus case %s = %s (not in the .pot).', $name, var_export($value, true));
    }
    $first_case = $fqcn::cases()[0] ?? null;
    $actual_module = $first_case instanceof LanguageIdentifier
        ? $first_case->module()
        : $literal_module_of($reflection);
    if ($actual_module === null) {
        $deviations[] = sprintf('module() of %s (without cases) does not just return a single-quoted string literal - cannot check it.', $fqcn);
    } elseif ($actual_module !== $module) {
        $deviations[] = sprintf('module() returns %s instead of %s.', var_export($actual_module, true), var_export($module, true));
    }
    if ($deviations === []) {
        fwrite(STDOUT, $printable(sprintf('OK: %s matches %s (%d cases, module "%s").', $fqcn, $pot_path, count($names), $module)) . PHP_EOL);
        exit(0);
    }
    foreach ($deviations as $deviation) {
        fwrite(STDERR, 'DEVIATION: ' . $printable($deviation) . PHP_EOL);
    }
    fwrite(STDERR, $printable(sprintf('%s deviates from %s - generate it again with %s.', $fqcn, $pot_path, LANGUAGE_IDENTIFIER_ENUM_TOOL)) . PHP_EOL);
    exit(1);
}

// --- generate ---

$code = $render($namespace, $enum_name, $module, $pot_path, $names);
if ($options['stdout']) {
    fwrite(STDOUT, $code);
    exit(0);
}
if (is_file($enum_file) && file_get_contents($enum_file) === $code) {
    fwrite(STDOUT, $printable(sprintf('Unchanged: %s (%d cases).', $enum_file, count($names))) . PHP_EOL);
    exit(0);
}
try {
    AtomicFileWriter::write($enum_file, $code, $repo_root);
} catch (RuntimeException $e) {
    $fail($printable($e->getMessage()));
}
fwrite(STDOUT, $printable(sprintf('Written: %s (%d cases, module "%s").', $enum_file, count($names), $module)) . PHP_EOL);
