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
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use PHPUnit\Framework\MockObject\Stub;

class ilCertificateTemplateImportActionTest extends ilCertificateBaseTestCase
{
    public function testCertificateCanBeImportedWithBackgroundImage(): void
    {
        /** @var ilCertificatePlaceholderDescription&Stub $placeholderDescriptionObject */
        $placeholderDescriptionObject = $this->createStub(ilCertificatePlaceholderDescription::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        $filesystem = $this->getMockBuilder(ILIAS\Filesystem\Filesystem::class)
            ->getMock();

        $filesystem
            ->expects($this->once())
            ->method('listContents')
            ->willReturn([
                new Metadata('certificate.xml', 'file'),
                new Metadata('background.jpg', 'file'),
            ]);

        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilCertificateObjectHelper&Stub $objectHelper */
        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $objectHelper->method('lookupType')
            ->willReturn('crs');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(true);

        $unzip = $this->getMockBuilder(ILIAS\Filesystem\Util\Archive\Unzip::class)
            ->disableOriginalConstructor()
            ->getMock();
        $unzip->expects($this->once())->method('extract')->willReturn(true);
        $utilHelper
            ->expects($this->once())
            ->method('unzip')
            ->willReturn($unzip);

        /** @var ilDBInterface&Stub $database */
        $database = $this->createStub(ilDBInterface::class);

        $resource_ident = new ResourceIdentification('someId');

        /** @var Manager&Stub $irss_manager */
        $irss_manager = $this->createStub(Manager::class);
        $irss_manager->method('stream')->willReturn($resource_ident);
        /** @var IRSS&Stub $irss */
        $irss = $this->createStub(IRSS::class);
        $irss->method('manage')->willReturn($irss_manager);

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
        /** @var ilCertificatePlaceholderDescription&Stub $placeholderDescriptionObject */
        $placeholderDescriptionObject = $this->createStub(ilCertificatePlaceholderDescription::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        $filesystem = $this->getMockBuilder(ILIAS\Filesystem\Filesystem::class)
            ->getMock();

        $filesystem
            ->expects($this->once())
            ->method('listContents')
            ->willReturn([
                new Metadata('certificate.xml', 'file')
            ]);

        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilCertificateObjectHelper&Stub $objectHelper */
        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $objectHelper->method('lookupType')
            ->willReturn('crs');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(true);

        $unzip = $this->getMockBuilder(ILIAS\Filesystem\Util\Archive\Unzip::class)
            ->disableOriginalConstructor()
            ->getMock();
        $unzip->expects($this->once())->method('extract')->willReturn(true);
        $utilHelper
            ->expects($this->once())
            ->method('unzip')
            ->willReturn($unzip);

        /** @var ilDBInterface&Stub $database */
        $database = $this->createStub(ilDBInterface::class);

        /** @var IRSS&Stub $irss */
        $irss = $this->createStub(IRSS::class);

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
        /** @var ilCertificatePlaceholderDescription&Stub $placeholderDescriptionObject */
        $placeholderDescriptionObject = $this->createStub(ilCertificatePlaceholderDescription::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        $filesystem = $this->getMockBuilder(ILIAS\Filesystem\Filesystem::class)
            ->getMock();

        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilCertificateObjectHelper&Stub $objectHelper */
        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(true);

        $unzip = $this->getMockBuilder(ILIAS\Filesystem\Util\Archive\Unzip::class)
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

        /** @var ilDBInterface&Stub $database */
        $database = $this->createStub(ilDBInterface::class);

        /** @var IRSS&Stub $irss */
        $irss = $this->createStub(IRSS::class);

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
        /** @var ilCertificatePlaceholderDescription&Stub $placeholderDescriptionObject */
        $placeholderDescriptionObject = $this->createStub(ilCertificatePlaceholderDescription::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        /** @var ILIAS\Filesystem\Filesystem&Stub $filesystem */
        $filesystem = $this->createStub(ILIAS\Filesystem\Filesystem::class);

        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilCertificateObjectHelper&Stub $objectHelper */
        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        /** @var ilCertificateUtilHelper&Stub $utilHelper */
        $utilHelper = $this->createStub(ilCertificateUtilHelper::class);

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(false);

        /** @var ilDBInterface&Stub $database */
        $database = $this->createStub(ilDBInterface::class);

        /** @var IRSS&Stub $irss */
        $irss = $this->createStub(IRSS::class);

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
