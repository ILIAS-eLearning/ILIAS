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

        $consecutive_returns = [
            'certificate_a4' => 'A4',
            'certificate_a4_landscape' => 'A4l',
            'certificate_a5' => 'A5',
            'certificate_a5_landscape' => 'A5l',
            'certificate_letter' => 'L',
            'certificate_letter_landscape' => 'Ll',
            'certificate_custom' => 'C',
        ];
        $languageMock->method('txt')
            ->willReturnCallback(fn($k) => $consecutive_returns[$k]);

        $pageFormats = new ilPageFormats($languageMock);

        $formats = $pageFormats->fetchPageFormats();

        $this->assertSame('a4', $formats['a4']['value']);
        $this->assertSame('210mm', $formats['a4']['width']);

        $this->assertSame('297mm', $formats['a4']['height']);
        $this->assertSame('A4', $formats['a4']['name']);

        $this->assertSame('a4landscape', $formats['a4landscape']['value']);
        $this->assertSame('297mm', $formats['a4landscape']['width']);
        $this->assertSame('210mm', $formats['a4landscape']['height']);
        $this->assertSame('A4l', $formats['a4landscape']['name']);

        $this->assertSame('a5', $formats['a5']['value']);
        $this->assertSame('148mm', $formats['a5']['width']);
        $this->assertSame('210mm', $formats['a5']['height']);
        $this->assertSame('A5', $formats['a5']['name']);

        $this->assertSame('a5landscape', $formats['a5landscape']['value']);
        $this->assertSame('210mm', $formats['a5landscape']['width']);
        $this->assertSame('148mm', $formats['a5landscape']['height']);
        $this->assertSame('A5l', $formats['a5landscape']['name']);

        $this->assertSame('letter', $formats['letter']['value']);
        $this->assertSame('8.5in', $formats['letter']['width']);
        $this->assertSame('11in', $formats['letter']['height']);
        $this->assertSame('L', $formats['letter']['name']);

        $this->assertSame('letterlandscape', $formats['letterlandscape']['value']);
        $this->assertSame('11in', $formats['letterlandscape']['width']);
        $this->assertSame('8.5in', $formats['letterlandscape']['height']);
        $this->assertSame('Ll', $formats['letterlandscape']['name']);

        $this->assertSame('custom', $formats['custom']['value']);
        $this->assertSame('', $formats['custom']['width']);
        $this->assertSame('', $formats['custom']['height']);
        $this->assertSame('C', $formats['custom']['name']);
    }
}
