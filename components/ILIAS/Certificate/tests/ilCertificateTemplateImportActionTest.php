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

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\ResourceStorage\Services as IRSS;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateImportActionTest extends ilCertificateBaseTestCase
{
    public function testCertificateCanBeImportedWithBackgroundImage(): void
    {
        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder(ILIAS\Filesystem\Filesystem::class)
            ->getMock();

        $filesystem
            ->expects($this->once())
            ->method('listContents')
            ->willReturn([
                new Metadata('certificate.xml', 'file'),
                new Metadata('background.jpg', 'file'),
            ]);

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->method('lookupType')
            ->willReturn('crs');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(true);

        $unzip = $this->getMockBuilder(\ILIAS\Filesystem\Util\Archive\Unzip::class)
                      ->disableOriginalConstructor()
                      ->getMock();
        $unzip->expects($this->once())->method('extract')->willReturn(true);
        $utilHelper
            ->expects($this->once())
            ->method('unzip')
            ->willReturn($unzip);

        $utilHelper
            ->expects($this->once())
            ->method('convertImage');

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $irss = $this->getMockBuilder(IRSS::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $action = new ilCertificateTemplateImportAction(
            100,
            'some/path/certificate.xml',
            $placeholderDescriptionObject,
            $logger,
            $filesystem,
            $irss,
            $templateRepository,
            $objectHelper,
            $utilHelper,
            $database,
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertTrue($result);
    }

    public function testCertificateCanBeImportedWithoutBackgroundImage(): void
    {
        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder(ILIAS\Filesystem\Filesystem::class)
            ->getMock();

        $filesystem
            ->expects($this->once())
            ->method('listContents')
            ->willReturn([
                new Metadata('certificate.xml', 'file')
            ]);

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->method('lookupType')
            ->willReturn('crs');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(true);

        $unzip = $this->getMockBuilder(\ILIAS\Filesystem\Util\Archive\Unzip::class)
                      ->disableOriginalConstructor()
                      ->getMock();
        $unzip->expects($this->once())->method('extract')->willReturn(true);
        $utilHelper
            ->expects($this->once())
            ->method('unzip')
            ->willReturn($unzip);

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $irss = $this->getMockBuilder(IRSS::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $action = new ilCertificateTemplateImportAction(
            100,
            'some/path/certificate.xml',
            $placeholderDescriptionObject,
            $logger,
            $filesystem,
            $irss,
            $templateRepository,
            $objectHelper,
            $utilHelper,
            $database,
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertTrue($result);
    }

    public function testNoXmlFileInUplodadZipFolder(): void
    {
        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder(ILIAS\Filesystem\Filesystem::class)
            ->getMock();

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(true);

        $unzip = $this->getMockBuilder(\ILIAS\Filesystem\Util\Archive\Unzip::class)
                      ->disableOriginalConstructor()
                      ->getMock();
        $unzip->expects($this->once())->method('extract')->willReturn(true);
        $utilHelper
            ->expects($this->once())
            ->method('unzip')
            ->willReturn($unzip);

        $filesystem
            ->expects($this->once())
            ->method('listContents')
            ->willReturn([]);

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $irss = $this->getMockBuilder(IRSS::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $action = new ilCertificateTemplateImportAction(
            100,
            'some/path/certificate.xml',
            $placeholderDescriptionObject,
            $logger,
            $filesystem,
            $irss,
            $templateRepository,
            $objectHelper,
            $utilHelper,
            $database,
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertFalse($result);
    }

    public function testZipfileCouldNoBeMoved(): void
    {
        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder(ILIAS\Filesystem\Filesystem::class)
            ->getMock();

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(false);

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $irss = $this->getMockBuilder(IRSS::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $action = new ilCertificateTemplateImportAction(
            100,
            'some/path/certificate.xml',
            $placeholderDescriptionObject,
            $logger,
            $filesystem,
            $irss,
            $templateRepository,
            $objectHelper,
            $utilHelper,
            $database,
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertFalse($result);
    }
}
