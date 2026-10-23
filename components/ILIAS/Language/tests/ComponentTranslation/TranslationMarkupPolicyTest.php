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

/**
 * TranslationMarkupPolicy: the single gate for HTML in a translation value. Security-critical: it stands between a
 * `.po`/local write and stored XSS.
 */
class TranslationMarkupPolicyTest extends TestCase
{
    private TranslationMarkupPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = new TranslationMarkupPolicy();
    }

    #[DataProvider('allowedValues')]
    public function testAllowedValuesAreByteIdenticalAndHaveNoViolations(string $value): void
    {
        $this->assertTrue($this->policy->isAllowed($value));
        $this->assertSame([], $this->policy->findViolations($value));
        $this->assertSame($value, $this->policy->sanitize($value));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function allowedValues(): array
    {
        return [
            'plain text' => ['Hallo Welt'],
            'no angle bracket at all' => ['a <= b und c >= d, aber ohne Tag'],
            'less-than followed by a digit is not a tag start' => ['a<3 und b<5, kein Tag'],
            'a with allowed attributes' => ['<a href="https://ilias.de" target="_blank" rel="noopener" title="ILIAS">Link</a>'],
            'relative href' => ['<a href="/goto.php?target=1">Link</a>'],
            'fragment only href' => ['<a href="#anchor">Sprung</a>'],
            'mailto href' => ['<a href="mailto:info@ilias.de">Mail</a>'],
            'img with src and alt' => ['<img src="https://ilias.de/logo.png" alt="Logo">'],
            'relative img src' => ['<img src="/images/logo.png" alt="Logo">'],
            'span with class and style' => ['<span class="hl" style="color:red">wichtig</span>'],
            'div align' => ['<div align="center">Text</div>'],
            'p align' => ['<p align="right">Text</p>'],
            'br self-closing stays as written' => ['Zeile 1<br/>Zeile 2'],
            'br plain' => ['Zeile 1<br>Zeile 2'],
            'nested formatting' => ['<b><i>fett kursiv</i></b>'],
            'list' => ['<ul><li>eins</li><li>zwei</li></ul>'],
            'ordered list' => ['<ol><li>eins</li></ol>'],
            'placeholder in href is a relative URL' => ['<a href="%1$s">Platzhalter</a>'],
            'gap tag without attributes' => ['<gap></gap>'],
            'b and i crossed (adoption agency, no surrounding element)' => ['<b><i>x</b></i>'],
            'li without end tag (omitted per HTML5)' => ['<ul><li>eins<li>zwei</ul>'],
            'p without end tag (omitted per HTML5)' => ['<p>Absatz eins<p>Absatz zwei'],
            'h3 heading' => ['<h3>Überschrift</h3>'],
            'sub and sup' => ['H<sub>2</sub>O und x<sup>2</sup>'],
            'code and pre' => ['<pre><code>echo 1;</code></pre>'],
        ];
    }

    #[DataProvider('violatingValues')]
    public function testViolatingValuesAreDetected(string $value, string $expected_violation_substring): void
    {
        $this->assertFalse($this->policy->isAllowed($value));
        $violations = $this->policy->findViolations($value);
        $this->assertNotEmpty($violations);
        $found = false;
        foreach ($violations as $violation) {
            if (str_contains($violation, $expected_violation_substring)) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, sprintf(
            'Expected a violation containing "%s", got: %s',
            $expected_violation_substring,
            implode('; ', $violations)
        ));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function violatingValues(): array
    {
        return [
            'script tag' => ['<script>alert(1)</script>', 'tag <script>'],
            'onerror on img' => ['<img src="x.png" alt="x" onerror="alert(1)">', 'attribute "onerror"'],
            'onclick on a' => ['<a href="/x" onclick="alert(1)">x</a>', 'attribute "onclick"'],
            'javascript scheme in href' => ['<a href="javascript:alert(1)">x</a>', 'URL "javascript:alert(1)"'],
            'javascript scheme uppercase' => ['<a href="JavaScript:alert(1)">x</a>', 'URL "JavaScript:alert(1)"'],
            'javascript scheme with a tab' => ["<a href=\"java\tscript:alert(1)\">x</a>", 'URL'],
            'javascript scheme with a newline' => ["<a href=\"java\nscript:alert(1)\">x</a>", 'URL'],
            'javascript scheme via entity' => ['<a href="&#106;avascript:alert(1)">x</a>', 'URL "javascript:alert(1)"'],
            'data scheme in img src' => ['<img src="data:text/html,<script>alert(1)</script>" alt="x">', 'URL "data:'],
            'body onload' => ['<body onload="alert(1)">x</body>', 'attribute "onload"'],
            'html attribute' => ['<html onmouseover="alert(1)">x</html>', 'attribute "onmouseover"'],
            // <td>/<tr>/<tbody> are not in ALLOWED_ATTRIBUTES at all - the table-context parse (which
            // alone turns a bare <td> into an element) reports the tag itself as the violation
            'td is not an allowed tag at all' => ['<td onclick="alert(1)">x</td>', 'tag <td>'],
            'comment' => ['<!-- comment -->', 'comment'],
            'broken closing tag becomes a comment' => ['</ i>', 'comment'],
            'unknown tag' => ['<blink>x</blink>', 'tag <blink>'],
            'disallowed attribute on allowed tag' => ['<b class="x">fett</b>', 'attribute "class" of <b>'],
            'end tags without start tags' => ['</div></td></table><h3>Fake</h3>', 'unbalanced markup'],
            'div closed inside li, li closed after it' => ['<li><div></li></div>', 'closes a surrounding <div>'],
            'the same inside a list' => ['<ul><li><div></li></div></ul>', 'closes a surrounding <div>'],
            'div closed inside span' => ['<span class="x"><div></span></div>', 'unclosed tag <span>'],
            'unclosed a' => ['<a href="https://evil.example">Klick', 'unclosed tag <a>'],
            'style with url()' => ['<span style="background-color:url(https://evil.example/x.png)">x</span>', 'style "background-color:url('],
            'style with position' => ['<span style="position:fixed;color:red">x</span>', 'style "position:fixed'],
            'style on unstyled tag' => ['<p style="color:red">x</p>', 'attribute "style" of <p>'],
            // a value ending inside an unfinished tag - harmless alone, but a browser completes it with the markup
            // following the value on the page
            'unfinished img tag with a dangling attribute at the end' => ['<img src=x onerror=alert(1)// ', 'incomplete tag at the end'],
            'unfinished a tag with an unterminated quoted attribute' => ["<a href='javascript:alert(1)'", 'incomplete tag at the end'],
            'unfinished img self-closing slash without the closing ">"' => ['<img src="x" alt="y"/', 'incomplete tag at the end'],
            // a "<" inside an attribute value is harmless as an attribute, but the SAME value is also inserted into
            // raw-text/script contexts elsewhere on the page, where it would start real markup (e.g. break out of a
            // following <title>/<textarea>/<script>)
            '"<" inside a class attribute value (breaks out of a following <title>)' => [
                '<span class="</title><img src=x onerror=alert(1)>">Portfolio</span>',
                '"<" in attribute "class" of <span>',
            ],
            '"<" inside a class attribute value (breaks out of a following inline <script>)' => [
                '<span class="<!--<script>">x</span>',
                '"<" in attribute "class" of <span>',
            ],
        ];
    }

    #[DataProvider('endTagsOfDisallowedTags')]
    public function testAnEndTagOfATagThatIsNotAllowedIsAViolationAndSanitizeRemovesIt(string $value, string $expected_sanitized): void
    {
        $this->assertNotEmpty(array_filter(
            $this->policy->findViolations($value),
            static fn(string $violation): bool => str_contains($violation, 'of a tag that is not allowed')
        ));
        $this->assertFalse($this->policy->isAllowed($value));
        $this->assertSame($expected_sanitized, $this->policy->sanitize($value));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function endTagsOfDisallowedTags(): array
    {
        return [
            'form' => ['Speichern</form>', 'Speichern'],
            'uppercase with whitespace' => ['x</FORM >y', 'xy'],
            'section only' => ['</Section>', ''],
            'th' => ['x</th>', 'x'],
            'dl before an allowed element' => ['</dl><b>y</b>', '<b>y</b>'],
        ];
    }

    #[DataProvider('valuesWithoutEndTagsOfDisallowedTags')]
    public function testAllowedEndTagsAndNoEndTagsAreNotReportedAsEndTagsOfDisallowedTags(string $value): void
    {
        $this->assertSame([], array_values(array_filter(
            $this->policy->findViolations($value),
            static fn(string $violation): bool => str_contains($violation, 'of a tag that is not allowed')
        )));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function valuesWithoutEndTagsOfDisallowedTags(): array
    {
        return [
            'uppercase end tag of an allowed tag' => ['<b>x</B>'],
            'p' => ['x</p>'],
            'a' => ['x</a>'],
            'not an end tag' => ['a </ b'],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('markupOfValues')]
    public function testMarkupOfNamesTheMarkupLanguageNeutral(string $value, array $expected): void
    {
        $this->assertSame($expected, $this->policy->markupOf($this->policy->findViolations($value)));
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function markupOfValues(): array
    {
        return [
            'script' => ['<script>alert(1)</script>', ['<script>']],
            'end tag of a tag not allowed' => ['Speichern</form>', ['</form>']],
            'javascript url' => ['<a href="javascript:alert(1)">x</a>', ['href="javascript:alert(1)"']],
            'style' => ['<span style="position: fixed">x</span>', ['style="position: fixed"']],
            'unclosed' => ['<b>x', ['<b>']],
            'end tag closing a surrounding element' => ['x</div>', ['</div>']],
            'too long' => ['<b>' . str_repeat('a', 16384) . '</b>', ['> 16384 B']],
            'attributes of one tag' => ['<img src="x.png" alt="x" onerror="1" onload="2">', ['<img onerror>', '<img onload>']],
        ];
    }

    public function testMarkupOfHasNoDuplicatesAndKeepsAnUnknownViolationAsItIs(): void
    {
        $this->assertSame(['<b>', 'something else'], $this->policy->markupOf(['unclosed tag <b>', 'tag <b>', 'something else']));
    }

    /**
     * A tag that is reported as not allowed gets no further message for its end tag or for being
     * left open - one message per tag name.
     */
    public function testATagNotAllowedIsReportedOnce(): void
    {
        $this->assertSame(['tag <script>'], $this->policy->findViolations('<script>alert(1)</script>'));
        $this->assertSame(['tag <blink>'], $this->policy->findViolations('<blink>x</blink>'));
        $this->assertSame(['tag <blink>'], $this->policy->findViolations('<blink>x'));
    }

    public function testAValueLongerThanTheCheckLimitWithMarkupIsOneViolationAndEscapedCompletely(): void
    {
        $value = '<b>' . str_repeat('a', 16384) . '</b>';

        $violations = $this->policy->findViolations($value);

        $this->assertCount(1, $violations);
        $this->assertStringContainsString('value too long', $violations[0]);
        $this->assertFalse($this->policy->isAllowed($value));
        $this->assertStringNotContainsString('<', $this->policy->sanitize($value));
    }

    public function testALongValueWithoutMarkupIsNotAViolation(): void
    {
        $value = str_repeat('a', 16385);

        $this->assertSame([], $this->policy->findViolations($value));
        $this->assertSame($value, $this->policy->sanitize($value));
    }

    public function testDeeplyNestedMarkupWithinTheLimitIsHandledWithoutAnError(): void
    {
        $balanced = str_repeat('<b>', 2000) . 'x' . str_repeat('</b>', 2000);
        $unclosed = str_repeat('<b>', 5000);

        $this->assertSame([], $this->policy->findViolations($balanced));
        $this->assertContains('unclosed tag <b>', $this->policy->findViolations($unclosed));
    }

    /**
     * A value that already legitimately contains the sentinel character hasIncompleteTagAtEnd() uses
     * internally (U+E000, a private-use character no real translation needs) must not desynchronise
     * the sentinel count it relies on - a complete, otherwise allowed tag stays allowed.
     */
    public function testAValueContainingTheInternalSentinelCharacterIsStillHandledCorrectly(): void
    {
        $value = "<b>text\u{E000}more</b>";

        $this->assertTrue($this->policy->isAllowed($value));
        $this->assertSame($value, $this->policy->sanitize($value));
    }

    /**
     * The same sentinel character, but now genuinely inside an unfinished tag at the end - still
     * correctly detected despite the extra, user-supplied occurrences the count must add on top of.
     */
    public function testASentinelCharacterInTheValueDoesNotHideAGenuinelyIncompleteTagAtTheEnd(): void
    {
        $value = "\u{E000}<img src=x onerror=alert(1)//";

        $this->assertFalse($this->policy->isAllowed($value));
        $this->assertContains('incomplete tag at the end', $this->policy->findViolations($value));
    }

    /**
     * A value ending in a bare "<" is harmless alone, but concatenated with a second allowed value ("Weiter <" . "img
     * src=x onerror=alert(1)>") it forms real markup; a trailing "<" is therefore rejected.
     */
    public function testAValueEndingInABareLessThanIsAViolation(): void
    {
        $value = 'Weiter <';

        $this->assertFalse($this->policy->isAllowed($value));
        $this->assertContains('incomplete tag at the end', $this->policy->findViolations($value));
    }

    /**
     * sanitize() must not just drop the trailing "<": htmlspecialchars() (the text-only fallback for a value still
     * unresolvable after MAX_SANITIZE_ROUNDS) turns it into "&lt;", which is safe under concatenation and keeps the
     * visible character.
     */
    public function testSanitizeEscapesATrailingLessThanInsteadOfDroppingIt(): void
    {
        $this->assertSame('Weiter &lt;', $this->policy->sanitize('Weiter <'));
    }

    /**
     * A "<" that is not the last character (followed by whitespace, a digit or "=") is plain text and stays allowed
     * byte-identical; only a trailing "<" is rejected.
     */
    #[DataProvider('lessThanNotAtTheEndStaysAllowed')]
    public function testALessThanNotAtTheVeryEndOfTheValueStaysAllowed(string $value): void
    {
        $this->assertTrue($this->policy->isAllowed($value));
        $this->assertSame($value, $this->policy->sanitize($value));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function lessThanNotAtTheEndStaysAllowed(): array
    {
        return [
            'less-than followed by a space' => ['x < '],
            'comparison text' => ['a <= b'],
            'less-than followed by a digit' => ['a<3 und b<5, kein Tag'],
        ];
    }

    public function testACaseInsensitiveAttributeNameIsStillCaughtAsAViolation(): void
    {
        $this->assertFalse($this->policy->isAllowed('<img src="x.png" alt="x" OnError="alert(1)">'));
    }

    public function testATableContextCatchesATagAllowedOnlyOutsideOfIt(): void
    {
        // a bare <img onclick> is invisible to the body parse alone if libxml drops the attribute
        // differently there than in a table context - both parses must be consulted
        $this->assertFalse($this->policy->isAllowed('<tr><td><img src="x.png" alt="x" onclick="alert(1)"></td></tr>'));
    }

    public function testSanitizeKeepsTextOfARemovedScriptTag(): void
    {
        $result = $this->policy->sanitize('vor<script>bad()</script>her');

        $this->assertTrue($this->policy->isAllowed($result));
        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringContainsString('vor', $result);
        $this->assertStringContainsString('her', $result);
    }

    public function testSanitizeRemovesADisallowedAttributeButKeepsTheTag(): void
    {
        $result = $this->policy->sanitize('<img src="x.png" alt="x" onerror="alert(1)">');

        $this->assertTrue($this->policy->isAllowed($result));
        $this->assertStringNotContainsString('onerror', $result);
        $this->assertStringContainsString('src="x.png"', $result);
    }

    public function testSanitizeRemovesAComment(): void
    {
        $result = $this->policy->sanitize('vor<!-- geheim -->her');

        $this->assertTrue($this->policy->isAllowed($result));
        $this->assertStringNotContainsString('geheim', $result);
    }

    public function testSanitizeOfAnAlreadyAllowedValueNeverParsesTheValue(): void
    {
        // isAllowed()/sanitize() must short-circuit before touching the parser for such input
        $this->assertSame('a <= b', $this->policy->sanitize('a <= b'));
    }

    public function testSanitizeOfATemplateTagRemovesItWithItsContent(): void
    {
        $result = $this->policy->sanitize('vor<template><script>bad()</script></template>her');

        $this->assertTrue($this->policy->isAllowed($result));
        $this->assertStringNotContainsString('bad()', $result);
        $this->assertSame('vorher', trim($result));
    }

    #[DataProvider('allowedHrefUrls')]
    public function testAllowedHrefUrlSchemes(string $url): void
    {
        $this->assertTrue($this->policy->isAllowed(sprintf('<a href="%s">x</a>', $url)));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function allowedHrefUrls(): array
    {
        return [
            'http' => ['http://ilias.de'],
            'https' => ['https://ilias.de'],
            'mailto' => ['mailto:info@ilias.de'],
            'relative path' => ['/goto.php'],
            'relative with query' => ['goto.php?target=1'],
            'fragment' => ['#top'],
            'placeholder' => ['%1$s'],
        ];
    }

    #[DataProvider('disallowedSrcUrls')]
    public function testMailtoIsNotAllowedForImgSrc(string $url): void
    {
        $this->assertFalse($this->policy->isAllowed(sprintf('<img src="%s" alt="x">', $url)));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function disallowedSrcUrls(): array
    {
        return [
            'mailto is only allowed for href, not src' => ['mailto:info@ilias.de'],
            'javascript' => ['javascript:alert(1)'],
            'data' => ['data:text/html,x'],
        ];
    }

    /**
     * The single check before any local write (admin edit, import, customizing file, "add new variable"): returns only
     * the invalid entries, keyed by original key in iteration order, never the values (ilLanguageInvalidMarkupException
     * reports only keys).
     */
    public function testFindInvalidValuesReturnsOnlyTheInvalidEntriesInInputOrder(): void
    {
        $result = (new TranslationMarkupPolicy())->findInvalidValues([
            'greeting' => 'Hallo',
            'bad_one' => '<script>alert(1)</script>',
            'farewell' => 'Tschüss',
            'bad_two' => '<img src=x onerror=alert(2)>',
        ]);

        $this->assertSame(['bad_one', 'bad_two'], array_keys($result));
        $this->assertContains('tag <script>', $result['bad_one']);
        $this->assertNotEmpty($result['bad_two']);
    }

    /**
     * Empty when every value is allowed - including the trivial case of no values at all.
     */
    public function testFindInvalidValuesIsEmptyWhenEverythingIsAllowed(): void
    {
        $policy = new TranslationMarkupPolicy();

        $this->assertSame([], $policy->findInvalidValues(['greeting' => 'Hallo', 'farewell' => '<b>Tschüss</b>']));
        $this->assertSame([], $policy->findInvalidValues([]));
    }

    /**
     * An int key is not dropped: it appears in the result (PHP normalises numeric string keys to int anyway;
     * findInvalidValues() casts via `(string) $key`).
     */
    public function testFindInvalidValuesDoesNotDropAnIntegerKey(): void
    {
        $result = (new TranslationMarkupPolicy())->findInvalidValues([42 => '<script>alert(1)</script>']);

        $this->assertSame([42], array_keys($result));
        $this->assertSame('42', implode(',', array_keys($result)));
    }

    /**
     * A non-string value (e.g. a bool/int/null slipping through from an untyped array) is cast to
     * string before the check, exactly like findViolations() itself expects.
     */
    public function testFindInvalidValuesCastsNonStringValuesToStringBeforeChecking(): void
    {
        $result = (new TranslationMarkupPolicy())->findInvalidValues(['flag' => false, 'greeting' => 'Hallo']);

        // false -> "" -> allowed (no tag at all)
        $this->assertSame([], $result);
    }

    /**
     * A value is checked only when the write actually changes it (differs from current and shipped): an unmodified
     * value with disallowed markup that was shipped that way (e.g. a historical `<br \>`) must not block saving or
     * importing the whole file/form; only an edited value violating the policy is rejected.
     */
    /**
     * @param array<string, string> $values
     * @param array<string, string> $current
     * @param array<string, string> $shipped
     * @param list<string> $expected_invalid_keys
     */
    #[DataProvider('findInvalidChangedValuesCases')]
    public function testFindInvalidChangedValues(array $values, array $current, array $shipped, array $expected_invalid_keys): void
    {
        $result = (new TranslationMarkupPolicy())->findInvalidChangedValues($values, $current, $shipped);

        $this->assertSame($expected_invalid_keys, array_keys($result));
    }

    /**
     * @return array<string, array{0: array<string,string>, 1: array<string,string>, 2: array<string,string>, 3: list<string>}>
     */
    public static function findInvalidChangedValuesCases(): array
    {
        $bad = '<script>alert(1)</script>';

        return [
            'unmodified value equal to the shipped one, even with disallowed markup, is not checked' => [
                ['key' => $bad], ['key' => 'irrelevant, current differs too'], ['key' => $bad], [],
            ],
            'unmodified value equal to the current one, even with disallowed markup, is not checked' => [
                ['key' => $bad], ['key' => $bad], ['key' => 'unrelated shipped value'], [],
            ],
            'a genuinely changed value with disallowed markup is rejected' => [
                ['key' => $bad], ['key' => 'Hallo'], ['key' => 'Servus'], ['key'],
            ],
            'a genuinely changed but allowed value is never in the result' => [
                ['key' => '<b>Hallo</b>'], ['key' => 'Hallo'], ['key' => 'Servus'], [],
            ],
            'a key not present in current or shipped at all (a new entry) is checked' => [
                ['key' => $bad], [], [], ['key'],
            ],
            'export-then-reimport unchanged: same as both current and shipped, byte for byte' => [
                ['key' => $bad], ['key' => $bad], ['key' => $bad], [],
            ],
        ];
    }

    public function testFindInvalidChangedValuesReturnsTheSameViolationsAsFindInvalidValues(): void
    {
        $policy = new TranslationMarkupPolicy();
        $bad = '<img src=x onerror=alert(1)>';

        $changed = $policy->findInvalidChangedValues(['key' => $bad], ['key' => 'Hallo'], []);
        $plain = $policy->findInvalidValues(['key' => $bad]);

        $this->assertSame($plain, $changed);
    }
}
