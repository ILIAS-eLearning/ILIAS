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

namespace ILIAS\Language\Tests\ComponentTranslation;

use ILIAS\Language\ComponentTranslation\PluralFormKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * PluralFormKey: the "<identifier> [<form>]" shape of admin GUI rows and local changes of a plural message.
 * of()/parse() must round-trip, and parse() must reject lookalikes (a legacy identifier ending in "[...]").
 */
class PluralFormKeyTest extends TestCase
{
    public function testOfFormatsTheIdentifierAndForm(): void
    {
        $this->assertSame('poll_population [0]', PluralFormKey::of('poll_population', 0));
        $this->assertSame('poll_population [1]', PluralFormKey::of('poll_population', 1));
    }

    #[DataProvider('roundTripKeys')]
    public function testOfAndParseRoundTrip(string $identifier, int $form): void
    {
        $this->assertSame([$identifier, $form], PluralFormKey::parse(PluralFormKey::of($identifier, $form)));
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function roundTripKeys(): array
    {
        return [
            'simple' => ['poll_population', 0],
            'two digit form' => ['poll_population', 10],
            'identifier containing brackets' => ['weird [legacy]', 1],
            'identifier containing a space' => ['poll population', 1],
        ];
    }

    #[DataProvider('nonFormKeys')]
    public function testParseReturnsNullForAnythingNotShapedLikeOf(string $key): void
    {
        $this->assertNull(PluralFormKey::parse($key));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function nonFormKeys(): array
    {
        return [
            'plain identifier' => ['poll_population'],
            'no space before bracket' => ['poll_population[0]'],
            'no closing bracket' => ['poll_population [0'],
            'non-numeric form' => ['poll_population [x]'],
            'leading zero form' => ['poll_population [01]'],
            'negative form' => ['poll_population [-1]'],
            'empty identifier' => [' [0]'],
            'empty string' => [''],
            'trailing characters after bracket' => ['poll_population [0] extra'],
        ];
    }

    public function testParseAcceptsTheHighestSupportedTwoDigitForm(): void
    {
        $this->assertSame(['id', 99], PluralFormKey::parse('id [99]'));
    }

    public function testParseRejectsAThreeDigitForm(): void
    {
        $this->assertNull(PluralFormKey::parse('id [100]'));
    }
}
