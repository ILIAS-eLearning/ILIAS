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
 * Lists every identifier of the shipped `lang/ilias_*.lang` files that occurs in one module in
 * spellings that differ only in upper/lower case (e.g. `style#:#Style` and `style#:#style`), as a
 * Markdown report on stdout: file:line, module, spellings, and the callers of
 * each spelling (`git grep` for `txt('<identifier>')`/`txt("<identifier>")`).
 *
 * Such a pair collides in lng_data, whose primary key (module, identifier, lang_key) compares case-insensitively
 * (utf8mb3_general_ci): the database holds one row, PHP two keys, see README.md. The collation also folds accents,
 * so identifiers are compared lower-cased and transliterated to ASCII (iconv, if available) - an approximation.
 *
 * Usage (ILIAS root): php components/ILIAS/Language/tools/po-migration/scan_shipped_case_duplicates.php
 *
 * Declares no named classes or functions, to stay out of Composer's classmap (as convert_module_to_po.php). Read-only.
 */

$ilias_root = dirname(__DIR__, 5);

// lower case and, where iconv can, without accents - roughly how utf8mb3_general_ci compares
$fold = static function (string $text): string {
    $lower = mb_strtolower($text);
    $ascii = function_exists('iconv') ? @iconv('UTF-8', 'ASCII//TRANSLIT', $lower) : false;

    return is_string($ascii) && $ascii !== '' ? strtolower($ascii) : $lower;
};

// folded module#:#identifier => spelling => file => line; only spellings within the same file collide
$occurrences = [];
$files = glob($ilias_root . '/lang/ilias_*.lang') ?: [];
sort($files);
foreach ($files as $file) {
    $started = false;
    foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $number => $line) {
        if (!$started) {
            $started = trim($line) === '<!-- language file start -->';
            continue;
        }
        $parts = explode('#:#', trim($line));
        if (count($parts) < 3) {
            continue;
        }
        $folded = $fold($parts[0]) . '#:#' . $fold($parts[1]);
        $occurrences[$folded][$parts[1]][basename($file)] ??= $number + 1;
    }
}
$duplicates = [];
foreach ($occurrences as $folded => $spellings) {
    $files_per_spelling = array_map('array_keys', $spellings);
    $colliding_files = count($spellings) > 1 ? array_values(array_intersect(...array_values($files_per_spelling))) : [];
    if ($colliding_files !== []) {
        $duplicates[$folded] = [$spellings, $colliding_files];
    }
}
ksort($duplicates);

$callers_of = static function (string $identifier) use ($ilias_root): array {
    $callers = [];
    foreach (["txt('" . $identifier . "'", 'txt("' . $identifier . '"'] as $pattern) {
        $output = [];
        exec(sprintf(
            'git -C %s grep -n -F %s -- %s 2>/dev/null',
            escapeshellarg($ilias_root),
            escapeshellarg($pattern),
            escapeshellarg('components')
        ), $output);
        foreach ($output as $hit) {
            $callers[] = implode(':', array_slice(explode(':', $hit), 0, 2));
        }
    }

    return array_values(array_unique($callers));
};

echo sprintf("%d Identifier mit Varianten, die sich in derselben Sprachdatei nur in Groß-/Kleinschreibung unterscheiden\n\n", count($duplicates));
echo "| Modul | Varianten | Betroffene Dateien | Fundstellen (Datei:Zeile) | Aufrufer (`txt('…')`) |\n|---|---|---|---|---|\n";
foreach ($duplicates as $folded => [$spellings, $colliding_files]) {
    $module = explode('#:#', $folded)[0];
    $variants = [];
    $places = [];
    $callers = [];
    $example = $colliding_files[0];
    foreach ($spellings as $spelling => $lines) {
        $variants[] = '`' . $spelling . '`';
        $places[] = '`' . $spelling . '`: lang/' . $example . ':' . $lines[$example];
        $found = $callers_of((string) $spelling);
        $callers[] = '`' . $spelling . '`: ' . ($found === [] ? 'keine' : implode(', ', array_slice($found, 0, 5)) . (count($found) > 5 ? sprintf(' … (+%d)', count($found) - 5) : ''));
    }
    $affected = count($colliding_files) >= 25
        ? 'fast alle (' . count($colliding_files) . ')'
        : implode(', ', array_map(static fn(string $f): string => substr($f, 6, -5), $colliding_files));
    echo sprintf("| `%s` | %s | %s | %s | %s |\n", $module, implode(', ', $variants), $affected, implode('<br>', $places), implode('<br>', $callers));
}
