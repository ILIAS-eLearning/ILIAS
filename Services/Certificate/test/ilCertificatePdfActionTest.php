<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfActionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatePdfWillCreatedAndIsDownloadable()
    {
        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = $this->getMockBuilder('ilPdfGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generateCurrentActiveCertificate'))
            ->getMock();

        $pdfGenerator->method('generateCurrentActiveCertificate')
            ->willReturn('Something');

        $ilUtilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $errorHandler = $this->getMockBuilder('ilErrorHandling')
            ->disableOriginalConstructor()
            ->getMock();

        $pdfAction = new ilCertificatePdfAction(
            $logger,
            $pdfGenerator,
            $ilUtilHelper,
            'translatedError',
            $errorHandler
        );

        $result = $pdfAction->createPDF(10, 200);

        $this->assertEquals('Something', $result);
    }

    public function testPdfDownloadAction()
    {
        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = $this->getMockBuilder('ilPdfGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generateCurrentActiveCertificate', 'generateFileName'))
            ->getMock();

        $pdfGenerator->method('generateCurrentActiveCertificate')
            ->willReturn('Something');

        $pdfGenerator->method('generateFileName')
            ->willReturn('some_file_name.pdf');

        $ilUtilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $ilUtilHelper->method('deliverData')
            ->with(
                'Something',
                'some_file_name.pdf',
                'application/pdf'
            );

        $errorHandler = $this->getMockBuilder('ilErrorHandling')
            ->disableOriginalConstructor()
            ->getMock();

        $pdfAction = new ilCertificatePdfAction(
            $logger,
            $pdfGenerator,
            $ilUtilHelper,
            'translatedError',
            $errorHandler
        );
        $result = $pdfAction->downloadPdf(10, 200);

        $this->assertEquals('Something', $result);
    }

    public function testDownloadResultsInExceptionBecauseTheServerIsNotActive()
    {
        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = $this->getMockBuilder('ilPdfGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generateCurrentActiveCertificate', 'generateFileName'))
            ->getMock();

        $pdfGenerator->method('generateCurrentActiveCertificate')
            ->willReturn('Something');

        $pdfGenerator->method('generateFileName')
            ->willReturn('some_file_name.pdf');

        $ilUtilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $ilUtilHelper->method('deliverData')
            ->with(
                'Something',
                'some_file_name.pdf',
                'application/pdf'
            )
        ->willThrowException(new ilRpcClientException(''));


        $errorHandler = $this->getMockBuilder('ilErrorHandling')
            ->getMock();

        $errorHandler
            ->expects($this->once())
            ->method('raiseError');

        $pdfAction = new ilCertificatePdfAction($logger, $pdfGenerator, $ilUtilHelper, '', $errorHandler);

        $result = $pdfAction->downloadPdf(10, 200);

        $this->assertEquals('', $result);
    }
}
