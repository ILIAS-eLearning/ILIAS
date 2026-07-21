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

namespace ILIAS\Data\Privacy\PHPStan\Collector;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Emits one report "error" per collected resolve() call, so the entries
 * survive into PHPStan's JSON output (which does not contain collected
 * data itself). Each message is the marker followed by the JSON payload.
 *
 * Only registered in privacy-analysis.neon for generator runs — never in
 * a linting configuration.
 *
 * @implements Rule<CollectedDataNode>
 */
final class PrivacyResolveReportRule implements Rule
{
    public const string MARKER = 'PRIVACY-RESOLVE ';

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        foreach ($node->get(PrivacyResolveCollector::class) as $file => $entries) {
            foreach ($entries as $entry) {
                $errors[] = RuleErrorBuilder::message(
                    self::MARKER . json_encode($entry, JSON_THROW_ON_ERROR)
                )
                    ->file($file)
                    ->line($entry['line'])
                    ->identifier('ilias.privacy.resolveReport')
                    ->build();
            }
        }
        return $errors;
    }
}
