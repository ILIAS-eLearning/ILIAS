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

namespace ILIAS\Language\ComponentTranslation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LegacyFuzzyMarkerTest extends TestCase
{
    /** @return array<string, array{string, bool}> */
    public static function comments(): array
    {
        return [
            'dated marker' => ['30 08 2015 new variable', true],
            'month name' => ['10 Jul 2006 new variable', true],
            'iso date' => ['2022-05-04 new variable', true],
            'one leading # ("####" in the .lang file)' => ['#04 05 2022 new variable', true],
            'several leading # and spaces' => ['## 04 05 2022 new variable', true],
            'note mentioning new variable' => ['bitte pruefen (new variable)', false],
            'new variable without any date' => ['new variable', false],
            'new variable before the date' => ['new variable 04 05 2022', false],
            'text before the date' => ['siehe 04 05 2022 new variable', false],
            'year only' => ['2024 new variable', false],
            'date without marker' => ['#04 05 2022 changed', false],
        ];
    }

    #[DataProvider('comments')]
    public function testMatches(string $comment, bool $expected): void
    {
        $this->assertSame($expected, LegacyFuzzyMarker::matches($comment));
    }
}
