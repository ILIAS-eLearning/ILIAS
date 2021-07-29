<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilHtmlDomNodeIteratorTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlDomNodeIteratorTest extends TestCase
{
    public function testDomNodeIteratorIteratesOverXhtmlDocumentNodes() : void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<body><div><p><b>phpunit</b> <i>test</i></p></div></body>');

        $expectedElements = [
            'body',
            'div',
            'p',
            'b',
            '#text',
            '#text',
            'i',
            '#text',
        ];
        $actualElements = [];

        $iter = new RecursiveIteratorIterator(
            new ilHtmlDomNodeIterator($dom),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iter as $element) {
            /** @var DOMNode $element */
            $actualElements[] = $element->nodeName;
        }

        $this->assertEquals($expectedElements, $actualElements);
    }
}
