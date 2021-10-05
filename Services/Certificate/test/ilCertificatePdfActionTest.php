<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfActionTest extends ilCertificateBaseTestCase
{
    public function testCreatePdfWillCreatedAndIsDownloadable() : void
    {
        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = $this->getMockBuilder(ilPdfGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generateCurrentActiveCertificate'])
            ->getMock();

        $pdfGenerator->method('generateCurrentActiveCertificate')
            ->willReturn('Something');

        $ilUtilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $errorHandler = $this->getMockBuilder(ilErrorHandling::class)
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

    public function testPdfDownloadAction() : void
    {
        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = $this->getMockBuilder(ilPdfGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generateCurrentActiveCertificate', 'generateFileName'])
            ->getMock();

        $pdfGenerator->method('generateCurrentActiveCertificate')
            ->willReturn('Something');

        $pdfGenerator->method('generateFileName')
            ->willReturn('some_file_name.pdf');

        $ilUtilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $ilUtilHelper->method('deliverData')
            ->with(
                'Something',
                'some_file_name.pdf',
                'application/pdf'
            );

        $errorHandler = $this->getMockBuilder(ilErrorHandling::class)
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

    public function testDownloadResultsInExceptionBecauseTheServerIsNotActive() : void
    {
        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = $this->getMockBuilder(ilPdfGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generateCurrentActiveCertificate', 'generateFileName'])
            ->getMock();

        $pdfGenerator->method('generateCurrentActiveCertificate')
            ->willReturn('Something');

        $pdfGenerator->method('generateFileName')
            ->willReturn('some_file_name.pdf');

        $ilUtilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $ilUtilHelper->method('deliverData')
            ->with(
                'Something',
                'some_file_name.pdf',
                'application/pdf'
            )
        ->willThrowException(new ilRpcClientException(''));


        $errorHandler = $this->getMockBuilder(ilErrorHandling::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['raiseError'])
            ->getMock();

        $errorHandler
            ->expects($this->once())
            ->method('raiseError');

        $pdfAction = new ilCertificatePdfAction($logger, $pdfGenerator, $ilUtilHelper, '', $errorHandler);

        $result = $pdfAction->downloadPdf(10, 200);

        $this->assertEquals('', $result);
    }
}
