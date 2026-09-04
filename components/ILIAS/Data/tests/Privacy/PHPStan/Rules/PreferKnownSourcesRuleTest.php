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

namespace ILIAS\Data\Privacy\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<PreferKnownSourcesRule>
 */
class PreferKnownSourcesRuleTest extends RuleTestCase
{
    private const string TIP = 'Add a getter to components/ILIAS/Data/src/Privacy/Source/Known.';

    protected function getRule(): Rule
    {
        return new PreferKnownSourcesRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/prefer-known-sources.php'], [
            [
                'Direct DbTableColumn("usr_data", "street") — add this column to the KnownSources'
                . ' catalogue or annotate with @privacy-undocumented.',
                33,
                self::TIP,
            ],
            [
                'Direct DbTableColumns("usr_data", "street", "city") — add this column to the KnownSources'
                . ' catalogue or annotate with @privacy-undocumented.',
                34,
                self::TIP,
            ],
        ]);
    }

    public function testCatalogueItselfIsExempt(): void
    {
        $this->analyse(
            [__DIR__ . '/../../../../src/Privacy/Source/Known/UserSources.php'],
            []
        );
    }
}
