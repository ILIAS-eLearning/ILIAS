<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;

/**
 * Class ilHtmlDomNodeIteratorTest
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilHtmlDomNodeIteratorTest extends TestCase
{
    public function testDomNodeIteratorIteratesOverXhtmlDocumentNodes(): void
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

        $this->assertSame($expectedElements, $actualElements);
    }
}
