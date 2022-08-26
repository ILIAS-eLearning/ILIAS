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

/**
 * Class ilTermsOfServiceTrimmedDocumentPurifierTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentsContainsHtmlValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function textProvider(): array
    {
        return [
            'Plain Text' => ['phpunit', false,],
            'HTML Fragment' => ['php<b>unit</b>', true,],
            'HTML Fragment with Email Address Wrapped in <>' => ['php<b>unit</b> <info@ilias.de>', false,],
            'HTML' => ['<html><body>php<b>unit</b></body></html>', true,],
            'HTML with Email Address Wrapped in <>' => ['<html><body>php<b>unit</b>Php Unit <info@ilias.de></body></html>', false,],
            'HTML with Unsupported Entities' => ['<html><body>php<b>unit</b>Php Unit<figure></figure></body></html>', true,],
            'Invalid HTML' => ['<html><body>php<b>unit</b>Php Unit<div </body></html>', false,],
        ];
    }

    /**
     * @dataProvider textProvider
     */
    public function testHtmlCanBeDetected(string $text, bool $result): void
    {
        $validator = new ilTermsOfServiceDocumentsContainsHtmlValidator($text);
        $this->assertSame($result, $validator->isValid());
    }
}
