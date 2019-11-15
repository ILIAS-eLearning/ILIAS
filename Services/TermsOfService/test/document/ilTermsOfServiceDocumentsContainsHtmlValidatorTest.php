<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceTrimmedDocumentPurifierTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentsContainsHtmlValidatorTest extends ilTermsOfServiceCriterionBaseTest
{
    /**
     * @return array
     */
    public function textProvider() : array
    {
        return [
            'Plain Text' => ['phpunit', false,],
            'HTML Fragment' =>['php<b>unit</b>', true,],
            'HTML Fragment with Email Address Wrapped in <>' => ['php<b>unit</b> <info@ilias.de>', false,],
            'HTML' => ['<html><body>php<b>unit</b></body></html>', true,],
            'HTML with Email Address Wrapped in <>' => ['<html><body>php<b>unit</b>Php Unit <info@ilias.de></body></html>', false,],
        ];
    }

    /**
     * @dataProvider textProvider
     * @param string $text
     * @param bool   $result
     */
    public function testHtmlCanBeDetected(string $text, bool $result): void
    {
        $validator = new ilTermsOfServiceDocumentsContainsHtmlValidator($text);
        $this->assertEquals($result, $validator->isValid());
    }
}