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

use Closure;
use ILIAS\Language\ComponentTranslation\PluralForms;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The "Plural-Forms" headers convert_module_to_po.php writes (tools/po-migration/plurals.json, CLDR 48.2) must parse
 * and select the CLDR category of every integer n, checked against a hand-written oracle per language.
 */
class PluralFormsShippedRulesTest extends TestCase
{
    private const string PLURALS_JSON = __DIR__ . '/../../tools/po-migration/plurals.json';

    /**
     * @return array<string, string> lang key => header
     */
    private static function shippedHeaders(): array
    {
        $data = json_decode((string) file_get_contents(self::PLURALS_JSON), true, 512, JSON_THROW_ON_ERROR);

        return $data['plural_forms'];
    }

    /**
     * @return array<string, Closure(int): int> lang key => CLDR category index of n (order of the
     *         categories as in the header: zero, one, two, few, many, other where present)
     */
    private static function oracles(): array
    {
        $one_other = static fn(int $n): int => $n === 1 ? 0 : 1;
        $zero = static fn(int $n): int => 0;
        $many_million = static fn(int $n): int => $n !== 0 && $n % 1000000 === 0 ? 1 : 2;
        $east_slavic = static fn(int $n): int => $n % 10 === 1 && $n % 100 !== 11
            ? 0
            : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 12 || $n % 100 > 14) ? 1 : 2);
        $cs_sk = static fn(int $n): int => $n === 1 ? 0 : ($n >= 2 && $n <= 4 ? 1 : 2);

        return [
            'ar' => static fn(int $n): int => match (true) {
                $n === 0 => 0,
                $n === 1 => 1,
                $n === 2 => 2,
                $n % 100 >= 3 && $n % 100 <= 10 => 3,
                $n % 100 >= 11 => 4,
                default => 5,
            },
            'bg' => $one_other, 'da' => $one_other, 'de' => $one_other, 'el' => $one_other,
            'en' => $one_other, 'et' => $one_other, 'hu' => $one_other, 'ka' => $one_other,
            'nl' => $one_other, 'sq' => $one_other, 'sv' => $one_other, 'tr' => $one_other,
            'fa' => static fn(int $n): int => $n <= 1 ? 0 : 1,
            'ja' => $zero, 'vi' => $zero, 'zh' => $zero,
            'cs' => $cs_sk, 'sk' => $cs_sk,
            'es' => static fn(int $n): int => $n === 1 ? 0 : $many_million($n),
            'it' => static fn(int $n): int => $n === 1 ? 0 : $many_million($n),
            'fr' => static fn(int $n): int => $n <= 1 ? 0 : $many_million($n),
            'pt' => static fn(int $n): int => $n <= 1 ? 0 : $many_million($n),
            'hr' => $east_slavic, 'ru' => $east_slavic, 'sr' => $east_slavic, 'uk' => $east_slavic,
            'pl' => static fn(int $n): int => $n === 1
                ? 0
                : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 12 || $n % 100 > 14) ? 1 : 2),
            'lt' => static fn(int $n): int => $n % 10 === 1 && ($n % 100 < 11 || $n % 100 > 19)
                ? 0
                : ($n % 10 >= 2 && ($n % 100 < 11 || $n % 100 > 19) ? 1 : 2),
            'ro' => static fn(int $n): int => $n === 1
                ? 0
                : ($n === 0 || ($n % 100 >= 1 && $n % 100 <= 19) ? 1 : 2),
            'sl' => static fn(int $n): int => match (true) {
                $n % 100 === 1 => 0,
                $n % 100 === 2 => 1,
                $n % 100 === 3, $n % 100 === 4 => 2,
                default => 3,
            },
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function languages(): array
    {
        $rows = [];
        foreach (self::shippedHeaders() as $lang => $header) {
            $rows[$lang] = [$lang, $header];
        }

        return $rows;
    }

    public function testEveryShippedLanguageHasAnOracleAndViceVersa(): void
    {
        $this->assertEqualsCanonicalizing(
            array_keys(self::shippedHeaders()),
            array_keys(self::oracles()),
            'plurals.json and the oracles of this test must list the same languages'
        );
    }

    #[DataProvider('languages')]
    public function testShippedHeaderSelectsTheCldrCategoryForEveryInteger(string $lang, string $header): void
    {
        $forms = PluralForms::fromHeader($header);
        $oracle = self::oracles()[$lang];

        $samples = [...range(0, 2000), 10000, 100000, 999999, 1000000, 1000001, 2000000, 2147483647];
        foreach ($samples as $n) {
            $this->assertSame($oracle($n), $forms->formIndexFor($n), sprintf('%s, n = %d', $lang, $n));
        }
        // header and the largest index used agree, so no form of the .po is never selected
        $used = array_unique(array_map($oracle, $samples));
        $this->assertSame(max($used) + 1, $forms->getCount(), $lang);
    }
}
