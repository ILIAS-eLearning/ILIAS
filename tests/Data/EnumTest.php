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

namespace ILIAS\Data;

use Data\EnumIntSample;
use Data\EnumStringSample;
use InvalidArgumentException;
use Generator;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public function backedEnumProvider(): Generator
    {
        require_once __DIR__ . '/EnumStringSample.php';
        require_once __DIR__ . '/EnumIntSample.php';

        yield 'Backed string enum' => [EnumStringSample::class, fn (int $case_id): string => 'case' . $case_id];
        yield 'Backed int enum' => [EnumIntSample::class, fn (int $case_id): int => $case_id];
    }

    /**
     * @param class-string<EnumStringSample|EnumIntSample> $enum_user
     * @param callable(int $case_id): (int|string) $case_id_transformer
     * @dataProvider backedEnumProvider
     */
    public function testBackedEnumTrait(string $enum_user, callable $case_id_transformer): void
    {
        $cases = $enum_user::cases();

        foreach (range(1, count($cases)) as $case_id) {
            $expected_value = $case_id_transformer($case_id);

            $method = 'CASE' . $case_id;
            $case = $enum_user::$method();
            $this->assertSame($expected_value, $case->value());

            $case_from = $enum_user::from($expected_value);
            $this->assertSame($case->value(), $case_from->value());
        }

        foreach ($cases as $case) {
            $this->assertInstanceOf($enum_user, $case);
        }

        $invalid_value = $case_id_transformer(count($cases) + 1);
        try {
            $enum_user::from($invalid_value);
            $this->fail('Creating an Enum by calling "from" with an invalid case value should throw an exception');
        } catch (InvalidArgumentException) {
        }

        $this->assertNull($enum_user::tryFrom($invalid_value));
    }
}
