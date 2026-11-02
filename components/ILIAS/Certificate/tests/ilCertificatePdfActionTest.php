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

use PHPUnit\Framework\MockObject\Stub;

class ilCertificatePdfActionTest extends ilCertificateBaseTestCase
{
    public function testCreatePdfWillCreatedAndIsDownloadable(): void
    {
        /** @var ilPdfGenerator&Stub $pdfGenerator */
        $pdfGenerator = $this->getStubBuilder(ilPdfGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generateCurrentActiveCertificate'])
            ->getStub();

        $pdfGenerator->method('generateCurrentActiveCertificate')
            ->willReturn('Something');

        /** @var ilCertificateUtilHelper&Stub $ilUtilHelper */
        $ilUtilHelper = $this->createStub(ilCertificateUtilHelper::class);

        /** @var ilErrorHandling&Stub $errorHandler */
        $errorHandler = $this->createStub(ilErrorHandling::class);

        $pdfAction = new ilCertificatePdfAction(
            $pdfGenerator,
            $ilUtilHelper,
            'translatedError',
            $errorHandler
        );

        $result = $pdfAction->createPDF(10, 200);

        $this->assertSame('Something', $result);
    }

    public function testPdfDownloadAction(): void
    {
        /** @var ilPdfGenerator&Stub $pdfGenerator */
        $pdfGenerator = $this->getStubBuilder(ilPdfGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generateCurrentActiveCertificate', 'generateFileName'])
            ->getStub();

        $pdfGenerator->method('generateCurrentActiveCertificate')
            ->willReturn('Something');

        $pdfGenerator->method('generateFileName')
            ->willReturn('some_file_name.pdf');

        $ilUtilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ilUtilHelper
            ->expects($this->once())
            ->method('deliverData')
            ->with(
                'Something',
                'some_file_name.pdf',
                'application/pdf'
            );

        /** @var ilErrorHandling&Stub $errorHandler */
        $errorHandler = $this->createStub(ilErrorHandling::class);

        $pdfAction = new ilCertificatePdfAction(
            $pdfGenerator,
            $ilUtilHelper,
            'translatedError',
            $errorHandler
        );
        $result = $pdfAction->downloadPdf(10, 200);

        $this->assertSame('Something', $result);
    }

    public function testDownloadResultsInExceptionBecauseTheServerIsNotActive(): void
    {
        /** @var ilPdfGenerator&Stub $pdfGenerator */
        $pdfGenerator = $this->getStubBuilder(ilPdfGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generateCurrentActiveCertificate', 'generateFileName'])
            ->getStub();

        $pdfGenerator->method('generateCurrentActiveCertificate')
            ->willReturn('Something');

        $pdfGenerator->method('generateFileName')
            ->willReturn('some_file_name.pdf');

        $ilUtilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ilUtilHelper
            ->expects($this->once())
            ->method('deliverData')
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

        $pdfAction = new ilCertificatePdfAction($pdfGenerator, $ilUtilHelper, '', $errorHandler);

        $result = $pdfAction->downloadPdf(10, 200);

        $this->assertSame('', $result);
    }
}
