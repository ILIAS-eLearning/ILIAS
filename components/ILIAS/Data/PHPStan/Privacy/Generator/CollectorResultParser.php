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

namespace ILIAS\Data\Privacy\PHPStan\Generator;

use ILIAS\Data\Privacy\PHPStan\Collector\PrivacyResolveReportRule;

/**
 * Reads PHPStan's JSON output and extracts the entries emitted by
 * {@see PrivacyResolveReportRule} (messages prefixed with its marker).
 */
final class CollectorResultParser
{
    /**
     * @return list<array{privacy_type: string, purpose_class: string, purpose_args: list<string>, component: string, file: string, line: int}>
     */
    public function parse(string $json_file): array
    {
        $raw = file_get_contents($json_file);
        if ($raw === false) {
            throw new \RuntimeException("Cannot read {$json_file}.");
        }
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

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
                    'purpose_class' => $payload['purpose_class'] ?? 'unknown',
                    'purpose_args' => $payload['purpose_args'] ?? [],
                    'component' => $payload['component'] ?? 'Unknown',
                    'file' => (string) $file,
                    'line' => $payload['line'] ?? 0,
                ];
            }
        }
        return $entries;
    }
}
