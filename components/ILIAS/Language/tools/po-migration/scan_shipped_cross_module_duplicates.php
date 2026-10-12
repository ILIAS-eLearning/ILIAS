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
 * Lists every identifier of the shipped `lang/ilias_*.lang` files that occurs in more than one
 * module (e.g. `bibl#:#sorting_3` and `irss#:#sorting_3`), as a Markdown report on stdout:
 * modules, kind of difference, languages whose values differ, the English values and the number of
 * literal callers (`git grep` for `txt('<identifier>')`/`txt("<identifier>")`).
 *
 * txt($topic) knows no module: the module loaded last wins, for the legacy and the migrated (.po) path alike, see
 * README.md. Identifiers are compared exactly; values without the `###` comment, trimmed; empty values are ignored.
 *
 * Usage (ILIAS root): php components/ILIAS/Language/tools/po-migration/scan_shipped_cross_module_duplicates.php
 *
 * Declares no named classes or functions, to stay out of Composer's classmap (as convert_module_to_po.php). Read-only.
 */

$ilias_root = dirname(__DIR__, 5);

// language => module => identifier => value
$values = [];
$files = glob($ilias_root . '/lang/ilias_*.lang') ?: [];
sort($files);
foreach ($files as $file) {
    $language = substr(basename($file), 6, -5);
    $started = false;
    foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        if (!$started) {
            $started = trim($line) === '<!-- language file start -->';
            continue;
        }
        $parts = explode('#:#', trim($line));
        if (count($parts) < 3) {
            continue;
        }
        $values[$language][$parts[0]][$parts[1]] = trim(explode('###', $parts[2])[0]);
    }
}

// identifier => module => true
$modules_of = [];
foreach ($values as $modules) {
    foreach ($modules as $module => $identifiers) {
        foreach (array_keys($identifiers) as $identifier) {
            $modules_of[(string) $identifier][$module] = true;
        }
    }
}
$duplicates = array_filter($modules_of, static fn(array $modules): bool => count($modules) > 1);
ksort($duplicates, SORT_STRING);

// modules shipped as .po (the .pot is named after the module with the default file name pattern)
$migrated = array_map(
    static fn(string $pot): string => basename($pot, '.pot'),
    glob($ilias_root . '/components/*/*/lang/*.pot') ?: []
);

// same text apart from case, whitespace and punctuation ("Activity-ID" = "Activity ID")
$normalise = static fn(string $value): string => mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', $value));

$callers_of = static function (string $identifier) use ($ilias_root): int {
    $callers = [];
    foreach (["txt('" . $identifier . "'", 'txt("' . $identifier . '"'] as $pattern) {
        $output = [];
        exec(sprintf(
            'git -C %s grep -n -F %s -- %s 2>/dev/null',
            escapeshellarg($ilias_root),
            escapeshellarg($pattern),
            escapeshellarg('components')
        ), $output);
        array_push($callers, ...$output);
    }

    return count(array_unique($callers));
};

$kinds = [
    'meaning' => 'Bedeutung (en)',
    'cosmetic' => 'kosmetisch (en)',
    'translation' => 'nur Übersetzung',
    'same' => 'gleich',
];
$rows = [];
$count_per_kind = array_fill_keys(array_keys($kinds), 0);
$count_per_combination = [];
foreach ($duplicates as $identifier => $modules) {
    $modules = array_keys($modules);
    sort($modules);
    $differing = [];
    foreach ($values as $language => $per_module) {
        $texts = [];
        foreach ($modules as $module) {
            $text = $per_module[$module][$identifier] ?? '';
            if ($text !== '') {
                $texts[] = $text;
            }
        }
        if (count(array_unique($texts)) > 1) {
            $differing[] = $language;
        }
    }
    $english = [];
    foreach ($modules as $module) {
        $text = $values['en'][$module][$identifier] ?? '';
        if ($text !== '') {
            $english[$module] = $text;
        }
    }
    $kind = match (true) {
        $differing === [] => 'same',
        !in_array('en', $differing, true) => 'translation',
        count(array_unique(array_map($normalise, $english))) <= 1 => 'cosmetic',
        default => 'meaning',
    };
    $count_per_kind[$kind]++;
    $combination = implode(' + ', $modules);
    $count_per_combination[$combination] = ($count_per_combination[$combination] ?? 0) + 1;
    $rows[] = [(string) $identifier, $modules, $kind, $differing, $english];
}
arsort($count_per_combination);

$code = static fn(string $text): string => '`' . $text . '`';
$cell = static fn(string $text): string => str_replace(['|', "\n"], ['\|', ' '], $text);

echo sprintf("%d Identifier in mehr als einem Modul (von %d)\n\n", count($duplicates), count($modules_of));
echo "| Art | Anzahl |\n|---|---|\n";
foreach ($kinds as $kind => $label) {
    echo sprintf("| %s | %d |\n", $label, $count_per_kind[$kind]);
}
echo "\nHäufigste Modulkombinationen (ab 3 Identifiern)\n\n| Module | Anzahl |\n|---|---|\n";
foreach ($count_per_combination as $combination => $count) {
    if ($count >= 3) {
        echo sprintf("| %s | %d |\n", implode(' + ', array_map($code, explode(' + ', $combination))), $count);
    }
}
echo "\n| Identifier | Module | Art | Abweichende Sprachen | en-Werte | Aufrufer (`txt('…')`) |\n|---|---|---|---|---|---|\n";
foreach ($rows as [$identifier, $modules, $kind, $differing, $english]) {
    $module_cells = array_map(
        static fn(string $module): string => $code($module) . (in_array($module, $migrated, true) ? ' (po)' : ''),
        $modules
    );
    $english_cells = [];
    foreach ($english as $module => $text) {
        $english_cells[] = $code($module) . ': ' . $cell($text);
    }
    $languages = match (true) {
        $differing === [] => '–',
        count($differing) >= 25 => 'fast alle (' . count($differing) . ')',
        default => implode(', ', $differing),
    };
    echo sprintf(
        "| %s | %s | %s | %s | %s | %d |\n",
        $code($identifier),
        implode(', ', $module_cells),
        $kinds[$kind],
        $languages,
        implode('<br>', $english_cells),
        $callers_of($identifier)
    );
}
