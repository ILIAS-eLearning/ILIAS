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

namespace ILIAS\Scripts\PHPStan\ErrorFormatter;

use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\Output;

/**
 * Renders the code-rules result as a Markdown summary (violations per rule and per
 * component) meant to be redirected into $GITHUB_STEP_SUMMARY.
 *
 * The human-readable rule name is taken from each error's `rule` metadata (which the
 * rules set to their LABEL constant), so this formatter needs no knowledge of the
 * individual rules — adding a rule requires no change here.
 */
final class StepSummaryFormatter implements ErrorFormatter
{
    private const COMPONENT_REGEX = '#/components/ILIAS/([^/]+)/#';
    private const OTHER = 'other';
    private const TOP_COMPONENTS = 10;

    public function formatErrors(AnalysisResult $analysisResult, Output $output): int
    {
        $total = 0;
        /** @var array<string, array{label: string, count: int}> $per_rule */
        $per_rule = [];
        /** @var array<string, int> $per_component */
        $per_component = [];
        /** @var array<string, array<string, array{label: string, count: int}>> $component_rules */
        $component_rules = [];

        foreach ($analysisResult->getFileSpecificErrors() as $error) {
            $total++;

            $identifier = $error->getIdentifier() ?? 'unknown';
            $label = $error->getMetadata()['rule'] ?? $identifier;
            $per_rule[$identifier] ??= ['label' => $label, 'count' => 0];
            $per_rule[$identifier]['count']++;

            $component = preg_match(self::COMPONENT_REGEX, $error->getFile(), $matches)
                ? $matches[1]
                : self::OTHER;
            $per_component[$component] = ($per_component[$component] ?? 0) + 1;
            $component_rules[$component][$identifier] ??= ['label' => $label, 'count' => 0];
            $component_rules[$component][$identifier]['count']++;
        }

        uasort($per_rule, static fn(array $a, array $b): int => $b['count'] <=> $a['count']);
        arsort($per_component);

        $output->writeLineFormatted('## ILIAS Code Rules — ' . $total . ' violation' . ($total === 1 ? '' : 's'));
        $output->writeLineFormatted('');

        if ($total === 0) {
            $output->writeLineFormatted('No violations. :white_check_mark:');
            return 0;
        }

        $output->writeLineFormatted('| Rule | Violations |');
        $output->writeLineFormatted('|---|--:|');
        foreach ($per_rule as $identifier => $info) {
            $output->writeLineFormatted('| ' . $info['label'] . ' (`' . $identifier . '`) | ' . $info['count'] . ' |');
        }

        $output->writeLineFormatted('');
        $output->writeLineFormatted('**Top components**');
        $output->writeLineFormatted('');
        foreach (array_slice($per_component, 0, self::TOP_COMPONENTS, true) as $component => $count) {
            $output->writeLineFormatted('- **' . $component . '** — ' . $count);

            $rules = $component_rules[$component];
            uasort($rules, static fn(array $a, array $b): int => $b['count'] <=> $a['count']);
            foreach ($rules as $info) {
                $output->writeLineFormatted('  - ' . $info['label'] . ': ' . $info['count']);
            }
        }

        return 1;
    }
}
