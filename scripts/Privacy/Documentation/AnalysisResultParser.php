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

namespace ILIAS\Scripts\Privacy\Documentation;

use ILIAS\Scripts\PHPStan\Rules\Privacy\PrivacyResolveReportRule;

/**
 * Reads PHPStan's JSON output and extracts the entries reported by
 * {@see PrivacyResolveReportRule} (messages prefixed with its marker).
 */
class AnalysisResultParser
{
    /**
     * @return list<array{privacy_type: string, requirement_class: string, requirement_args: list<string>, component: string, file: string, location: string}>
     */
    public function parse(string $json_file): array
    {
        $raw = file_get_contents($json_file);
        if ($raw === false) {
            throw new \RuntimeException("Cannot read {$json_file}.");
        }
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (($data['errors'] ?? []) !== []) {
            // an analysis that crashed is incomplete, a report built from it would be wrong
            throw new \RuntimeException(
                "PHPStan reported errors, the output is incomplete:\n" . implode("\n", (array) $data['errors'])
            );
        }

        $entries = [];
        foreach ($data['files'] ?? [] as $file => $file_result) {
            foreach ($file_result['messages'] ?? [] as $message) {
                $text = $message['message'] ?? '';
                if (!str_starts_with($text, PrivacyResolveReportRule::MARKER)) {
                    continue;
                }
                $payload = json_decode(
                    substr($text, strlen(PrivacyResolveReportRule::MARKER)),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
                if (!is_array($payload)) {
                    continue;
                }
                $entries[] = [
                    'privacy_type' => $payload['privacy_type'] ?? 'unknown',
                    'requirement_class' => $payload['requirement_class'] ?? 'unknown',
                    'requirement_args' => $payload['requirement_args'] ?? [],
                    'component' => $payload['component'] ?? 'Unknown',
                    'file' => (string) $file,
                    'location' => $payload['location'] ?? '',
                ];
            }
        }
        return $entries;
    }
}
