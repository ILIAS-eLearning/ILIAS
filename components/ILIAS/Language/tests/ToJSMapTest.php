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

/**
 * ilLanguage::toJSMap() embeds values into a `<script>` block via json_encode(): without JSON_HEX_TAG a value with "</script>"
 * would end the element early, and a "'" in the key could break out of the JS string literal.
 */
class ToJSMapTest extends ilLanguageBaseTestCase
{
    /**
     * @param array<string, string> $map
     * @return list<string>
     */
    private function callToJSMap(array $map): array
    {
        $captured = [];
        $tpl = $this->createStub(ilGlobalTemplateInterface::class);
        $tpl->method('addOnloadCode')->willReturnCallback(function (string $code) use (&$captured): void {
            $captured[] = $code;
        });
        $this->setGlobalVariable('tpl', $tpl);

        $language = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        $language->toJSMap($map, $tpl);

        return $captured;
    }

    public function testAValueEndingTheScriptElementIsNeutralisedByHexEncoding(): void
    {
        $code = $this->callToJSMap(['greeting' => 'Hallo</script><script>alert(1)</script>']);

        $this->assertCount(1, $code);
        $this->assertStringNotContainsString('</script>', $code[0]);
        $this->assertStringContainsString('\u003Cscript\u003E', $code[0]);
    }

    public function testTheGeneratedCodeNeverClosesTheSurroundingScriptElement(): void
    {
        $code = $this->callToJSMap(['greeting' => 'Hallo</script><script>alert(1)</script>']);
        $html = '<!DOCTYPE html><html><body><script>' . $code[0] . '</script><p id="after">content</p></body></html>';

        $document = Dom\HTMLDocument::createFromString($html, LIBXML_NOERROR, 'UTF-8');

        $this->assertSame(1, $document->getElementsByTagName('script')->length);
        $this->assertNotNull($document->getElementById('after'));
    }

    public function testASingleQuoteInTheKeyCannotBreakOutOfTheJsStringLiteral(): void
    {
        $code = $this->callToJSMap(["it's_a_key" => 'Wert']);

        $this->assertCount(1, $code);
        $this->assertStringNotContainsString("'a_key", $code[0]);
        $this->assertStringContainsString(json_encode("it's_a_key", JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), $code[0]);
    }

    public function testAnEmptyValueIsSkippedEntirely(): void
    {
        $code = $this->callToJSMap(['empty' => '', 'filled' => 'x']);

        $this->assertCount(1, $code);
        $this->assertStringContainsString('filled', $code[0]);
    }
}
