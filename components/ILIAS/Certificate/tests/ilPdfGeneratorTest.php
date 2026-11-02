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
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\MockObject\Stub;

class ilPdfGeneratorTest extends ilCertificateBaseTestCase
{
    #[DoesNotPerformAssertions]
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
            '',
            '',
            300
        );

        /** @var ilUserCertificateRepository&Stub $user_repo */
        $user_repo = $this->createStub(ilUserCertificateRepository::class)
        ;
        $user_repo->method('fetchCertificate')
            ->willReturn($certificate);

        /** @var ilCertificateRpcClientFactoryHelper&Stub $rpc */
        $rpc = $this->createStub(ilCertificateRpcClientFactoryHelper::class);

        $pdf = new stdClass();
        $pdf->scalar = '';
        $rpc->method('ilFO2PDF')->willReturn($pdf);

        /** @var ilCertificatePdfFileNameFactory&Stub $filename_factory */
        $filename_factory = $this->createStub(ilCertificatePdfFileNameFactory::class);

        /** @var ilLanguage&Stub $language */
        $language = $this->createStub(ilLanguage::class);

        /** @var IRSS&Stub $irss */
        $irss = $this->createStub(IRSS::class);

        $pdf_generator = new ilPdfGenerator(
            $user_repo,
            $irss,
            $rpc,
            $filename_factory,
            $language
        );
        $pdf_generator->generate(100);
    }

    #[DoesNotPerformAssertions]
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
            '',
            '',
            300
        );

        /** @var ilUserCertificateRepository&Stub $user_repo */
        $user_repo = $this->createStub(ilUserCertificateRepository::class);

        $user_repo->method('fetchActiveCertificate')
            ->willReturn($certificate);

        /** @var ilCertificateRpcClientFactoryHelper&Stub $rpc */
        $rpc = $this->createStub(ilCertificateRpcClientFactoryHelper::class);

        $pdf = new stdClass();
        $pdf->scalar = '';
        $rpc->method('ilFO2PDF')->willReturn($pdf);

        /** @var ilCertificatePdfFileNameFactory&Stub $filename_factory */
        $filename_factory = $this->createStub(ilCertificatePdfFileNameFactory::class);

        /** @var ilLanguage&Stub $language */
        $language = $this->createStub(ilLanguage::class);

        /** @var IRSS&Stub $irss */
        $irss = $this->createStub(IRSS::class);

        $pdf_generator = new ilPdfGenerator(
            $user_repo,
            $irss,
            $rpc,
            $filename_factory,
            $language
        );
        $pdf_generator->generateCurrentActiveCertificate(100, 200);
    }
}
