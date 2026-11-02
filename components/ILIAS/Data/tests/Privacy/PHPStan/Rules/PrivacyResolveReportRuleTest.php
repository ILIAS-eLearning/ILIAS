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

namespace ILIAS\Scripts\PHPStan\Rules\Privacy;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Analyses a fixture; every resolve() call on a privacy data type becomes
 * one marker message.
 *
 * @extends RuleTestCase<PrivacyResolveReportRule>
 */
class PrivacyResolveReportRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PrivacyResolveReportRule($this->createReflectionProvider());
    }


    public function testCollectsEveryResolveCallSite(): void
    {
        $message = static fn(
            string $requirement_class,
            array $requirement_args,
            int $line,
            string $location = 'resolveCalls()',
            string $type = 'PostalAddress'
        ): string =>
            PrivacyResolveReportRule::MARKER . json_encode([
                'privacy_type' => 'ILIAS\\Data\\Privacy\\Types\\' . $type,
                'requirement_class' => $requirement_class,
                'requirement_args' => $requirement_args,
                'component' => 'Data',
                'location' => $location,
            ], JSON_THROW_ON_ERROR);

        $this->analyse([__DIR__ . '/Fixtures/resolve-calls.php'], [
            [$message('DisplayToUser', ['where' => 'on the page', 'to' => 'the reader', 'in_order_to' => 'read it'], 54), 54],
            [$message('DisplayToUser', ['to' => 'the reader', 'where' => 'in the list', 'in_order_to' => 'find it'], 55), 55],
            [$message('PassToComponent', ['component' => 'Mail', 'in_order_to' => 'send it'], 56), 56],
            [$message('StoreInTable', ['target' => 'catalogue:ILIAS\\Data\\Privacy\\Source\\Known\\UserSources::postalAddress', 'in_order_to' => 'keep it'], 57), 57],
            [$message('StoreInTable', ['target' => 'DbTableColumn(tmp_table, tmp_column)', 'in_order_to' => 'keep it', 'condition' => 'it is enabled'], 58), 58],
            [$message('dynamic', ['ILIAS\\Data\\Privacy\\TechnicalRequirement\\TechnicalRequirement'], 59), 59],
            [$message('unknown', [], 63), 63],
            [$message('DisplayToUser', ['where' => '{string}', 'to' => 'the reader', 'in_order_to' => 'read it'], 64), 64],
            [$message('DisplayToUser', ['where' => '{string}', 'to' => 'the reader', 'in_order_to' => 'read it'], 65), 65],
            [$message('Export', ['where' => 'as CSV', 'by' => 'the reader', 'in_order_to' => 'keep a copy'], 66), 66],
            [$message('Export', ['where' => 'as vCard', 'by' => 'the reader', 'in_order_to' => 'keep a copy'], 67), 67],
            [$message('Export', [], 69), 69],
            [$message('DisplayToUser', ['where' => 'in the method', 'to' => 'the reader', 'in_order_to' => 'read it'], 76, 'ResolvingClass::method()'), 76],
            [$message('DisplayToUser', ['where' => 'nullsafe', 'to' => 'the reader', 'in_order_to' => 'read it'], 90, 'nullableResolveCalls()'), 90],
            [$message('DisplayToUser', ['where' => 'nullsafe chain', 'to' => 'the reader', 'in_order_to' => 'read it'], 91, 'nullableResolveCalls()'), 91],
            [$message('dynamic', ['ILIAS\\Data\\Privacy\\TechnicalRequirement\\DisplayToUser|ILIAS\\Data\\Privacy\\TechnicalRequirement\\Export'], 105, 'ambiguousResolveCalls()'), 105],
            [$message('PassToComponent', ['component' => 'Mail', 'in_order_to' => 'send it'], 106, 'ambiguousResolveCalls()'), 106],
            [$message('DisplayToUser', ['where' => 'in the union', 'to' => 'the reader', 'in_order_to' => 'read it'], 107, 'ambiguousResolveCalls()', 'PersonName'), 107],
            [$message('DisplayToUser', ['where' => 'in the union', 'to' => 'the reader', 'in_order_to' => 'read it'], 107, 'ambiguousResolveCalls()'), 107],
        ]);
    }
}
