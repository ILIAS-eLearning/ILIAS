<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilPdfGeneratorTest extends ilCertificateBaseTestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testGenerateSpecificCertificate() : void
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
            3,
            'v5.4.0',
            true,
            '/some/where/background.jpg',
            300
        );

        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchCertificate')
            ->willReturn($certificate);

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rpcHelper = $this->getMockBuilder(ilCertificateRpcClientFactoryHelper::class)
            ->getMock();

        $pdf = new stdClass();
        $pdf->scalar = '';
        $rpcHelper->method('ilFO2PDF')
            ->willReturn($pdf);

        $pdfFileNameFactory = $this->getMockBuilder(ilCertificatePdfFileNameFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = new ilPdfGenerator(
            $userCertificateRepository,
            $logger,
            $rpcHelper,
            $pdfFileNameFactory,
            $language
        );

        $pdfGenerator->generate(100);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGenerateCurrentActiveCertificate() : void
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
            3,
            'v5.4.0',
            true,
            '/some/where/background.jpg',
            300
        );

        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchActiveCertificate')
            ->willReturn($certificate);

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rpcHelper = $this->getMockBuilder(ilCertificateRpcClientFactoryHelper::class)
            ->getMock();

        $pdf = new stdClass();
        $pdf->scalar = '';
        $rpcHelper->method('ilFO2PDF')
            ->willReturn($pdf);

        $pdfFileNameFactory = $this->getMockBuilder(ilCertificatePdfFileNameFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = new ilPdfGenerator(
            $userCertificateRepository,
            $logger,
            $rpcHelper,
            $pdfFileNameFactory,
            $language
        );

        $pdfGenerator->generateCurrentActiveCertificate(100, 200);
    }
}
