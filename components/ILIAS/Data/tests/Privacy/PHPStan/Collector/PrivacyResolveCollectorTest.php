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

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Analyses a fixture with the collector active; the report rule turns
 * every collected resolve() call into one marker message, so this test
 * covers both classes end to end.
 *
 * @extends RuleTestCase<PrivacyResolveReportRule>
 */
class PrivacyResolveCollectorTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PrivacyResolveReportRule();
    }

    protected function getCollectors(): array
    {
        return [new PrivacyResolveCollector()];
    }

    public function testCollectsEveryResolveCallSite(): void
    {
        $message = static fn(string $purpose_class, array $purpose_args, int $line): string =>
            PrivacyResolveReportRule::MARKER . json_encode([
                'privacy_type' => 'ILIAS\\Data\\Privacy\\Types\\PostalAddress',
                'purpose_class' => $purpose_class,
                'purpose_args' => $purpose_args,
                'component' => 'Data',
                'line' => $line,
            ], JSON_THROW_ON_ERROR);

        $this->analyse([__DIR__ . '/Fixtures/resolve-calls.php'], [
            [$message('DisplayToUser', ['fixture_context'], 52), 52],
            [$message('DisplayToUser', ['factory_context'], 53), 53],
            [$message('PassToComponent', ['Mail', 'fixture_reason'], 54), 54],
            [$message('StoreInTable', ['usr_data.(street,city,zipcode,country)'], 55), 55],
            [$message('StoreInTable', ['DbTableColumn(tmp_table, tmp_column)'], 56), 56],
            [$message('dynamic', ['ILIAS\\Data\\Privacy\\Purpose\\Purpose'], 57), 57],
            [$message('unknown', [], 61), 61],
            [$message('DisplayToUser', ['string'], 62), 62],
            [$message('DisplayToUser', ['string'], 63), 63],
        ]);
    }
}
