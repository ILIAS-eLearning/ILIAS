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
class ilTermsOfServiceTrimmedDocumentPurifierTest extends ilTermsOfServiceCriterionBaseTest
{
    public function stringsToTrimProvider(): array
    {
        return [
            'Text with or without Spaces' => [' phpunit ', 'phpunit',],
            'Text with or without Line Endings and Tabs' => ["\n\r\tphpunit\n\r\t", 'phpunit',],
        ];
    }

    public function stringElementsArrayToTrimProvider(): array
    {
        return [
            'Text with or without Spaces' => [[' phpunit '], ['phpunit'],],
            'Text with or without Line Endings and Tabs' => [["\n\r\tphpunit\n\r\t"], ['phpunit'],],
        ];
    }

    /**
     * @dataProvider stringsToTrimProvider
     * @param string $text
     * @param string $expectedResult
     */
    public function testSingleStringIsTrimmed(string $text, string $expectedResult): void
    {
        $aggregated = $this
            ->getMockBuilder(ilHtmlPurifierInterface::class)
            ->getMock();

        $aggregated
            ->expects($this->once())
            ->method('purify')
            ->with($text)
            ->willReturn($text);

        $purifier = new ilTermsOfServiceTrimmedDocumentPurifier($aggregated);

        $this->assertSame($expectedResult, $purifier->purify($text));
    }

    /**
     * @dataProvider stringElementsArrayToTrimProvider
     * @param string[] $texts
     * @param string[] $expectedResult
     */
    public function testArrayOfStringElementsIsTrimmed(array $texts, array $expectedResult): void
    {
        $aggregated = $this
            ->getMockBuilder(ilHtmlPurifierInterface::class)
            ->getMock();

        $aggregated
            ->expects($this->exactly(count($texts)))
            ->method('purify')
            ->with($this->isType('string'))
            ->willReturnArgument(0);

        $purifier = new ilTermsOfServiceTrimmedDocumentPurifier($aggregated);

        $this->assertSame($expectedResult, $purifier->purifyArray($texts));
    }
}
