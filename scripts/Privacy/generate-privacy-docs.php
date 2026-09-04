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
 * Generates privacy data usage documentation per ILIAS component from
 * the resolve() calls collected by the privacy PHPStan extension.
 *
 * Usage:
 *   php scripts/Privacy/generate-privacy-docs.php [--run-phpstan] [--dry-run]
 *       [--component=User] [--phpstan-output=/tmp/phpstan-privacy.json]
 *       [--components-dir=components/ILIAS] [--overwrite-privacy-md]
 *
 * By default the report is written to PRIVACY_DATA.md next to each
 * component's handwritten PRIVACY.md; --overwrite-privacy-md writes to
 * PRIVACY.md instead.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/composer/vendor/autoload.php';

use ILIAS\Data\Privacy\PHPStan\Generator\PrivacyDocGenerator;

$root = dirname(__DIR__, 2);

$options = getopt('', ['dry-run', 'run-phpstan', 'component:', 'phpstan-output:', 'components-dir:', 'overwrite-privacy-md']);
$dry_run = isset($options['dry-run']);
$run_phpstan = isset($options['run-phpstan']);
$filter_component = $options['component'] ?? null;
$phpstan_output = $options['phpstan-output'] ?? sys_get_temp_dir() . '/phpstan-privacy.json';
$components_dir = $options['components-dir'] ?? $root . '/components/ILIAS';
$target_filename = isset($options['overwrite-privacy-md'])
    ? 'PRIVACY.md'
    : PrivacyDocGenerator::DEFAULT_TARGET_FILENAME;

if ($run_phpstan) {
    $paths = $filter_component !== null
        ? "{$components_dir}/{$filter_component}"
        : $components_dir;
    // exit code is non-zero by design: the report rule emits one "error" per resolve()
    passthru(sprintf(
        '%s analyse --configuration=%s --autoload-file=%s --error-format=json --no-progress %s > %s 2>/dev/null',
        escapeshellarg($root . '/vendor/composer/vendor/bin/phpstan'),
        escapeshellarg($root . '/components/ILIAS/Data/PHPStan/Privacy/privacy-analysis.neon'),
        escapeshellarg($root . '/vendor/composer/vendor/autoload.php'),
        escapeshellarg($paths),
        escapeshellarg($phpstan_output)
    ));
}

if (!file_exists($phpstan_output)) {
    fwrite(STDERR, "PHPStan output not found at {$phpstan_output}.\nRun with --run-phpstan.\n");
    exit(1);
}

$results = new PrivacyDocGenerator(
    $phpstan_output,
    $components_dir,
    $dry_run,
    $filter_component,
    $target_filename
)->run();

echo str_repeat('-', 60) . "\n";
foreach ($results as $component => $path) {
    echo sprintf("  %s  %-30s  %s\n", $dry_run ? '[DRY RUN]' : '[written]', $component, $path);
}
echo str_repeat('-', 60) . "\n";
echo sprintf("  %d component(s) processed.%s\n", count($results), $dry_run ? ' (dry run)' : '');
