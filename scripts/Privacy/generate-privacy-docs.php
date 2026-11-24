#!/usr/bin/env php
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

/**
 * Generates the per-component privacy documentation (PRIVACY_DATA.md) from
 * the resolve() calls of privacy data types reported by the privacy PHPStan
 * rule. The handwritten PRIVACY.md is never touched.
 *
 * Usage:
 *   php scripts/Privacy/generate-privacy-docs.php [--run-phpstan] [--dry-run | --check]
 *       [--component=User] [--phpstan-output=/tmp/phpstan-privacy.json]
 *       [--components-dir=components/ILIAS]
 *
 * A component gets a report if it resolves privacy data types; reports of
 * other components are removed.
 * --check writes nothing and exits with 1 if a report is missing, outdated
 * or orphaned. The generator refuses to work with an incomplete PHPStan
 * analysis.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/composer/vendor/autoload.php';

use ILIAS\Scripts\Privacy\Documentation\PrivacyDocGenerator;

$root = dirname(__DIR__, 2);

$options = getopt('', ['dry-run', 'check', 'run-phpstan', 'component:', 'phpstan-output:', 'components-dir:']);
$dry_run = isset($options['dry-run']);
$check = isset($options['check']);
$run_phpstan = isset($options['run-phpstan']);
$filter_component = $options['component'] ?? null;
$phpstan_output = $options['phpstan-output'] ?? null;
if ($phpstan_output === null && !$run_phpstan) {
    fwrite(STDERR, "Pass --run-phpstan, or --phpstan-output=<file> with the output of an earlier run.\n");
    exit(1);
}
if ($phpstan_output === null) {
    // a fresh analysis gets its own file, so parallel runs do not collide
    $phpstan_output = tempnam(sys_get_temp_dir(), 'phpstan-privacy-');
    register_shutdown_function(static fn() => @unlink($phpstan_output));
}
$components_dir = $options['components-dir'] ?? $root . '/components/ILIAS';

if ($run_phpstan) {
    $paths = $filter_component !== null
        ? "{$components_dir}/{$filter_component}"
        : $components_dir;
    // exit code 1 is expected: the report rule emits one "error" per resolve()
    passthru(sprintf(
        '%s analyse --configuration=%s --autoload-file=%s --error-format=json --no-progress --memory-limit=-1 %s > %s',
        escapeshellarg($root . '/vendor/composer/vendor/bin/phpstan'),
        escapeshellarg($root . '/scripts/PHPStan/privacy-analysis.neon'),
        escapeshellarg($root . '/vendor/composer/vendor/autoload.php'),
        escapeshellarg($paths),
        escapeshellarg($phpstan_output)
    ), $phpstan_exit_code);
    if ($phpstan_exit_code > 1) {
        fwrite(STDERR, "PHPStan failed with exit code {$phpstan_exit_code}, no report written.\n");
        exit(1);
    }
} elseif (file_exists($phpstan_output)) {
    echo sprintf(
        "  Using existing PHPStan output %s from %s (pass --run-phpstan to analyse again).\n",
        $phpstan_output,
        date('Y-m-d H:i', (int) filemtime($phpstan_output))
    );
}

if (!file_exists($phpstan_output)) {
    fwrite(STDERR, "PHPStan output not found at {$phpstan_output}.\n");
    exit(1);
}

$generator = new PrivacyDocGenerator(
    $phpstan_output,
    $components_dir,
    $dry_run,
    $filter_component,
    // only an analysis of all components, run right now, tells which reports are orphaned
    $run_phpstan && $filter_component === null
);

if ($check) {
    try {
        $stale = $generator->check();
    } catch (\RuntimeException $e) {
        fwrite(STDERR, $e->getMessage() . "\n");
        exit(1);
    }
    foreach ($stale as $component => $path) {
        echo sprintf("  [stale]  %-30s  %s\n", $component, $path);
    }
    if ($stale !== []) {
        echo sprintf("  %d report(s) do not match the code. Re-run without --check.\n", count($stale));
        exit(1);
    }
    echo "  All reports match the code.\n";
    exit(0);
}

try {
    $results = $generator->run();
} catch (\RuntimeException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

echo str_repeat('-', 60) . "\n";
foreach ($results as $component => $path) {
    $state = is_file($path) || $dry_run ? '[written]' : '[removed]';
    echo sprintf("  %s  %-30s  %s\n", $dry_run ? '[DRY RUN]' : $state, $component, $path);
}
echo str_repeat('-', 60) . "\n";
echo sprintf("  %d component(s) processed.%s\n", count($results), $dry_run ? ' (dry run)' : '');
