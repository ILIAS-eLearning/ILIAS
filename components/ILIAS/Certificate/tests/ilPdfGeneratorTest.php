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

use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Certificate\ValueObject\CertificateId;
use ILIAS\Filesystem\Filesystem;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilPdfGeneratorTest extends ilCertificateBaseTestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testGenerateSpecificCertificate(): void
    {
        if (!defined('CLIENT_WEB_DIR')) {
            define('CLIENT_WEB_DIR', 'my/client/web/dir');
        }
        $certificate = new ilUserCertificate(
            3,
            20,
            'crs',
            50,
            'ilyas',
            123_456_789,
            '<xml> Some content </xml>',
            '[]',
            null,
            3,
            'v5.4.0',
            true,
            new CertificateId('11111111-2222-3333-4444-555555555555'),
            '/some/where/background.jpg',
            '/some/where/thumbnail.jpg',
            '',
            '',
            300
        );

        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $userCertificateRepository->method('fetchCertificate')
            ->willReturn($certificate)
        ;

        $rpcHelper = $this->getMockBuilder(ilCertificateRpcClientFactoryHelper::class)
            ->getMock()
        ;

        $pdf = new stdClass();
        $pdf->scalar = '';
        $rpcHelper->method('ilFO2PDF')
            ->willReturn($pdf)
        ;

        $pdfFileNameFactory = $this->getMockBuilder(ilCertificatePdfFileNameFactory::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $irss = $this->getMockBuilder(IRSS::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = new ilPdfGenerator(
            $userCertificateRepository,
            $irss,
            $filesystem,
            $rpcHelper,
            $pdfFileNameFactory,
            $language
        );

        $pdfGenerator->generate(100);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGenerateCurrentActiveCertificate(): void
    {
        if (!defined('CLIENT_WEB_DIR')) {
            define('CLIENT_WEB_DIR', 'my/client/web/dir');
        }
        $certificate = new ilUserCertificate(
            3,
            20,
            'crs',
            50,
            'ilyas',
            123_456_789,
            '<xml> Some content </xml>',
            '[]',
            null,
            3,
            'v5.4.0',
            true,
            new CertificateId('11111111-2222-3333-4444-555555555555'),
            '/some/where/background.jpg',
            '/some/where/thumbnail.jpg',
            '',
            '',
            300
        );

        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $userCertificateRepository->method('fetchActiveCertificate')
            ->willReturn($certificate)
        ;

        $rpcHelper = $this->getMockBuilder(ilCertificateRpcClientFactoryHelper::class)
            ->getMock()
        ;

        $pdf = new stdClass();
        $pdf->scalar = '';
        $rpcHelper->method('ilFO2PDF')
            ->willReturn($pdf)
        ;

        $pdfFileNameFactory = $this->getMockBuilder(ilCertificatePdfFileNameFactory::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $irss = $this->getMockBuilder(IRSS::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdfGenerator = new ilPdfGenerator(
            $userCertificateRepository,
            $irss,
            $filesystem,
            $rpcHelper,
            $pdfFileNameFactory,
            $language
        );

        $pdfGenerator->generateCurrentActiveCertificate(100, 200);
    }
}
