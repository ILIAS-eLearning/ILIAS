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
 * Lists every value of the shipped `lang/ilias_*.lang` files that TranslationMarkupPolicy does not
 * allow, grouped by key, as a Markdown table on stdout.
 *
 * Usage (ILIAS root): php components/ILIAS/Language/tools/po-migration/scan_shipped_markup.php
 *
 * Declares no named classes or functions, to stay out of Composer's classmap (as convert_module_to_po.php).
 */

$ilias_root = dirname(__DIR__, 5);
require $ilias_root . '/vendor/composer/vendor/autoload.php';

$policy = new ILIAS\Language\ComponentTranslation\TranslationMarkupPolicy();
$by_key = [];
$total = 0;
foreach (glob($ilias_root . '/lang/ilias_*.lang') ?: [] as $file) {
    $lang = substr(basename($file, '.lang'), 6);
    $started = false;
    foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        if (!$started) {
            $started = trim($line) === '<!-- language file start -->';
            continue;
        }
        $parts = explode('#:#', $line);
        if (count($parts) < 3) {
            continue;
        }
        $violations = $policy->findViolations(explode('###', $parts[2])[0]);
        if ($violations === []) {
            continue;
        }
        $total++;
        $key = $parts[0] . '#:#' . $parts[1];
        $by_key[$key]['langs'][] = $lang;
        foreach ($violations as $violation) {
            $by_key[$key]['violations'][$violation] = true;
        }
    }
}
ksort($by_key);

echo sprintf("%d Werte in %d Keys\n\n", $total, count($by_key));
echo "| Key | Sprachen | Verstoß |\n|---|---|---|\n";
foreach ($by_key as $key => $data) {
    $langs = count($data['langs']) >= 25
        ? 'fast alle (' . count($data['langs']) . ')'
        : implode(', ', $data['langs']);
    $violations = implode('; ', array_map(
        static fn(string $violation): string => str_replace('|', '\\|', $violation),
        array_keys($data['violations'])
    ));
    echo sprintf("| `%s` | %s | %s |\n", $key, $langs, htmlspecialchars($violations, ENT_NOQUOTES));
}
