<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilPdfGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testGenerateSpecificCertificate()
    {
        if (!defined('CLIENT_WEB_DIR')) {
            define("CLIENT_WEB_DIR", 'my/client/web/dir');
        }
        $certificate = new ilUserCertificate(
            3,
            20,
            'crs',
            50,
            'ilyas',
            123456789,
            '<xml> Some content </xml>',
            '[]',
            null,
            '3',
            'v5.4.0',
            true,
            '/some/where/background.jpg',
            300
        );

        $userCertificateRepository = $this->getMockBuilder('ilUserCertificateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchCertificate')
            ->willReturn($certificate);

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $rpcHelper = $this->getMockBuilder('ilCertificateRpcClientFactoryHelper')
            ->getMock();

        $rpcHelper->method('ilFO2PDF')
            ->willReturn(new ScalarPdf());

        $pdfFileNameFactory = $this->getMockBuilder('ilCertificatePdfFileNameFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = new ilPdfGenerator(
            $userCertificateRepository,
            $logger,
            $rpcHelper,
            $pdfFileNameFactory
        );

        $pdfGenerator->generate(100);
    }

    public function testGenerateCurrentActiveCertificate()
    {
        if (!defined('CLIENT_WEB_DIR')) {
            define("CLIENT_WEB_DIR", 'my/client/web/dir');
        }
        $certificate = new ilUserCertificate(
            3,
            20,
            'crs',
            50,
            'ilyas',
            123456789,
            '<xml> Some content </xml>',
            '[]',
            null,
            '3',
            'v5.4.0',
            true,
            '/some/where/background.jpg',
            300
        );

        $userCertificateRepository = $this->getMockBuilder('ilUserCertificateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchActiveCertificate')
            ->willReturn($certificate);

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $rpcHelper = $this->getMockBuilder('ilCertificateRpcClientFactoryHelper')
            ->getMock();

        $rpcHelper->method('ilFO2PDF')
            ->willReturn(new ScalarPdf());

        $pdfFileNameFactory = $this->getMockBuilder('ilCertificatePdfFileNameFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = new ilPdfGenerator(
            $userCertificateRepository,
            $logger,
            $rpcHelper,
            $pdfFileNameFactory
        );

        $pdfGenerator->generateCurrentActiveCertificate(100, 200);
    }
}

class ScalarPdf
{
    public $scalar = 'Some scalar';
}
