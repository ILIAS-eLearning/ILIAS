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
 * @extends RuleTestCase<NoRawValueAccessRule>
 */
class NoRawValueAccessRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoRawValueAccessRule();
    }

    public function testRule(): void
    {
        $message = static fn(string $function): string => sprintf(
            'PrivacyDataType passed directly to %s(). Call ->resolve() with a Purpose first.',
            $function
        );

        $this->analyse([__DIR__ . '/Fixtures/no-raw-value-access.php'], [
            [$message('var_dump'), 27],
            [$message('print_r'), 28],
            [$message('var_export'), 29],
            [$message('json_encode'), 30],
            [$message('serialize'), 31],
            [$message('var_dump'), 34],
        ]);
    }
}
