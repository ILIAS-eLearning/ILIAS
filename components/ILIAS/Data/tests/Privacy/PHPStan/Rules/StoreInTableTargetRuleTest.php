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
 * @extends RuleTestCase<StoreInTableTargetRule>
 */
class StoreInTableTargetRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new StoreInTableTargetRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/store-in-table-target.php'], [
            [
                'StoreInTable requires a DbTarget argument.',
                34,
            ],
            [
                'StoreInTable expects a DbTarget (DbTableColumn/DbTableColumns), got string.'
                . ' Use the KnownSources catalogue.',
                35,
            ],
        ]);
    }
}
