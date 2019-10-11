<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Report;

use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * Generates a report according to gitlabs subset of codeclimate json files.
 *
 * @author Hans-Christian Otto <c.otto@suora.com>
 *
 * @internal
 */
final class GitlabReporter implements ReporterInterface
{
    public function getFormat()
    {
        return 'gitlab';
    }

    /**
     * Process changed files array. Returns generated report.
     *
     * @param ReportSummary $reportSummary
     *
     * @return string
     */
    public function generate(ReportSummary $reportSummary)
    {
        $report = [];
        foreach ($reportSummary->getChanged() as $fileName => $change) {
            foreach ($change['appliedFixers'] as $fixerName) {
                $report[] = [
                    'description' => $fixerName,
                    'location.path' => $fileName,
                    'fingerprint' => md5($fileName.$fixerName),
                    'location.lines.begin' => 0, // line numbers are required in the format, but not available to reports
                ];
            }
        }

        $jsonString = json_encode($report);

        return $reportSummary->isDecoratedOutput() ? OutputFormatter::escape($jsonString) : $jsonString;
    }
}
