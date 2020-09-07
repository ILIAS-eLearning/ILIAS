<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilPageFormatsTest extends \PHPUnit_Framework_TestCase
{
    public function testFetchFormats()
    {
        $languageMock = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->setMethods(array('txt'))
            ->getMock();

        $languageMock->method('txt')
            ->withConsecutive(
                array('certificate_a4'),
                array('certificate_a4_landscape'),
                array('certificate_a5'),
                array('certificate_a5_landscape'),
                array('certificate_letter'),
                array('certificate_letter_landscape'),
                array('certificate_custom')
            )
            ->willReturn('Some Translation');

        $pageFormats = new ilPageFormats($languageMock);

        $formats = $pageFormats->fetchPageFormats();

        $this->assertEquals('a4', $formats['a4']['value']);
        $this->assertEquals('210mm', $formats['a4']['width']);

        $this->assertEquals('297mm', $formats['a4']['height']);
        $this->assertEquals('Some Translation', $formats['a4']['name']);

        $this->assertEquals('a4landscape', $formats['a4landscape']['value']);
        $this->assertEquals('297mm', $formats['a4landscape']['width']);
        $this->assertEquals('210mm', $formats['a4landscape']['height']);
        $this->assertEquals('Some Translation', $formats['a4landscape']['name']);

        $this->assertEquals('a5', $formats['a5']['value']);
        $this->assertEquals('148mm', $formats['a5']['width']);
        $this->assertEquals('210mm', $formats['a5']['height']);
        $this->assertEquals('Some Translation', $formats['a5']['name']);

        $this->assertEquals('a5landscape', $formats['a5landscape']['value']);
        $this->assertEquals('210mm', $formats['a5landscape']['width']);
        $this->assertEquals('148mm', $formats['a5landscape']['height']);
        $this->assertEquals('Some Translation', $formats['a5landscape']['name']);

        $this->assertEquals('letter', $formats['letter']['value']);
        $this->assertEquals('8.5in', $formats['letter']['width']);
        $this->assertEquals('11in', $formats['letter']['height']);
        $this->assertEquals('Some Translation', $formats['letter']['name']);

        $this->assertEquals('letterlandscape', $formats['letterlandscape']['value']);
        $this->assertEquals('11in', $formats['letterlandscape']['width']);
        $this->assertEquals('8.5in', $formats['letterlandscape']['height']);
        $this->assertEquals('Some Translation', $formats['letterlandscape']['name']);

        $this->assertEquals('custom', $formats['custom']['value']);
        $this->assertEquals('', $formats['custom']['width']);
        $this->assertEquals('', $formats['custom']['height']);
        $this->assertEquals('Some Translation', $formats['custom']['name']);
    }
}
