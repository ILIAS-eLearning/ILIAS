<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceTrimmedDocumentPurifierTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceTrimmedDocumentPurifierTest extends \ilTermsOfServiceCriterionBaseTest
{
    /**
     * @return array[]
     */
    public function stringsToTrimProvider() : array
    {
        return [
            [' phpunit ', 'phpunit', ],
            ["\n\r\tphpunit\n\r\t", 'phpunit', ],
        ];
    }

    /**
     * @return array[]
     */
    public function stringElementsArrayToTrimProvider() : array
    {
        return [
            [[' phpunit '], ['phpunit'], ],
            [["\n\r\tphpunit\n\r\t"], ['phpunit'], ],
        ];
    }

    /**
     * @dataProvider stringsToTrimProvider
     * @param string $text
     * @param string $expectedResult
     */
    public function testSingleStringIsTrimmed(string $text, string $expectedResult)
    {
        $aggregated = $this
            ->getMockBuilder(\ilHtmlPurifierInterface::class)
            ->getMock();

        $aggregated
            ->expects($this->once())
            ->method('purify')
            ->with($text)
            ->willReturn($text);

        $purifier = new \ilTermsOfServiceTrimmedDocumentPurifier($aggregated);

        $this->assertEquals($expectedResult, $purifier->purify($text));
    }

    /**
     * @dataProvider stringElementsArrayToTrimProvider
     * @param string[] $texts
     * @param string[] $expectedResult
     */
    public function testArrayOfStringElementsIsTrimmed(array $texts, array $expectedResult)
    {
        $aggregated = $this
            ->getMockBuilder(\ilHtmlPurifierInterface::class)
            ->getMock();

        $aggregated
            ->expects($this->exactly(count($texts)))
            ->method('purify')
            ->with($this->isType('string'))
            ->willReturnArgument(0);

        $purifier = new \ilTermsOfServiceTrimmedDocumentPurifier($aggregated);

        $this->assertEquals($expectedResult, $purifier->purifyArray($texts));
    }
}
