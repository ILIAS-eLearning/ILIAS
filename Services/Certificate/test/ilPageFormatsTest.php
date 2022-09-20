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
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilPageFormatsTest extends ilCertificateBaseTestCase
{
    public function testFetchFormats(): void
    {
        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt'])
            ->getMock();

        $languageMock->method('txt')
            ->withConsecutive(
                ['certificate_a4'],
                ['certificate_a4_landscape'],
                ['certificate_a5'],
                ['certificate_a5_landscape'],
                ['certificate_letter'],
                ['certificate_letter_landscape'],
                ['certificate_custom']
            )
            ->willReturn('Some Translation');

        $pageFormats = new ilPageFormats($languageMock);

        $formats = $pageFormats->fetchPageFormats();

        $this->assertSame('a4', $formats['a4']['value']);
        $this->assertSame('210mm', $formats['a4']['width']);

        $this->assertSame('297mm', $formats['a4']['height']);
        $this->assertSame('Some Translation', $formats['a4']['name']);

        $this->assertSame('a4landscape', $formats['a4landscape']['value']);
        $this->assertSame('297mm', $formats['a4landscape']['width']);
        $this->assertSame('210mm', $formats['a4landscape']['height']);
        $this->assertSame('Some Translation', $formats['a4landscape']['name']);

        $this->assertSame('a5', $formats['a5']['value']);
        $this->assertSame('148mm', $formats['a5']['width']);
        $this->assertSame('210mm', $formats['a5']['height']);
        $this->assertSame('Some Translation', $formats['a5']['name']);

        $this->assertSame('a5landscape', $formats['a5landscape']['value']);
        $this->assertSame('210mm', $formats['a5landscape']['width']);
        $this->assertSame('148mm', $formats['a5landscape']['height']);
        $this->assertSame('Some Translation', $formats['a5landscape']['name']);

        $this->assertSame('letter', $formats['letter']['value']);
        $this->assertSame('8.5in', $formats['letter']['width']);
        $this->assertSame('11in', $formats['letter']['height']);
        $this->assertSame('Some Translation', $formats['letter']['name']);

        $this->assertSame('letterlandscape', $formats['letterlandscape']['value']);
        $this->assertSame('11in', $formats['letterlandscape']['width']);
        $this->assertSame('8.5in', $formats['letterlandscape']['height']);
        $this->assertSame('Some Translation', $formats['letterlandscape']['name']);

        $this->assertSame('custom', $formats['custom']['value']);
        $this->assertSame('', $formats['custom']['width']);
        $this->assertSame('', $formats['custom']['height']);
        $this->assertSame('Some Translation', $formats['custom']['name']);
    }
}
