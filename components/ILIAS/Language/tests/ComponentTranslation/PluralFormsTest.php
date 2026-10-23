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

use ILIAS\Language\ComponentTranslation\PluralForms;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * PluralForms: the parser/evaluator for "Plural-Forms" headers, the "one value" rule
 * (defaultFormIndex()/defaultValueOf()) and the fallback to the Germanic rule.
 */
class PluralFormsTest extends TestCase
{
    /**
     * Real "Plural-Forms" headers of gettext/CLDR, checked for a handful of representative n's each -
     * every value is what msgfmt/gettext itself would select.
     *
     * @param array<int, int> $expected n => form index
     */
    #[DataProvider('wellKnownHeaders')]
    public function testFormIndexForMatchesTheKnownRuleOfTheLanguage(string $header, int $count, array $expected): void
    {
        $forms = PluralForms::fromHeader($header);

        $this->assertSame($count, $forms->getCount());
        foreach ($expected as $n => $index) {
            $this->assertSame($index, $forms->formIndexFor($n), sprintf('n = %d', $n));
        }
    }

    /**
     * @return array<string, array{0: string, 1: int, 2: array<int, int>}>
     */
    public static function wellKnownHeaders(): array
    {
        return [
            'Germanic (en/de)' => [
                'nplurals=2; plural=(n != 1);',
                2,
                [0 => 1, 1 => 0, 2 => 1, 100 => 1],
            ],
            'French' => [
                'nplurals=2; plural=(n > 1);',
                2,
                [0 => 0, 1 => 0, 2 => 1, 100 => 1],
            ],
            'single form (ja)' => [
                'nplurals=1; plural=0;',
                1,
                [0 => 0, 1 => 0, 2 => 0, 100 => 0],
            ],
            'Russian' => [
                'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);',
                3,
                [1 => 0, 21 => 0, 2 => 1, 3 => 1, 4 => 1, 22 => 1, 5 => 2, 11 => 2, 100 => 2],
            ],
            'Polish' => [
                'nplurals=3; plural=(n==1 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);',
                3,
                [1 => 0, 2 => 1, 5 => 2, 12 => 2, 22 => 1],
            ],
            'Czech' => [
                'nplurals=3; plural=((n==1) ? 0 : (n>=2 && n<=4) ? 1 : 2);',
                3,
                [1 => 0, 2 => 1, 4 => 1, 5 => 2, 0 => 2],
            ],
            'Arabic' => [
                'nplurals=6; plural=(n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 && n%100<=99 ? 4 : 5);',
                6,
                [0 => 0, 1 => 1, 2 => 2, 3 => 3, 10 => 3, 11 => 4, 99 => 4, 100 => 5, 101 => 5],
            ],
        ];
    }

    /**
     * "==" binds tighter than "?:", and "?:" is right-associative: "a ? b : c ? d : e" reads as
     * "a ? b : (c ? d : e)" - a left-associative (or wrong-precedence) parser would reject the header
     * outright (the sampled range includes n's failing for the wrong tree) or select the wrong form.
     */
    public function testTernaryIsRightAssociativeAndBindsLooserThanComparisons(): void
    {
        // n==1 -> 0 ; n==2 -> 1 ; else -> 2 - only correct if grouped as n==1 ? 0 : (n==2 ? 1 : 2)
        $forms = PluralForms::fromHeader('nplurals=3; plural=n==1 ? 0 : n==2 ? 1 : 2;');

        $this->assertSame(0, $forms->formIndexFor(1));
        $this->assertSame(1, $forms->formIndexFor(2));
        $this->assertSame(2, $forms->formIndexFor(3));
    }

    /**
     * "%" binds tighter than the comparisons, which bind tighter than "&&"/"||": "n%10==1 && n%100!=11"
     * must be "((n%10)==1) && ((n%100)!=11)".
     */
    public function testModuloBindsTighterThanComparisonsAndComparisonsTighterThanLogicalAnd(): void
    {
        $forms = PluralForms::fromHeader('nplurals=2; plural=(n%10==1 && n%100!=11) ? 0 : 1;');

        $this->assertSame(0, $forms->formIndexFor(21));
        $this->assertSame(1, $forms->formIndexFor(11));
    }

    public function testBinaryOperatorsAreLeftAssociative(): void
    {
        // (1-... not available: "-" is not part of the grammar) use "%" chained: (n % 5) % 2
        $forms = PluralForms::fromHeader('nplurals=2; plural=n%5%2;');

        // n=7 -> 7%5=2, 2%2=0 ; if evaluated right-associatively (5%2=1, n%1=0) it would differ for n=6
        $this->assertSame(0, $forms->formIndexFor(7));
        $forms2 = PluralForms::fromHeader('nplurals=2; plural=n%6%2;');
        $this->assertSame(0, $forms2->formIndexFor(6), '6%6=0, 0%2=0');
    }

    /**
     * The default form is always the LAST one (CLDR's "other", the most general category), never fixed at index 1
     * regardless of the form count.
     */
    #[DataProvider('countsAndDefaultIndex')]
    public function testDefaultFormIndexForCountIsTheLastForm(int $count, int $expected): void
    {
        $this->assertSame($expected, PluralForms::defaultFormIndexForCount($count));
    }

    /**
     * @return array<string, array{0: int, 1: int}>
     */
    public static function countsAndDefaultIndex(): array
    {
        return [
            'single form (ja/vi/zh)' => [1, 0],
            'two forms (en/de)' => [2, 1],
            'three forms (cs/pl/ru)' => [3, 2],
            'four forms (sl)' => [4, 3],
            'six forms (ar)' => [6, 5],
        ];
    }

    public function testDefaultValueOfFallsBackDownwardsWhenTheLastFormIsEmpty(): void
    {
        $forms = PluralForms::fromHeader('nplurals=2; plural=(n != 1);');

        $this->assertSame('Eintrag', $forms->defaultValueOf(['Eintrag', '']));
    }

    public function testDefaultValueOfFallsBackDownwardsWhenTheLastFormIsMissingEntirely(): void
    {
        $forms = PluralForms::fromHeader('nplurals=2; plural=(n != 1);');

        $this->assertSame('Eintrag', $forms->defaultValueOf([0 => 'Eintrag']));
    }

    public function testDefaultValueOfPrefersTheLastFormWhenItIsNotEmpty(): void
    {
        $forms = PluralForms::fromHeader('nplurals=2; plural=(n != 1);');

        $this->assertSame('Einträge', $forms->defaultValueOf(['Eintrag', 'Einträge']));
    }

    public function testDefaultValueOfUsesFormZeroForASingleFormLanguage(): void
    {
        $forms = PluralForms::fromHeader('nplurals=1; plural=0;');

        $this->assertSame('Eintrag', $forms->defaultValueOf(['Eintrag']));
    }

    public function testDefaultValueOfWithNoFormsAtAllIsEmpty(): void
    {
        $forms = PluralForms::fromHeader('nplurals=2; plural=(n != 1);');

        $this->assertSame('', $forms->defaultValueOf([]));
    }

    /**
     * Three forms (e.g. Czech): the last one (index 2, "many") is preferred over the earlier ones.
     */
    public function testDefaultValueOfPrefersTheLastOfThreeForms(): void
    {
        $forms = PluralForms::fromHeader('nplurals=3; plural=((n==1) ? 0 : (n>=2 && n<=4) ? 1 : 2);');

        $this->assertSame('mnoho', $forms->defaultValueOf(['jeden', 'dva', 'mnoho']));
    }

    /**
     * A gap in the middle (index 1 empty) is not the fallback target: the search from the last form
     * downward skips straight to the next non-empty one before it, not to index 0.
     */
    public function testDefaultValueOfSkipsAnEmptyFormInTheMiddleWhileSearchingDownwards(): void
    {
        $forms = PluralForms::fromHeader('nplurals=4; plural=(n%100==1?0:(n%100==2?1:(n%100==3||n%100==4?2:3)));');

        $this->assertSame(
            'dve',
            $forms->defaultValueOf(['ena', 'dve', '', '']),
            'form 3 (the actual default) and form 2 are both empty - form 1 is the closest non-empty one'
        );
    }

    /**
     * Every form empty: no non-empty value exists anywhere to fall back to.
     */
    public function testDefaultValueOfIsEmptyWhenEveryFormIsEmpty(): void
    {
        $forms = PluralForms::fromHeader('nplurals=3; plural=((n==1) ? 0 : (n>=2 && n<=4) ? 1 : 2);');

        $this->assertSame('', $forms->defaultValueOf(['', '', '']));
    }

    /**
     * A formula that fails to evaluate for a sampled n (a division by zero only that n reaches, see
     * testFormIndexForFallsBackToTheDefaultFormWhenTheFormulaDividesByZeroForThisN below) falls back
     * to the LAST form even for a language with more than two forms - not to index 1.
     */
    public function testFormIndexForFallsBackToTheLastFormOnEvaluationFailureForAMultiFormLanguage(): void
    {
        $forms = PluralForms::fromHeader('nplurals=3; plural=(n % (n==300 ? 0 : 3));');

        $this->assertSame(2, $forms->formIndexFor(300));
    }

    public function testIsOneSingularOtherPluralIsTrueForTheGermanicRule(): void
    {
        $this->assertTrue(PluralForms::germanic()->isOneSingularOtherPlural());
    }

    public function testIsOneSingularOtherPluralIsFalseForFrenchWhichTreatsZeroAsSingular(): void
    {
        $this->assertFalse(PluralForms::fromHeader('nplurals=2; plural=(n > 1);')->isOneSingularOtherPlural());
    }

    public function testIsOneSingularOtherPluralIsFalseForAnyNonTwoFormCount(): void
    {
        $this->assertFalse(PluralForms::fromHeader('nplurals=1; plural=0;')->isOneSingularOtherPlural());
        $this->assertFalse(PluralForms::fromHeader('nplurals=3; plural=((n==1) ? 0 : (n>=2 && n<=4) ? 1 : 2);')->isOneSingularOtherPlural());
    }

    public function testFormIndexForTreatsANegativeNAsItsAbsoluteValueLikeGettext(): void
    {
        $forms = PluralForms::fromHeader('nplurals=2; plural=(n != 1);');

        $this->assertSame($forms->formIndexFor(1), $forms->formIndexFor(-1));
        $this->assertSame($forms->formIndexFor(5), $forms->formIndexFor(-5));
    }

    /**
     * abs(PHP_INT_MIN) overflows a native int - must not throw or produce a bogus result.
     */
    public function testFormIndexForHandlesPhpIntMinWithoutOverflowing(): void
    {
        $forms = PluralForms::fromHeader('nplurals=2; plural=(n != 1);');

        $this->assertSame(1, $forms->formIndexFor(\PHP_INT_MIN));
    }

    /**
     * A division by zero (here: modulo) only reached for a particular n (not one of the n's
     * fromHeader() itself samples while validating the header, or construction would already have
     * thrown) falls back to the default form for that n instead of throwing out of formIndexFor().
     */
    public function testFormIndexForFallsBackToTheDefaultFormWhenTheFormulaDividesByZeroForThisN(): void
    {
        $forms = PluralForms::fromHeader('nplurals=2; plural=(n % (n==300 ? 0 : 1));');

        $this->assertSame($forms->defaultFormIndex(), $forms->formIndexFor(300));
    }

    #[DataProvider('invalidHeaders')]
    public function testFromHeaderThrowsForAnInvalidHeader(string $header): void
    {
        $this->expectException(InvalidArgumentException::class);
        PluralForms::fromHeader($header);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidHeaders(): array
    {
        return [
            'empty' => [''],
            'missing plural=' => ['nplurals=2;'],
            'missing nplurals=' => ['plural=(n != 1);'],
            'nplurals zero' => ['nplurals=0; plural=0;'],
            'nplurals negative' => ['nplurals=-1; plural=0;'],
            'nplurals too large (11)' => ['nplurals=11; plural=n%11;'],
            'not a number nplurals' => ['nplurals=x; plural=0;'],
            'identifier other than n' => ['nplurals=2; plural=(m != 1);'],
            'function call' => ['nplurals=2; plural=intdiv(n,2);'],
            'variable' => ['nplurals=2; plural=$n;'],
            'semicolon injection' => ['nplurals=2; plural=(n != 1); system("x");'],
            'assignment' => ['nplurals=2; plural=(n = 1);'],
            'unary minus' => ['nplurals=2; plural=(-n != 1);'],
            'unsupported operator +' => ['nplurals=2; plural=(n + 1) % 2;'],
            'unbalanced parenthesis' => ['nplurals=2; plural=(n != 1;'],
            'trailing garbage' => ['nplurals=2; plural=(n != 1) x;'],
            'empty ternary branch' => ['nplurals=2; plural=n ? : 1;'],
            'dangling operator' => ['nplurals=2; plural=n ==;'],
            'formula too long' => ['nplurals=2; plural=' . str_repeat('(n!=1)&&', 100) . '(n!=1);'],
            'integer with too many digits' => ['nplurals=2; plural=n==1000000000 ? 0 : 1;'],
            'index equal to count (out of range)' => ['nplurals=2; plural=2;'],
            'deeply nested parentheses beyond the depth limit' => [
                'nplurals=2; plural=' . str_repeat('(', 100) . 'n!=1' . str_repeat(')', 100) . ';',
            ],
        ];
    }

    public function testFromHeaderAcceptsMaximumNplurals(): void
    {
        $forms = PluralForms::fromHeader('nplurals=10; plural=n%10;');

        $this->assertSame(10, $forms->getCount());
    }

    /**
     * ReDoS guard: an overlong header (e.g. a long whitespace run before a literal that never matches) is rejected by a
     * strlen() check before the regex runs - proven by an execution time bound far below catastrophic backtracking, not
     * just by the exception type.
     */
    public function testAnOverlongHeaderIsRejectedBeforeTheRegularExpressionRunsOnIt(): void
    {
        $header = str_repeat(' ', 10000) . 'x'; // never matches "nplurals..." - would force backtracking
        $started = microtime(true);

        try {
            PluralForms::fromHeader($header);
            $this->fail('Expected an InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('too long', $e->getMessage());
        }

        $this->assertLessThan(1.0, microtime(true) - $started, 'rejected in well under a second - no backtracking took place');
    }

    public function testGetHeaderRoundTripsTheParsedHeader(): void
    {
        $forms = PluralForms::fromHeader('nplurals=3; plural=((n==1) ? 0 : (n>=2 && n<=4) ? 1 : 2);');

        $this->assertSame(3, PluralForms::fromHeader($forms->getHeader())->getCount());
        $this->assertSame($forms->formIndexFor(5), PluralForms::fromHeader($forms->getHeader())->formIndexFor(5));
    }

    public function testFromHeaderOrGermanicReturnsTheParsedRuleForAValidHeader(): void
    {
        $forms = PluralForms::fromHeaderOrGermanic('nplurals=1; plural=0;');

        $this->assertSame(1, $forms->getCount());
    }

    public function testFromHeaderOrGermanicFallsBackAndReportsForANullHeader(): void
    {
        $messages = [];
        $forms = PluralForms::fromHeaderOrGermanic(null, function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        $this->assertSame(2, $forms->getCount());
        $this->assertTrue($forms->isOneSingularOtherPlural());
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Missing Plural-Forms', $messages[0]);
    }

    public function testFromHeaderOrGermanicFallsBackAndReportsForABlankHeader(): void
    {
        $messages = [];
        PluralForms::fromHeaderOrGermanic('   ', function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        $this->assertCount(1, $messages);
    }

    public function testFromHeaderOrGermanicFallsBackAndReportsForAnInvalidHeader(): void
    {
        $messages = [];
        $forms = PluralForms::fromHeaderOrGermanic('garbage', function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        $this->assertSame(2, $forms->getCount());
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Invalid Plural-Forms header "garbage"', $messages[0]);
    }

    public function testFromHeaderOrGermanicWorksWithoutACallback(): void
    {
        $forms = PluralForms::fromHeaderOrGermanic('garbage');

        $this->assertSame(2, $forms->getCount());
    }

    public function testGermanicIsTheDocumentedFallbackHeader(): void
    {
        $this->assertSame('nplurals=2; plural=(n != 1);', PluralForms::GERMANIC_HEADER);
        $this->assertSame(2, PluralForms::germanic()->getCount());
    }

    /**
     * For every form of the new rule, the forms of the previous rule that served the same sampled
     * quantities - the basis of taking local values over by meaning, not by index.
     *
     * @param array<int, list<int>> $expected new form => previous forms
     */
    #[DataProvider('ruleChanges')]
    public function testPreviousFormsOfListsThePreviousFormsServingTheSameQuantities(string $previous, string $rule, array $expected): void
    {
        $this->assertSame($expected, PluralForms::fromHeader($rule)->previousFormsOf(PluralForms::fromHeader($previous)));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: array<int, list<int>>}>
     */
    public static function ruleChanges(): array
    {
        $one = 'nplurals=1; plural=0;';
        $germanic = 'nplurals=2; plural=(n != 1);';
        $three = 'nplurals=3; plural=(n == 1 ? 0 : n >= 2 && n <= 4 ? 1 : 2);';

        return [
            'vi: 1 -> 2 forms (CLDR 49)' => [$one, 'nplurals=2; plural=n > 1;', [0 => [0], 1 => [0]]],
            '2 -> 3 forms' => [$germanic, $three, [0 => [0], 1 => [1], 2 => [1]]],
            '3 -> 2 forms' => [$three, $germanic, [0 => [0], 1 => [1, 2]]],
            'the same rule is the identity' => [$three, $three, [0 => [0], 1 => [1], 2 => [2]]],
            'a form only large quantities select (es: millions)' => [
                'nplurals=3; plural=(n == 1 ? 0 : n != 0 && n % 1000000 == 0 ? 1 : 2);', $germanic, [0 => [0], 1 => [1, 2]],
            ],
            'a form no sampled quantity selects' => [$germanic, 'nplurals=3; plural=(n == 1 ? 0 : 1);', [0 => [0], 1 => [1], 2 => []]],
        ];
    }

    #[DataProvider('sameFormsRules')]
    public function testSelectsSameFormsAsComparesTheSelectedFormsNotTheSpelling(string $header, string $other, bool $expected): void
    {
        $this->assertSame($expected, PluralForms::fromHeader($header)->selectsSameFormsAs(PluralForms::fromHeader($other)));
        $this->assertSame($expected, PluralForms::fromHeader($other)->selectsSameFormsAs(PluralForms::fromHeader($header)));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: bool}>
     */
    public static function sameFormsRules(): array
    {
        return [
            'white space and parentheses' => ['nplurals=2; plural=n != 1;', 'nplurals=2;  plural=( n!=1 );', true],
            'another formula, same forms' => ['nplurals=2; plural=n != 1;', 'nplurals=2; plural=n == 1 ? 0 : 1;', true],
            'another number of forms' => ['nplurals=1; plural=0;', 'nplurals=2; plural=0;', false],
            'same number, another formula' => ['nplurals=2; plural=n != 1;', 'nplurals=2; plural=n > 1;', false],
            'differs only for a large n' => [
                'nplurals=2; plural=n != 1;',
                'nplurals=2; plural=(n != 1 && n != 1000000 ? 1 : 0);',
                false,
            ],
        ];
    }
}
